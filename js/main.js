const SPLASH_COMMAND = "whoami";
const SPLASH_TYPE_MS = 70; // per character of the command
const SPLASH_PAUSE_MS = 250; // between pressing enter and the output
const SPLASH_HOLD_MS = 650; // name on screen before the fade
const MARQUEE_SPEED = 150; // px per second, shared by every marquee
// The same pixel speed crosses a phone screen in a third of the time, too fast
// to read, so narrow screens get their own. Same breakpoint as styleMobile.css.
const MARQUEE_SPEED_NARROW = 60;
const NARROW_SCREEN = "(max-width: 720px)";
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

/* --- preferences ---
   Blocked site data throws on access rather than returning null, so both ends
   are guarded; the site has to work with no stored preference either way.
   The inline script in index.html reads the same keys before the first paint
   and leaves its decision on the root element. */

function writePref(key, value) {
    try {
        localStorage.setItem(key, value);
    } catch (e) {
        /* preferences are a convenience, not a requirement */
    }
}

function splashSeen() {
    try {
        return sessionStorage.getItem("splash") !== null;
    } catch (e) {
        return false;
    }
}

function markSplashSeen() {
    try {
        sessionStorage.setItem("splash", "1");
    } catch (e) {
        /* worst case the splash plays again on the next page load */
    }
}

/* --- language --- */

function changeLang(lang) {
    const isEnglish = lang !== "ITA";
    const code = isEnglish ? "en" : "it";

    document.documentElement.setAttribute("data-lang", code);
    document.documentElement.setAttribute("lang", code);

    const eng = document.getElementById("langEng");
    const ita = document.getElementById("langIta");
    eng.classList.toggle("is-current", isEnglish);
    ita.classList.toggle("is-current", !isEnglish);
    eng.setAttribute("aria-pressed", String(isEnglish));
    ita.setAttribute("aria-pressed", String(!isEnglish));

    document.getElementById("cvLink").setAttribute(
        "href", isEnglish ? "cv/Giacomo_Guaresi_CV_EN.pdf" : "cv/Giacomo_Guaresi_CV_IT.pdf"
    );

    writePref("lang", code);

    // Translations are not the same width, so the marquee track has to be
    // rebuilt around the text that is actually on screen.
    initMarquees();
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
    const speed = window.matchMedia(NARROW_SCREEN).matches ? MARQUEE_SPEED_NARROW : MARQUEE_SPEED;

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
        marquee.style.setProperty("--marquee-duration", halfWidth / speed + "s");
    });
}

/* --- splash ---
   A shell types `whoami` and prints the name, then fades out. Any click or key
   skips straight to the fade, and reduced motion skips the splash entirely.
   It plays once per session: on every later page load it is a second and a
   half between the visitor and the content. */

function runSplash() {
    const splash = document.querySelector(".part-loading");
    const cmd = splash.querySelector(".splash-cmd");
    const out = splash.querySelector(".splash-out");
    const cursor = splash.querySelector(".splash-cursor");
    let timer;
    let done = false;

    function finish() {
        if (done) {
            return;
        }
        done = true;
        clearTimeout(timer);
        document.removeEventListener("pointerdown", finish);
        document.removeEventListener("keydown", finish);
        splash.style.transition = "opacity 0.4s ease";
        splash.style.opacity = "0";
        setTimeout(function () { splash.remove(); }, 400);
    }

    if (splashSeen() || window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
        splash.remove();
        return;
    }
    markSplashSeen();

    document.addEventListener("pointerdown", finish);
    document.addEventListener("keydown", finish);

    let i = 0;
    (function type() {
        cmd.textContent = SPLASH_COMMAND.slice(0, i);
        i++;

        if (i <= SPLASH_COMMAND.length) {
            timer = setTimeout(type, SPLASH_TYPE_MS);
            return;
        }

        timer = setTimeout(function () {
            cursor.style.visibility = "hidden"; // the output line has its own
            out.style.visibility = "visible";
            timer = setTimeout(finish, SPLASH_HOLD_MS);
        }, SPLASH_PAUSE_MS);
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
    const root = document.documentElement;

    // Stored preference, or the browser's language on a first visit: the head
    // script has already worked it out.
    changeLang(root.getAttribute("data-lang") === "it" ? "ITA" : "ENG");

    document.getElementById("langEng").addEventListener("click", function () { changeLang("ENG"); });
    document.getElementById("langIta").addEventListener("click", function () { changeLang("ITA"); });

    initStagger();
    changePage();
    runSplash();

    // Web fonts land after this point and change how wide the text measures, so
    // lay the marquees out again once they are in.
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(initMarquees);
    }
});
