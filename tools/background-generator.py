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

The sources live in tools/backgrounds-src/, away from the images the site
serves. The same run also renders img/og-image.png, the 1200x630 card that
link previews show.

    python3 tools/background-generator.py
"""

import subprocess
import sys
import tempfile
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont, ImageOps

ROOT = Path(__file__).resolve().parent.parent
IMG_DIR = ROOT / "img" / "immaginiBitmappateSfondo"
SRC_DIR = ROOT / "tools" / "backgrounds-src"
FONT_DIR = ROOT / "tools" / "fonts"
OG_IMAGE = ROOT / "img" / "og-image.png"

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
# Lossy WebP: these are decoration behind opaque cards, and at this quality the
# scanlines stay crisp at roughly a quarter of the size of the palette PNGs
# the site used to ship.
WEBP_QUALITY = 75

OG_SIZE = (1200, 630)
OG_NAME = "GIACOMO GUARESI"
OG_TITLE = "FULL-STACK DEVELOPER"
OG_URL = "giacomoguaresi.github.io/GiacomoGuaresi"


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
    image.save(path, "WEBP", quality=WEBP_QUALITY, method=6)
    return path.stat().st_size


def og_image(gray):
    """The link-preview card: the home portrait on top, the wordmark on a solid
    band underneath, clear of the face. PNG on purpose: not every preview
    crawler takes WebP."""
    width, height = OG_SIZE
    card = ImageOps.fit(gray, OG_SIZE, method=Image.LANCZOS, centering=(0.62, 0.5))
    card = scanlines(card).convert("RGB")
    draw = ImageDraw.Draw(card)

    band_top = 410
    draw.rectangle((0, band_top, width, height), fill=(255, 255, 255))
    draw.line((0, band_top, width, band_top), fill=(0, 0, 0), width=4)

    name_font = ImageFont.truetype(str(FONT_DIR / "JetBrainsMono-ExtraBold.ttf"), 76)
    title_font = ImageFont.truetype(str(FONT_DIR / "JetBrainsMono-Bold.ttf"), 30)
    url_font = ImageFont.truetype(str(FONT_DIR / "JetBrainsMono-Regular.ttf"), 22)

    left = 48
    draw.text((left, band_top + 30), OG_NAME, font=name_font, fill=(0, 0, 0))
    name_end = left + draw.textlength(OG_NAME, font=name_font)
    draw.rectangle((name_end + 12, band_top + 42, name_end + 19, band_top + 104), fill=GREEN)  # the cursor
    draw.text((left, band_top + 138), OG_TITLE, font=title_font, fill=(0, 0, 0))
    url_w = draw.textlength(OG_URL, font=url_font)
    draw.text((width - left - url_w, band_top + 144), OG_URL, font=url_font, fill=(109, 109, 109))

    card.save(OG_IMAGE, optimize=True)
    return OG_IMAGE.stat().st_size


def main():
    if not IMG_DIR.is_dir():
        raise SystemExit(f"missing {IMG_DIR}")
    if not SRC_DIR.is_dir():
        raise SystemExit(f"missing {SRC_DIR}")

    for page, source in PAGES.items():
        path = SRC_DIR / source
        if not path.exists():
            print(f"  skipped {page}: {source} not found", file=sys.stderr)
            continue

        original = load(path)
        source_size = original.size
        gray = ImageOps.fit(original, CANVAS, method=Image.LANCZOS,
                            centering=FRAMING.get(page, DEFAULT_FRAMING))
        # The sources are shot under very different light; without this the
        # artwork is far heavier on some sections than others.
        gray = ImageOps.autocontrast(gray, cutoff=0.5)

        light_size = save(scanlines(gray), f"{page}-light.webp")
        dark_size = save(scanlines(tint(gray, GREEN)), f"{page}-dark.webp")

        print(f"  {page:9s} {source_size[0]}x{source_size[1]} -> {gray.width}x{gray.height}"
              f"   light {light_size // 1024:4d} KB   dark {dark_size // 1024:4d} KB"
              f"   <- {source}")

        if page == "home":
            og_size = og_image(ImageOps.autocontrast(original, cutoff=0.5))
            print(f"  og-image  {OG_SIZE[0]}x{OG_SIZE[1]}   {og_size // 1024:4d} KB")


if __name__ == "__main__":
    main()
