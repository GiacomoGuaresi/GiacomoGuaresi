#!/usr/bin/env python3
"""Render Animation.webp, the banner at the top of the profile README.

The old version of this script printed a fake installer to the terminal and the
webp had to be screen-recorded by hand. This one draws the frames and writes the
animation directly, so the banner can be regenerated from a single command:

    python3 tools/animation-generator.py

Styling follows the site: IBM Plex Mono, black ground, #00ff67, hard 2px rules.
"""

from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / "Animation.webp"
FONT_REGULAR = ROOT / "fonts" / "IBMPlexMono-Regular.ttf"
FONT_BOLD = ROOT / "fonts" / "IBMPlexMono-Bold.ttf"

SITE = "giacomoguaresi.github.io/GiacomoGuaresi"

WIDTH, HEIGHT = 800, 378
BAR_H = 30
PAD_X, PAD_Y = 22, 18
LINE_H = 20
BODY_SIZE = 13
BAR_SIZE = 12

BG = (0, 0, 0)
GREEN = (0, 255, 103)
DIM = (63, 156, 102)
WHITE = (255, 255, 255)

FRAME_MS = 70
TYPE_CHARS_PER_FRAME = 2
LINE_HOLD = 3
END_HOLD = 26

# (kind, payload). "cmd" types out a shell line, "out" and "kv" drop a finished
# line of output in, "gap" leaves a blank line.
SCRIPT = [
    ("cmd", "whoami"),
    ("out", "giacomo guaresi"),
    ("out", "electronics, firmware, and the software that runs machines"),
    ("gap", ""),
    ("cmd", "cat stack"),
    ("kv", ("electronics", "schematic → pcb → production")),
    ("kv", ("embedded", "c/c++ · stm32 · esp32 · can bus")),
    ("kv", ("industrial", "scada · modbus · opc-ua · gmp")),
    ("kv", ("backend", ".net · node.js · python · spring")),
    ("kv", ("systems", "linux · custom kernels · docker")),
    ("gap", ""),
    ("cmd", "open portfolio"),
]


def build_states():
    """Expand SCRIPT into one screen state per frame.

    A state is (finished_lines, partial_line): the partial is the shell line
    currently being typed, and is None while output is being printed.
    """
    states = []
    lines = []

    for kind, payload in SCRIPT:
        if kind == "cmd":
            for i in range(0, len(payload) + 1, TYPE_CHARS_PER_FRAME):
                states.append((list(lines), ("cmd", payload[:i])))
            states.extend([(list(lines), ("cmd", payload))] * LINE_HOLD)
            lines.append(("cmd", payload))
        else:
            lines.append((kind, payload))
            states.extend([(list(lines), None)] * LINE_HOLD)

    states.extend([(list(lines), None)] * END_HOLD)
    return states


def draw_bars(draw, fonts):
    """Top and bottom rules, both carrying the things a reader must not miss."""
    bar_bold, bar_regular = fonts

    draw.rectangle([0, 0, WIDTH, BAR_H], fill=GREEN)
    draw.text((PAD_X, BAR_H / 2), "GIACOMO GUARESI", font=bar_bold, fill=BG, anchor="lm")
    draw.text((WIDTH - PAD_X, BAR_H / 2), "SOFTWARE · HARDWARE · INDUSTRY",
              font=bar_regular, fill=BG, anchor="rm")

    top = HEIGHT - BAR_H
    draw.rectangle([0, top, WIDTH, HEIGHT], fill=GREEN)
    draw.text((PAD_X, top + BAR_H / 2), "→ " + SITE, font=bar_bold, fill=BG, anchor="lm")
    draw.text((WIDTH - PAD_X, top + BAR_H / 2), "PORTFOLIO",
              font=bar_regular, fill=BG, anchor="rm")


def render(state, blink, fonts):
    body, body_bold, bar_bold, bar_regular = fonts
    finished, partial = state

    image = Image.new("RGB", (WIDTH, HEIGHT), BG)
    draw = ImageDraw.Draw(image)
    draw_bars(draw, (bar_bold, bar_regular))

    y = BAR_H + PAD_Y
    for kind, payload in finished:
        draw_line(draw, y, kind, payload, (body, body_bold))
        y += LINE_H

    if partial is not None:
        _, text = partial
        cursor = "_" if blink else " "
        draw_line(draw, y, "cmd", text + cursor, (body, body_bold))
    elif blink:
        draw.text((PAD_X, y), "$ _", font=body, fill=GREEN)

    return image


def draw_line(draw, y, kind, payload, fonts):
    body, body_bold = fonts

    if kind == "gap":
        return

    if kind == "cmd":
        draw.text((PAD_X, y), "$ ", font=body, fill=DIM)
        draw.text((PAD_X + text_width(body, "$ "), y), payload, font=body_bold, fill=WHITE)
        return

    if kind == "kv":
        key, value = payload
        draw.text((PAD_X + 18, y), key, font=body_bold, fill=GREEN)
        draw.text((PAD_X + 18 + 110, y), value, font=body, fill=DIM)
        return

    draw.text((PAD_X + 18, y), payload, font=body, fill=GREEN)


def text_width(font, text):
    return font.getbbox(text)[2]


def main():
    fonts = (
        ImageFont.truetype(str(FONT_REGULAR), BODY_SIZE),
        ImageFont.truetype(str(FONT_BOLD), BODY_SIZE),
        ImageFont.truetype(str(FONT_BOLD), BAR_SIZE),
        ImageFont.truetype(str(FONT_REGULAR), BAR_SIZE),
    )

    states = build_states()
    frames = [render(state, (i // 5) % 2 == 0, fonts) for i, state in enumerate(states)]

    # Hold the last frame — the one showing the URL — long enough to read.
    durations = [FRAME_MS] * len(frames)
    durations[-1] = 2200

    frames[0].save(
        OUT,
        format="WEBP",
        save_all=True,
        append_images=frames[1:],
        duration=durations,
        loop=0,
        lossless=True,
        method=6,
    )
    print(f"wrote {OUT.relative_to(ROOT)} — {len(frames)} frames, {OUT.stat().st_size // 1024} KB")


if __name__ == "__main__":
    main()
