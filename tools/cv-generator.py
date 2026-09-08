#!/usr/bin/env python3
"""Build the printable CV/portfolio from the site itself.

Skills, work history, the about text and the toolbox are read straight out of
index.html and js/main.js, so the PDF cannot drift from the live site: update
the site, re-run this, done. Only the things that exist nowhere on the site
(contact details, the Italian wording) live in this file.

Usage:
    python3 tools/cv-generator.py            # writes cv/*.html and cv/*.pdf
    python3 tools/cv-generator.py --no-pdf   # HTML only, no Chrome needed
"""

import argparse
import html
import os
import re
import shutil
import subprocess
import sys
from html.parser import HTMLParser

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

PORTFOLIO_URL = "https://giacomoguaresi.github.io/GiacomoGuaresi"
GITHUB_URL = "https://github.com/GiacomoGuaresi"
LINKEDIN_URL = "https://www.linkedin.com/in/giacomo-guaresi-b76b0812b"
EMAIL = "guaresi.giacomo@gmail.com"

NAME = "Giacomo Guaresi"
ACCENT = "#00ff67"  # the site's link-hover green

# Everything below is wording that has no home on the site. The English side is
# extracted, so only Italian needs spelling out.
STRINGS = {
    "en": {
        "role": "Software Engineer",
        "location": "Milan, Italy",
        "skills": "Skills",
        "about": "About",
        "work": "Work",
        "contact": "Contact",
        "toolbox": "Toolbox",
        "portfolio": "Portfolio",
        "period": "Period",
        "company": "Company",
        "role_col": "Role & Responsibilities",
        "footer": "Generated from " + PORTFOLIO_URL,
    },
    "it": {
        "role": "Software Engineer",
        "location": "Milano, Italia",
        "skills": "Competenze",
        "about": "Chi sono",
        "work": "Esperienza",
        "contact": "Contatti",
        "toolbox": "Strumenti",
        "portfolio": "Portfolio",
        "period": "Periodo",
        "company": "Azienda",
        "role_col": "Ruolo e responsabilità",
        "footer": "Generato da " + PORTFOLIO_URL,
    },
}

# Italian renderings of the site's English copy, keyed by the English source so
# a change on the site surfaces here as a missing key instead of stale text.
IT_SKILLS = {
    "Full-Stack Development (Frontend & Backend)":
        "Sviluppo Full-Stack (Frontend & Backend)",
    "Cloud Architectures & DevOps (Docker, CI/CD)":
        "Architetture Cloud & DevOps (Docker, CI/CD)",
    "Embedded Systems & Electronics (Arduino, PCB Design)":
        "Sistemi Embedded & Elettronica (Arduino, PCB Design)",
    "Programming Languages: C, C++, C#, Python, Java, JavaScript, TypeScript":
        "Linguaggi di programmazione: C, C++, C#, Python, Java, JavaScript, TypeScript",
    "Web Technologies: HTML, CSS, React, Node.js":
        "Tecnologie web: HTML, CSS, React, Node.js",
    "Databases & Tools: MySQL, PostgreSQL, MongoDB, Git":
        "Database e strumenti: MySQL, PostgreSQL, MongoDB, Git",
    "Creative Coding & Open-Source Collaboration":
        "Creative Coding e collaborazione open-source",
}

IT_ROLE_DESCRIPTIONS = {
    "web application development and database management":
        "sviluppo di applicazioni web e gestione di database",
    "industrial automation, control systems, and pharmaceutical research":
        "automazione industriale, sistemi di controllo e ricerca farmaceutica",
    "enterprise solutions, frontend/backend integration, cloud architectures":
        "soluzioni enterprise, integrazione frontend/backend, architetture cloud",
    "Software development for additive manufacturing, automation, and systems integration":
        "sviluppo software per additive manufacturing, automazione e integrazione di sistemi",
}

IT_TOOLBOX_LABELS = {
    "OS": "OS",
    "Languages": "Linguaggi",
    "Frontend": "Frontend",
    "Backend": "Backend",
    "DB": "DB",
    "DevOps": "DevOps",
}


# Corrections for typos in the site copy. Empty because they are all fixed
# upstream right now; add entries here only as a stopgap, then fix the source.
SITE_TYPOS = []


def fix_typos(text):
    for pattern, replacement in SITE_TYPOS:
        text = re.sub(pattern, replacement, text)
    return text


# ------------------------------------------------------------- site scraping


class ElementById(HTMLParser):
    """Captures the raw markup nested inside the element carrying an id."""

    VOID = {"br", "img", "hr", "meta", "link", "input"}

    def __init__(self, target):
        # convert_charrefs=True so entity handling stays with the parser: a bare
        # "&" in copy like "R&D ·" otherwise comes back as a bogus "R&D;".
        super().__init__(convert_charrefs=True)
        self.target = target
        self.depth = 0
        self.parts = []
        self.done = False

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if not self.done and self.depth == 0 and attrs.get("id") == self.target:
            self.depth = 1
            return
        if self.depth:
            if tag not in self.VOID:
                self.depth += 1
            self.parts.append(self.get_starttag_text())

    def handle_startendtag(self, tag, attrs):
        if self.depth:
            self.parts.append(self.get_starttag_text())

    def handle_endtag(self, tag):
        if not self.depth:
            return
        self.depth -= 1
        if self.depth == 0:
            self.done = True
        else:
            self.parts.append(f"</{tag}>")

    def handle_data(self, data):
        if self.depth:
            self.parts.append(data)

    @property
    def markup(self):
        return "".join(self.parts)


def element_by_id(markup, element_id):
    parser = ElementById(element_id)
    parser.feed(markup)
    if not parser.done:
        sys.exit(f"index.html: no element with id={element_id!r}")
    return parser.markup


def text_of(fragment):
    """Tags out, entities decoded, whitespace collapsed, site typos corrected."""
    plain = re.sub(r"\s+", " ", html.unescape(re.sub(r"<[^>]+>", "", fragment))).strip()
    return fix_typos(plain)


def scrape_site():
    with open(os.path.join(ROOT, "index.html"), encoding="utf-8") as handle:
        page = handle.read()

    skills = [text_of(td) for td in
              re.findall(r"<td[^>]*>(.*?)</td>",
                         element_by_id(page, "pageSkills"), re.S)]

    works = []
    for row in re.findall(r"<tr[^>]*>(.*?)</tr>",
                          element_by_id(page, "pageWorks"), re.S):
        if "<h3" in row:
            continue  # the .01/.02/.03 column headings
        cells = re.findall(r"<td[^>]*>(.*?)</td>", row, re.S)
        if len(cells) != 3:
            continue
        italic = re.search(r"<i[^>]*>(.*?)</i>", cells[2], re.S)
        works.append({
            "period": text_of(cells[0]),
            "company": text_of(cells[1]),
            "role": text_of(re.sub(r"<i[^>]*>.*?</i>", "", cells[2], flags=re.S)).rstrip(" ·"),
            "description": text_of(italic.group(1)) if italic else "",
        })

    # The footer marquee doubles as the toolbox list: "| Label: a · b | Label: ..."
    marquee = element_by_id(page, "ContentDesktop")
    content = re.search(r'MarqueeName="footerText".*?<div class="content">(.*?)</div>',
                        marquee, re.S)
    toolbox = []
    for chunk in text_of(content.group(1)).split("|"):
        chunk = chunk.replace("TOOLBOX →", "").strip()
        if ":" in chunk:
            label, values = chunk.split(":", 1)
            toolbox.append((label.strip(), values.strip()))

    with open(os.path.join(ROOT, "js", "main.js"), encoding="utf-8") as handle:
        script = handle.read()

    def about(name):
        match = re.search(r"const %s = `(.*?)`;" % name, script, re.S)
        if not match:
            sys.exit(f"js/main.js: could not find {name}")
        return [fix_typos(re.sub(r"\s+", " ", p).strip())
                for p in match.group(1).strip().split("\n") if p.strip()]

    return {
        "skills": [s for s in skills if s],
        "works": works,
        "toolbox": toolbox,
        "about": {"en": about("textEng"), "it": about("textIta")},
    }


def localise(data, lang):
    """Swap in the Italian wording; English is already what the site says."""
    if lang == "en":
        return data
    missing = []
    skills = []
    for skill in data["skills"]:
        if skill not in IT_SKILLS:
            missing.append(skill)
        skills.append(IT_SKILLS.get(skill, skill))
    works = []
    for work in data["works"]:
        if work["description"] and work["description"] not in IT_ROLE_DESCRIPTIONS:
            missing.append(work["description"])
        works.append(dict(work, description=IT_ROLE_DESCRIPTIONS.get(
            work["description"], work["description"])))
    if missing:
        print("  ! no Italian wording for (left in English):", file=sys.stderr)
        for item in missing:
            print(f"      {item}", file=sys.stderr)
    return dict(data, skills=skills, works=works,
                toolbox=[(IT_TOOLBOX_LABELS.get(l, l), v) for l, v in data["toolbox"]])


# ----------------------------------------------------------------- rendering

CSS = """
@font-face { font-family: 'IBMPlexMono'; src: url('../fonts/IBMPlexMono-Regular.ttf') format('truetype'); }
@font-face { font-family: 'IBMPlexMono-Bold'; src: url('../fonts/IBMPlexMono-Bold.ttf') format('truetype'); }

@page { size: A4; margin: 11mm 12mm 10mm 12mm; }

* { box-sizing: border-box; }

html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

body {
    margin: 0;
    font-family: 'IBMPlexMono', monospace;
    font-size: 7.8pt;
    line-height: 1.4;
    color: #000;
    background: #fff;
}

b, strong, .bold { font-family: 'IBMPlexMono-Bold', monospace; font-weight: normal; }

/* --- masthead --- */
.masthead { border-bottom: 3px solid #000; padding-bottom: 3.5mm; margin-bottom: 4mm; }

.name {
    font-family: 'IBMPlexMono-Bold', monospace;
    font-size: 27pt;
    line-height: 1;
    letter-spacing: -0.02em;
    text-transform: uppercase;
    margin: 0;
}

.tagline {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    text-transform: uppercase;
    font-size: 9pt;
    margin-top: 2.5mm;
}

/* One ref per line: the LinkedIn URL is too long to survive two columns, and a
   wrapped link with a border-bottom leaves a dangling rule across the page. */
.refs { margin-top: 3mm; }

.ref { display: flex; gap: 2mm; font-size: 7.5pt; line-height: 1.45; }

.ref .key {
    font-family: 'IBMPlexMono-Bold', monospace;
    text-transform: uppercase;
    min-width: 22mm;
}

.ref a { color: #000; text-decoration: underline; text-underline-offset: 2px;
         text-decoration-color: #b9b9b9; }

/* --- sections --- */
section { margin-bottom: 4mm; break-inside: auto; }

h2 {
    font-family: 'IBMPlexMono-Bold', monospace;
    font-size: 9.5pt;
    text-transform: uppercase;
    margin: 0 0 1.5mm 0;
    padding-bottom: 1mm;
    border-bottom: 2px solid #000;
    display: flex;
    gap: 3mm;
    break-after: avoid;
}

h2 .num { color: #6d6d6d; }

/* --- skills --- */
.skills { list-style: none; margin: 0; padding: 0; }

.skills li {
    padding: 0.65mm 0 0.65mm 5mm;
    border-bottom: 1px solid #d5d5d5;
    position: relative;
    break-inside: avoid;
}

.skills li::before { content: '→'; position: absolute; left: 0; }

/* --- work --- */
.job {
    display: grid;
    grid-template-columns: 24mm 1fr;
    gap: 0 4mm;
    padding: 1.4mm 0;
    border-bottom: 1px solid #d5d5d5;
    break-inside: avoid;
}

.job .period { font-family: 'IBMPlexMono-Bold', monospace; }

.job .company { font-family: 'IBMPlexMono-Bold', monospace; text-transform: uppercase; }

.job .description { color: #444; font-style: italic; }

/* --- about --- */
.about p { margin: 0 0 1.4mm 0; text-align: justify; hyphens: auto; }

/* --- toolbox --- */
.toolbox { display: grid; grid-template-columns: 1fr; gap: 0.8mm; }

.tool { display: flex; gap: 3mm; break-inside: avoid; }

.tool .key {
    font-family: 'IBMPlexMono-Bold', monospace;
    text-transform: uppercase;
    min-width: 22mm;
    flex: none;
}

/* --- footer --- */
.colophon {
    margin-top: 4mm;
    padding-top: 1.5mm;
    border-top: 3px solid #000;
    display: flex;
    justify-content: space-between;
    font-size: 7pt;
    text-transform: uppercase;
    color: #6d6d6d;
}

.colophon .mark { color: #000; font-family: 'IBMPlexMono-Bold', monospace; }
"""


def esc(text):
    return html.escape(str(text), quote=True)


def render(data, lang):
    s = STRINGS[lang]
    out = []
    add = out.append

    add(f"""<!DOCTYPE html>
<html lang="{lang}">
<head>
<meta charset="UTF-8">
<title>{esc(NAME)} — CV</title>
<style>{CSS}</style>
</head>
<body>
<header class="masthead">
  <h1 class="name">{esc(NAME)}</h1>
  <div class="tagline"><span>{esc(s['role'])}</span><span>{esc(s['location'])}</span></div>
  <div class="refs">""")

    for key, label, url in (
        ("portfolio", s["portfolio"], PORTFOLIO_URL),
        ("email", "Email", "mailto:" + EMAIL),
        ("github", "GitHub", GITHUB_URL),
        ("linkedin", "LinkedIn", LINKEDIN_URL),
    ):
        shown = EMAIL if key == "email" else url.replace("https://", "")
        add(f'    <div class="ref"><span class="key">{esc(label)}</span>'
            f'<a href="{esc(url)}">{esc(shown)}</a></div>')

    add("  </div>\n</header>")

    add(f'<section><h2><span class="num">.01</span>{esc(s["skills"])}</h2><ul class="skills">')
    for skill in data["skills"]:
        add(f"  <li>{esc(skill)}</li>")
    add("</ul></section>")

    add(f'<section class="about"><h2><span class="num">.02</span>{esc(s["about"])}</h2>')
    for paragraph in data["about"][lang]:
        add(f"  <p>{esc(paragraph)}</p>")
    add("</section>")

    add(f'<section><h2><span class="num">.03</span>{esc(s["work"])}</h2>')
    for job in reversed(data["works"]):  # most recent first, unlike the site
        add(f"""  <div class="job">
    <div class="period">{esc(job['period'])}</div>
    <div>
      <div class="company">{esc(job['company'])}</div>
      <div class="role">{esc(job['role'])}</div>
      <div class="description">{esc(job['description'])}</div>
    </div>
  </div>""")
    add("</section>")

    add(f'<section><h2><span class="num">.04</span>{esc(s["toolbox"])}</h2><div class="toolbox">')
    for label, values in data["toolbox"]:
        add(f'  <div class="tool"><span class="key">{esc(label)}</span>'
            f"<span>{esc(values)}</span></div>")
    add("</div></section>")

    add(f"""<div class="colophon">
  <span class="mark">{esc(NAME)}</span>
  <span>{esc(s['footer'])}</span>
</div>
</body>
</html>""")
    return "\n".join(out)


# ---------------------------------------------------------------------- main


def find_chrome():
    for name in ("google-chrome", "google-chrome-stable", "chromium",
                 "chromium-browser", "microsoft-edge"):
        path = shutil.which(name)
        if path:
            return path
    return None


def to_pdf(chrome, html_path, pdf_path):
    subprocess.run([
        chrome, "--headless=new", "--disable-gpu", "--no-sandbox",
        "--no-pdf-header-footer", "--virtual-time-budget=4000",
        f"--print-to-pdf={pdf_path}", "file://" + html_path,
    ], check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--out", default="cv")
    parser.add_argument("--lang", default="en,it", help="comma separated: en, it")
    parser.add_argument("--no-pdf", action="store_true", help="skip the Chrome step")
    args = parser.parse_args()

    data = scrape_site()
    print(f"scraped {len(data['skills'])} skills, {len(data['works'])} roles, "
          f"{len(data['toolbox'])} toolbox groups")

    out_dir = os.path.join(ROOT, args.out)
    os.makedirs(out_dir, exist_ok=True)
    chrome = None if args.no_pdf else find_chrome()
    if not args.no_pdf and not chrome:
        sys.exit("No Chrome/Chromium found. Re-run with --no-pdf and print the HTML yourself.")

    for lang in [l.strip() for l in args.lang.split(",") if l.strip()]:
        if lang not in STRINGS:
            sys.exit(f"unknown language {lang!r}")
        html_path = os.path.join(out_dir, f"cv-{lang}.html")
        with open(html_path, "w", encoding="utf-8") as handle:
            handle.write(render(localise(data, lang), lang))
        print(f"wrote {os.path.relpath(html_path, ROOT)}")

        if chrome:
            pdf_path = os.path.join(out_dir, f"Giacomo_Guaresi_CV_{lang.upper()}.pdf")
            to_pdf(chrome, html_path, pdf_path)
            size = os.path.getsize(pdf_path) / 1024
            with open(pdf_path, "rb") as handle:
                pages = len(re.findall(rb"/Type\s*/Page[^s]", handle.read()))
            print(f"wrote {os.path.relpath(pdf_path, ROOT)} "
                  f"({size:.0f} KB, {pages} page{'s' if pages != 1 else ''})")


if __name__ == "__main__":
    main()
