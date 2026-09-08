#!/usr/bin/env python3
"""Generate the GitHub Stats cards used by the README as static SVG files.

The public github-readme-stats instance keeps going down (503 DEPLOYMENT_PAUSED),
so the cards are rendered here instead and committed to the repo. A GitHub
Action (.github/workflows/stats.yml) refreshes them on a schedule.

Usage:
    GITHUB_TOKEN=<token> python3 tools/stats-generator.py [--user LOGIN] [--out DIR]
"""

import argparse
import json
import math
import os
import sys
import urllib.error
import urllib.request

API = "https://api.github.com/graphql"

# Same palette the README used in the github-readme-stats query string.
THEME = {
    "title": "#ffffff",
    "text": "#c9cacc",
    "icon": "#dc6601",
    "bg": "#1d1f21",
    "border": "#2c2f31",
    "track": "#3a3d40",
}

CARD_WIDTH = 495
LINE_HEIGHT = 27
FONT = "'Segoe UI', Ubuntu, Sans-Serif"

ICONS = {
    "star": '<path d="M8 .25a.75.75 0 01.673.418l1.882 3.815 4.21.612a.75.75 0 01.416 1.279l-3.046 2.97.719 4.192a.75.75 0 01-1.088.791L8 12.347l-3.766 1.98a.75.75 0 01-1.088-.79l.72-4.194L.818 6.374a.75.75 0 01.416-1.28l4.21-.611L7.327.668A.75.75 0 018 .25z"/>',
    "commit": '<path d="M10.5 7.75a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zm1.43.75a4.002 4.002 0 01-7.86 0H.75a.75.75 0 110-1.5h3.32a4.002 4.002 0 017.86 0h3.32a.75.75 0 110 1.5h-3.32z"/>',
    "pr": '<path d="M7.177 3.073L9.573.677A.25.25 0 0110 .854v4.792a.25.25 0 01-.427.177L7.177 3.427a.25.25 0 010-.354zM3.75 2.5a.75.75 0 100 1.5.75.75 0 000-1.5zm-2.25.75a2.25 2.25 0 113 2.122v5.256a2.251 2.251 0 11-1.5 0V5.372A2.25 2.25 0 011.5 3.25zM11 2.5h-1V4h1a1 1 0 011 1v5.628a2.251 2.251 0 101.5 0V5A2.5 2.5 0 0011 2.5zm1 10.25a.75.75 0 111.5 0 .75.75 0 01-1.5 0zM3.75 12a.75.75 0 100 1.5.75.75 0 000-1.5z"/>',
    "issue": '<path d="M8 9.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/><path fill-rule="evenodd" d="M8 0a8 8 0 100 16A8 8 0 008 0zM1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0z"/>',
    "repo": '<path d="M1.75 16A1.75 1.75 0 010 14.25V1.75C0 .784.784 0 1.75 0h8.5C11.216 0 12 .784 12 1.75v12.5c0 .085-.006.168-.018.25h2.268a.25.25 0 00.25-.25V8.285a.25.25 0 00-.111-.208l-1.055-.703a.75.75 0 11.832-1.248l1.055.703c.487.325.779.871.779 1.456v5.965A1.75 1.75 0 0114.25 16h-3.5a.766.766 0 01-.197-.026c-.099.017-.2.026-.303.026h-3a.75.75 0 01-.75-.75V14h-1v1.25a.75.75 0 01-.75.75h-3zM1.75 1.5a.25.25 0 00-.25.25v12.5c0 .138.112.25.25.25h2V13.5a.75.75 0 01.75-.75h2.5a.75.75 0 01.75.75v1h2.5a.25.25 0 00.25-.25V1.75a.25.25 0 00-.25-.25h-8.5zM3 3.75A.75.75 0 013.75 3h.5a.75.75 0 010 1.5h-.5A.75.75 0 013 3.75zM3.75 6a.75.75 0 000 1.5h.5a.75.75 0 000-1.5h-.5zM3 9.75A.75.75 0 013.75 9h.5a.75.75 0 010 1.5h-.5A.75.75 0 013 9.75zM7.75 9a.75.75 0 000 1.5h.5a.75.75 0 000-1.5h-.5zM7 6.75A.75.75 0 017.75 6h.5a.75.75 0 010 1.5h-.5A.75.75 0 017 6.75zM7.75 3a.75.75 0 000 1.5h.5a.75.75 0 000-1.5h-.5z"/>',
}

# ---------------------------------------------------------------- GitHub API


def graphql(token, query, variables):
    body = json.dumps({"query": query, "variables": variables}).encode()
    req = urllib.request.Request(
        API,
        data=body,
        headers={
            "Authorization": f"bearer {token}",
            "Content-Type": "application/json",
            "User-Agent": "GiacomoGuaresi-stats-generator",
        },
    )
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            payload = json.load(resp)
    except urllib.error.HTTPError as exc:
        raise SystemExit(f"GitHub API {exc.code}: {exc.read().decode(errors='replace')}")
    if "errors" in payload:
        raise SystemExit("GitHub API errors: " + json.dumps(payload["errors"]))
    return payload["data"]


STATS_QUERY = """
query($login: String!) {
  user(login: $login) {
    createdAt
    followers { totalCount }
    pullRequests { totalCount }
    openIssues: issues(states: OPEN) { totalCount }
    closedIssues: issues(states: CLOSED) { totalCount }
    contributionsCollection { totalPullRequestReviewContributions }
    repositoriesContributedTo(
      first: 1
      contributionTypes: [COMMIT, ISSUE, PULL_REQUEST, REPOSITORY]
    ) { totalCount }
    repositories(first: 100, ownerAffiliations: OWNER, isFork: false) {
      totalCount
      nodes { stargazerCount }
    }
  }
}
"""

COMMITS_QUERY = """
query($login: String!, $from: DateTime!, $to: DateTime!) {
  user(login: $login) {
    contributionsCollection(from: $from, to: $to) {
      totalCommitContributions
      restrictedContributionsCount
    }
  }
}
"""

LANGS_QUERY = """
query($login: String!, $after: String) {
  user(login: $login) {
    repositories(first: 100, ownerAffiliations: OWNER, isFork: false, after: $after) {
      pageInfo { hasNextPage endCursor }
      nodes {
        languages(first: 10, orderBy: {field: SIZE, direction: DESC}) {
          edges { size node { name color } }
        }
      }
    }
  }
}
"""


def fetch_stats(token, login):
    user = graphql(token, STATS_QUERY, {"login": login})["user"]
    created_year = int(user["createdAt"][:4])
    this_year = __import__("datetime").datetime.now(__import__("datetime").timezone.utc).year

    commits = 0
    for year in range(created_year, this_year + 1):
        data = graphql(
            token,
            COMMITS_QUERY,
            {
                "login": login,
                "from": f"{year}-01-01T00:00:00Z",
                "to": f"{year}-12-31T23:59:59Z",
            },
        )["user"]["contributionsCollection"]
        commits += data["totalCommitContributions"] + data["restrictedContributionsCount"]

    return {
        "stars": sum(r["stargazerCount"] for r in user["repositories"]["nodes"]),
        "commits": commits,
        "prs": user["pullRequests"]["totalCount"],
        "issues": user["openIssues"]["totalCount"] + user["closedIssues"]["totalCount"],
        "reviews": user["contributionsCollection"]["totalPullRequestReviewContributions"],
        "contributed": user["repositoriesContributedTo"]["totalCount"],
        "repos": user["repositories"]["totalCount"],
        "followers": user["followers"]["totalCount"],
    }


def fetch_languages(token, login, hide=(), count=6):
    hidden = {h.lower() for h in hide}
    totals, colors = {}, {}
    after = None
    while True:
        repos = graphql(token, LANGS_QUERY, {"login": login, "after": after})["user"]["repositories"]
        for repo in repos["nodes"]:
            for edge in repo["languages"]["edges"]:
                name = edge["node"]["name"]
                if name.lower() in hidden:
                    continue
                totals[name] = totals.get(name, 0) + edge["size"]
                colors.setdefault(name, edge["node"]["color"] or "#858585")
        if not repos["pageInfo"]["hasNextPage"]:
            break
        after = repos["pageInfo"]["endCursor"]

    top = sorted(totals.items(), key=lambda kv: kv[1], reverse=True)[:count]
    total = sum(size for _, size in top) or 1
    return [
        {"name": name, "color": colors[name], "share": size / total}
        for name, size in top
    ]


# ------------------------------------------------------------------- ranking


def calculate_rank(stats):
    """Port of the github-readme-stats v2 rank so the badge keeps its meaning."""

    def exponential_cdf(x):
        return 1 - 2 ** -x

    def log_normal_cdf(x):
        return x / (1 + x) if x <= 0 else math.log(x + 1) / (1 + math.log(x + 1))

    weights = (
        (2, exponential_cdf(stats["commits"] / 1000)),
        (3, exponential_cdf(stats["prs"] / 50)),
        (1, exponential_cdf(stats["issues"] / 25)),
        (1, exponential_cdf(stats["reviews"] / 2)),
        (4, log_normal_cdf(stats["stars"] / 50)),
        (1, log_normal_cdf(stats["followers"] / 10)),
    )
    rank = 1 - sum(w * v for w, v in weights) / 12

    thresholds = [1, 12.5, 25, 37.5, 50, 62.5, 75, 87.5, 100]
    levels = ["S", "A+", "A", "A-", "B+", "B", "B-", "C+", "C"]
    percentile = rank * 100
    level = next(
        (lvl for thr, lvl in zip(thresholds, levels) if percentile <= thr), levels[-1]
    )
    return {"level": level, "percentile": percentile}


# ------------------------------------------------------------------ renderer


def svg_header(width, height, title):
    return (
        f'<svg xmlns="http://www.w3.org/2000/svg" width="{width}" height="{height}" '
        f'viewBox="0 0 {width} {height}" fill="none" role="img" '
        f'aria-label="{title}">\n'
        f'  <title>{title}</title>\n'
        f'  <rect x="0.5" y="0.5" width="{width - 1}" height="{height - 1}" rx="4.5" '
        f'fill="{THEME["bg"]}" stroke="{THEME["border"]}"/>\n'
    )


def humanize(value):
    if value >= 1_000_000:
        return f"{value / 1_000_000:.1f}".rstrip("0").rstrip(".") + "M"
    if value >= 1000:
        return f"{value / 1000:.1f}".rstrip("0").rstrip(".") + "k"
    return str(value)


def render_stats_card(stats, rank):
    rows = [
        ("star", "Total Stars Earned", stats["stars"]),
        ("commit", "Total Commits", stats["commits"]),
        ("pr", "Total PRs", stats["prs"]),
        ("issue", "Total Issues", stats["issues"]),
        ("repo", "Contributed to", stats["contributed"]),
    ]
    height = 45 + LINE_HEIGHT * (len(rows) - 1) + 20
    out = [svg_header(CARD_WIDTH, height, "Giacomo Guaresi's GitHub stats")]

    y = 45
    for icon, label, value in rows:
        out.append(
            f'  <g transform="translate(25 {y - 12})" fill="{THEME["icon"]}">'
            f'<svg width="16" height="16" viewBox="0 0 16 16">{ICONS[icon]}</svg></g>\n'
            f'  <text x="50" y="{y}" font-family="{FONT}" font-size="14" font-weight="600" '
            f'fill="{THEME["text"]}">{label}:</text>\n'
            f'  <text x="270" y="{y}" font-family="{FONT}" font-size="14" font-weight="600" '
            f'fill="{THEME["title"]}">{humanize(value)}</text>\n'
        )
        y += LINE_HEIGHT

    # Rank badge: the ring fills clockwise the closer the percentile is to the top.
    cx, cy, r = 400, height / 2, 40
    circumference = 2 * math.pi * r
    progress = max(0.0, min(1.0, 1 - rank["percentile"] / 100))
    out.append(
        f'  <circle cx="{cx}" cy="{cy}" r="{r}" fill="none" stroke="{THEME["track"]}" '
        f'stroke-width="6"/>\n'
        f'  <circle cx="{cx}" cy="{cy}" r="{r}" fill="none" stroke="{THEME["icon"]}" '
        f'stroke-width="6" stroke-linecap="round" '
        f'stroke-dasharray="{circumference:.2f}" '
        f'stroke-dashoffset="{circumference * (1 - progress):.2f}" '
        f'transform="rotate(-90 {cx} {cy})"/>\n'
        f'  <text x="{cx}" y="{cy + 8}" text-anchor="middle" '
        f'font-family="{FONT}" font-size="24" font-weight="700" '
        f'fill="{THEME["title"]}">{rank["level"]}</text>\n'
    )
    out.append("</svg>\n")
    return "".join(out)


def render_top_langs_card(langs):
    bar_x, bar_w, bar_y, bar_h = 25, CARD_WIDTH - 50, 45, 8
    columns = 2
    rows = math.ceil(len(langs) / columns) if langs else 0
    height = bar_y + bar_h + 20 + rows * 22 + 10
    out = [svg_header(CARD_WIDTH, height, "Top languages")]
    out.append(
        f'  <text x="25" y="30" font-family="{FONT}" font-size="16" font-weight="600" '
        f'fill="{THEME["title"]}">Most Used Languages</text>\n'
    )

    # Single rounded bar, clipped so each segment keeps the rounded ends.
    out.append(
        f'  <defs><clipPath id="bar"><rect x="{bar_x}" y="{bar_y}" width="{bar_w}" '
        f'height="{bar_h}" rx="{bar_h / 2}"/></clipPath></defs>\n'
        f'  <g clip-path="url(#bar)">\n'
        f'    <rect x="{bar_x}" y="{bar_y}" width="{bar_w}" height="{bar_h}" '
        f'fill="{THEME["track"]}"/>\n'
    )
    offset = float(bar_x)
    for lang in langs:
        width = bar_w * lang["share"]
        out.append(
            f'    <rect x="{offset:.2f}" y="{bar_y}" width="{width:.2f}" '
            f'height="{bar_h}" fill="{lang["color"]}"/>\n'
        )
        offset += width
    out.append("  </g>\n")

    col_w = bar_w / columns
    for index, lang in enumerate(langs):
        col, row = index % columns, index // columns
        x = bar_x + col * col_w
        y = bar_y + bar_h + 28 + row * 22
        out.append(
            f'  <circle cx="{x + 5}" cy="{y - 4}" r="5" fill="{lang["color"]}"/>\n'
            f'  <text x="{x + 18}" y="{y}" font-family="{FONT}" font-size="13" '
            f'font-weight="600" fill="{THEME["text"]}">{escape(lang["name"])} '
            f'{lang["share"] * 100:.2f}%</text>\n'
        )
    out.append("</svg>\n")
    return "".join(out)


def escape(text):
    return (
        text.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
    )


# ---------------------------------------------------------------------- main


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--user", default="GiacomoGuaresi")
    parser.add_argument("--out", default="img/stats")
    parser.add_argument("--hide", default="shell", help="comma separated languages to skip")
    parser.add_argument("--langs-count", type=int, default=6)
    args = parser.parse_args()

    token = os.environ.get("STATS_TOKEN") or os.environ.get("GITHUB_TOKEN")
    if not token:
        sys.exit("Set STATS_TOKEN or GITHUB_TOKEN to a GitHub token with public read access.")

    stats = fetch_stats(token, args.user)
    rank = calculate_rank(stats)
    langs = fetch_languages(
        token, args.user, hide=[h for h in args.hide.split(",") if h], count=args.langs_count
    )

    os.makedirs(args.out, exist_ok=True)
    for name, content in (
        ("stats.svg", render_stats_card(stats, rank)),
        ("top-langs.svg", render_top_langs_card(langs)),
    ):
        path = os.path.join(args.out, name)
        with open(path, "w", encoding="utf-8") as handle:
            handle.write(content)
        print(f"wrote {path}")

    print(f"rank {rank['level']} ({rank['percentile']:.2f} percentile) — {stats}")


if __name__ == "__main__":
    main()
