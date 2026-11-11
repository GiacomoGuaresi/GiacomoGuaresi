#!/usr/bin/env python3
"""Print the two CVs to PDF.

cv/cv-en.html and cv/cv-it.html are written and maintained by hand; they are
the source of truth and this script never touches them. It only prints each one
to cv/Giacomo_Guaresi_CV_<LANG>.pdf with headless Chrome, and reports the page
count so a CV that has spilled past two pages does not go unnoticed.

(It used to scrape index.html and regenerate the HTML as well. The CVs have
since diverged from the site on purpose, so that half is gone.)

Usage:
    python3 tools/cv-generator.py              # both languages
    python3 tools/cv-generator.py --lang it    # one of them
"""

import argparse
import os
import re
import shutil
import subprocess
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
CV_DIR = os.path.join(ROOT, "cv")
LANGS = ("en", "it")
MAX_PAGES = 2

CHROME_NAMES = ("google-chrome", "google-chrome-stable", "chromium",
                "chromium-browser", "microsoft-edge")
CHROME_PATHS = (
    "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome",
    "/Applications/Chromium.app/Contents/MacOS/Chromium",
    "/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge",
)


def find_chrome():
    for name in CHROME_NAMES:
        path = shutil.which(name)
        if path:
            return path
    for path in CHROME_PATHS:
        if os.path.exists(path):
            return path
    return None


def to_pdf(chrome, html_path, pdf_path):
    subprocess.run([
        chrome, "--headless=new", "--disable-gpu", "--no-sandbox",
        "--no-pdf-header-footer", "--virtual-time-budget=4000",
        f"--print-to-pdf={pdf_path}", "file://" + html_path,
    ], check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)


def page_count(pdf_path):
    with open(pdf_path, "rb") as handle:
        return len(re.findall(rb"/Type\s*/Page[^s]", handle.read()))


def main():
    parser = argparse.ArgumentParser(description=__doc__,
                                     formatter_class=argparse.RawDescriptionHelpFormatter)
    parser.add_argument("--lang", default=",".join(LANGS), help="comma separated: en, it")
    args = parser.parse_args()

    chrome = find_chrome()
    if not chrome:
        sys.exit("No Chrome/Chromium found: open the cv/*.html files and print them to PDF by hand.")

    too_long = False
    for lang in [l.strip() for l in args.lang.split(",") if l.strip()]:
        if lang not in LANGS:
            sys.exit(f"unknown language {lang!r}")
        html_path = os.path.join(CV_DIR, f"cv-{lang}.html")
        if not os.path.exists(html_path):
            sys.exit(f"missing {os.path.relpath(html_path, ROOT)}")
        pdf_path = os.path.join(CV_DIR, f"Giacomo_Guaresi_CV_{lang.upper()}.pdf")
        to_pdf(chrome, html_path, pdf_path)

        pages = page_count(pdf_path)
        too_long = too_long or pages > MAX_PAGES
        print(f"wrote {os.path.relpath(pdf_path, ROOT)} "
              f"({os.path.getsize(pdf_path) / 1024:.0f} KB, {pages} page{'s' if pages != 1 else ''})"
              f"{'   <- over ' + str(MAX_PAGES) + ' pages' if pages > MAX_PAGES else ''}")

    if too_long:
        sys.exit(1)


if __name__ == "__main__":
    main()
