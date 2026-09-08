const SPLASH_STRING = "GIACOMO_GUARESI";
const SPLASH_FRAME_MS = 100;
const MARQUEE_SPEED = 150; // px per second, shared by every marquee
const STAGGER_MS = 60;

const PAGES = {
    "#Stack": "pageStack",
    "#Work": "pageWork",
    "#Projects": "pageProjects",
    "#About": "pageAbout",
    "#Contact": "pageContact"
};

const NAV_LINKS = {
    "#Stack": "linkStack",
    "#Work": "linkWork",
    "#Projects": "linkProjects",
    "#About": "linkAbout",
    "#Contact": "linkContact"
};

let darkMode = false;

/* --- preferences ---
   Blocked site data throws on access rather than returning null, so both ends
   are guarded; the site has to work with no stored preference either way. */

function readPref(key) {
    try {
        return localStorage.getItem(key);
    } catch (e) {
        return null;
    }
}

function writePref(key, value) {
    try {
        localStorage.setItem(key, value);
    } catch (e) {
        /* preferences are a convenience, not a requirement */
    }
}

/* --- language --- */

function changeLang(lang) {
    const isEnglish = lang !== "ITA";
    const code = isEnglish ? "en" : "it";

    document.documentElement.setAttribute("data-lang", code);
    document.documentElement.setAttribute("lang", code);

    document.getElementById("langEng").classList.toggle("is-current", isEnglish);
    document.getElementById("langIta").classList.toggle("is-current", !isEnglish);

    document.getElementById("cvLink").setAttribute(
        "href", isEnglish ? "cv/Giacomo_Guaresi_CV_EN.pdf" : "cv/Giacomo_Guaresi_CV_IT.pdf"
    );

    writePref("lang", code);

    // Translations are not the same width, so the marquee track has to be
    // rebuilt around the text that is actually on screen.
    initMarquees();
}

/* --- theme --- */

function changeMode() {
    darkMode = !darkMode;
    applyMode();
    writePref("theme", darkMode ? "dark" : "light");
}

function applyMode() {
    const existing = document.getElementById("styleDark");

    if (darkMode && !existing) {
        const link = document.createElement("link");
        link.rel = "stylesheet";
        link.type = "text/css";
        link.id = "styleDark";
        link.href = "css/styleDark.css";
        document.head.appendChild(link);
    } else if (!darkMode && existing) {
        existing.remove();
    }

    const variant = darkMode ? "Dark" : "Light";
    document.getElementById("modeIcon").src = "img/" + variant + "Mode.svg";
    document.getElementById("homeButton").src = "img/" + variant + "HomeButton.svg";
    document.getElementById("DownloadBtn").src = "img/" + variant + "DownloadIcon.svg";
}

/* --- routing --- */

function changePage() {
    const hash = window.location.hash;
    const activeId = PAGES[hash] || "pageHome";

    document.querySelectorAll(".panel").forEach(function (panel) {
        panel.classList.toggle("is-visible", panel.id === activeId);
    });

    Object.keys(NAV_LINKS).forEach(function (key) {
        document.getElementById(NAV_LINKS[key]).classList.toggle("is-current", key === hash);
    });

    // A panel that was hidden measures zero wide, so its marquees can only be
    // laid out once it is on screen.
    initMarquees();
}

/* --- entrance animation ---
   The stagger is a per-element transition delay; style.css does the movement. */

function initStagger() {
    document.querySelectorAll(".panel").forEach(function (panel) {
        panel.querySelectorAll(".animEnter").forEach(function (el, index) {
            el.style.transitionDelay = index * STAGGER_MS + "ms";
        });
    });
}

/* --- marquee ---
   Repeats the authored .content until one half of the track covers the
   container, duplicates that half so the CSS -50% slide loops seamlessly, and
   derives the duration from the half width so every marquee scrolls at the
   same pixel speed whatever its content is. */

function initMarquees() {
    document.querySelectorAll(".marquee, .marquee-reversed").forEach(function (marquee) {
        // The track is thrown away on every rebuild, so keep the original.
        if (marquee.dataset.marqueeSource === undefined) {
            const source = marquee.querySelector(".content");
            marquee.dataset.marqueeSource = source ? source.outerHTML : "";
        }

        const source = marquee.dataset.marqueeSource;
        const containerWidth = marquee.clientWidth;
        if (!source || !containerWidth) {
            return; // Hidden or not laid out yet; changePage() calls us again.
        }

        const track = document.createElement("div");
        track.className = "marquee-track";
        track.innerHTML = source;
        marquee.replaceChildren(track);

        const contentWidth = track.firstElementChild.getBoundingClientRect().width;
        if (!contentWidth) {
            return;
        }

        // One extra copy absorbs sub-pixel rounding, otherwise a sliver of empty
        // space shows up just before the loop restarts.
        const copies = Math.ceil(containerWidth / contentWidth) + 1;
        const halfWidth = copies * contentWidth;

        track.innerHTML = new Array(copies * 2).fill(source).join("");
        marquee.style.setProperty("--marquee-duration", halfWidth / MARQUEE_SPEED + "s");
    });
}

/* --- splash --- */

function runSplash() {
    const letter = document.querySelector(".centerLecter");
    const folder = darkMode ? "darkLoadingImgs" : "lightLoadingImgs";
    let i = 0;

    (function next() {
        const char = SPLASH_STRING.charAt(i);
        letter.src = "img/" + folder + "/" + (char === "_" ? "SPACE" : char) + ".svg";
        i++;

        if (i < SPLASH_STRING.length) {
            setTimeout(next, SPLASH_FRAME_MS);
        } else {
            const splash = document.querySelector(".part-loading");
            splash.style.transition = "opacity 0.4s ease";
            splash.style.opacity = "0";
            setTimeout(function () { splash.remove(); }, 400);
        }
    })();
}

/* --- boot --- */

window.addEventListener("hashchange", changePage);

let lastWidth = window.innerWidth;
let resizeTimer;

window.addEventListener("resize", function () {
    // Mobile browsers fire resize whenever the address bar collapses, which
    // changes the height only. Reacting to that would rebuild the marquees
    // mid-scroll, so only a real width change is worth acting on.
    if (window.innerWidth === lastWidth) {
        return;
    }
    lastWidth = window.innerWidth;
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(initMarquees, 200);
});

document.addEventListener("DOMContentLoaded", function () {
    const storedTheme = readPref("theme");
    darkMode = storedTheme === "dark";
    applyMode();

    const storedLang = readPref("lang");
    const preferred = storedLang || (navigator.language || "en").toLowerCase().slice(0, 2);
    changeLang(preferred === "it" ? "ITA" : "ENG");

    initStagger();
    changePage();
    runSplash();

    // Web fonts land after this point and change how wide the text measures, so
    // lay the marquees out again once they are in.
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(initMarquees);
    }
});
