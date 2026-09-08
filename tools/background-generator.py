#!/usr/bin/env python3
"""Render the CRT-style page backgrounds from the source photographs.

One filter, two outputs per page:

  light theme  greyscale, for the white ground
  dark theme   the same frame mapped onto the interface green, for the black one

Both get the scanlines the site is built around: black for half of every
period, image for the other half. Every source is first fitted to one common
canvas, and only then are the lines drawn on top. Matching the width alone is
not enough: the browser scales these to cover the panel, so a frame shorter
than the others gets scaled up more and its stripes come out thicker.

    python3 tools/background-generator.py
"""

import subprocess
import sys
import tempfile
from pathlib import Path

from PIL import Image, ImageOps

ROOT = Path(__file__).resolve().parent.parent
IMG_DIR = ROOT / "img" / "immaginiBitmappateSfondo"

# Which source photograph backs which section of the site.
PAGES = {
    "home": "FacciaJack_original.png",
    "stack": "Stack_original.jpg",
    "work": "Works_original.avif",
    "projects": "CircuitBoard_original.png",
    "about": "AboutBg_original.jpg",
}

CANVAS = (1920, 1080)

# Fitting to the canvas crops whatever does not fit, so say which part of each
# frame has to survive: (0, 0) keeps the top left corner, (1, 1) the bottom
# right. Anything not listed is cropped around its middle.
FRAMING = {
    "home": (0.58, 0.5),   # he sits right of centre in a very wide frame
    "about": (0.5, 0.12),  # his head is near the top edge
}
DEFAULT_FRAMING = (0.5, 0.5)

SCANLINE_PERIOD = 6
SCANLINE_HEIGHT = 3
GREEN = (0, 255, 103)
# Enough steps that the gradients stay smooth, few enough that these stay
# reasonable to download — they are decoration behind opaque cards.
PALETTE_COLORS = 48


def load(path):
    """Open an image, going through ffmpeg for formats Pillow cannot read."""
    try:
        return Image.open(path).convert("L")
    except OSError:
        with tempfile.TemporaryDirectory() as tmp:
            decoded = Path(tmp) / "decoded.png"
            result = subprocess.run(
                ["ffmpeg", "-y", "-loglevel", "error", "-i", str(path), str(decoded)],
                capture_output=True, text=True,
            )
            if result.returncode != 0 or not decoded.exists():
                raise SystemExit(f"cannot decode {path.name}: {result.stderr.strip()}")
            return Image.open(decoded).convert("L")


def scanlines(image):
    """Paint the black CRT lines over a copy of the image."""
    out = image.copy()
    black = Image.new(out.mode, (out.width, SCANLINE_HEIGHT), 0 if out.mode == "L" else (0, 0, 0))
    for y in range(0, out.height, SCANLINE_PERIOD):
        out.paste(black, (0, y))
    return out


def tint(gray, colour):
    """Map a greyscale frame onto a single hue: black stays black, white becomes
    the colour. This is what makes the dark variant read as phosphor glow."""
    r, g, b = colour
    return Image.merge("RGB", (
        gray.point(lambda v, c=r: v * c // 255),
        gray.point(lambda v, c=g: v * c // 255),
        gray.point(lambda v, c=b: v * c // 255),
    ))


def save(image, name):
    path = IMG_DIR / name
    image.quantize(colors=PALETTE_COLORS, method=Image.MEDIANCUT).save(path, optimize=True)
    return path.stat().st_size


def main():
    if not IMG_DIR.is_dir():
        raise SystemExit(f"missing {IMG_DIR}")

    for page, source in PAGES.items():
        path = IMG_DIR / source
        if not path.exists():
            print(f"  skipped {page}: {source} not found", file=sys.stderr)
            continue

        gray = load(path)
        source_size = gray.size
        gray = ImageOps.fit(gray, CANVAS, method=Image.LANCZOS,
                            centering=FRAMING.get(page, DEFAULT_FRAMING))
        # The sources are shot under very different light; without this the
        # artwork is far heavier on some sections than others.
        gray = ImageOps.autocontrast(gray, cutoff=0.5)

        light_size = save(scanlines(gray), f"{page}-light.png")
        dark_size = save(scanlines(tint(gray, GREEN)), f"{page}-dark.png")

        print(f"  {page:9s} {source_size[0]}x{source_size[1]} -> {gray.width}x{gray.height}"
              f"   light {light_size // 1024:4d} KB   dark {dark_size // 1024:4d} KB"
              f"   <- {source}")


if __name__ == "__main__":
    main()
