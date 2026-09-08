const textIta = `
    Nato nel 1998 a Milano.
    Fin da piccolo ho sviluppato una forte curiosita' verso la tecnologia e il funzionamento dei sistemi complessi. Ho passato molto tempo a smontare e ricostruire oggetti elettronici, un’attitudine che mi ha portato naturalmente a scoprire la passione per la programmazione e l’ingegneria dei sistemi.
    Con il tempo ho iniziato a dedicarmi allo sviluppo software e all’elettronica, sperimentando tra circuiti, microcontrollori e codice. Questa passione mi ha spinto a intraprendere un percorso di studi in Informatica, dove ho potuto approfondire concetti di Programmazione, ingegneria e progettazione elettronica.
    Oggi mi occupo di sviluppo full-stack e di architetture cloud, senza mai abbandonare l’interesse per l’embedded e la progettazione PCB. Mi affascina tanto la parte pratica e di tinkering quanto quella concettuale e creativa, perché mi permettono di avere una visione completa dei progetti a cui lavoro.
    Amo condividere le mie conoscenze con la community open-source, collaborare a nuove idee e costruire soluzioni innovative. Il mio obiettivo è unire software, hardware e creativita' per dare vita a sistemi che siano al tempo stesso efficienti e stimolanti.
`;

const textEng = `
    Born in Milan in 1998.
    From an early age, I developed a strong curiosity about technology and how complex systems work. I spent a lot of time taking apart and rebuilding electronic devices, an aptitude that naturally led me to discover a passion for programming and systems engineering.
    Over time, I began to devote myself to software development and electronics, experimenting with circuits, microcontrollers, and code. This passion led me to pursue a degree in Computer Science, where I was able to deepen my understanding of programming, engineering, and electronic design concepts.
    Today, I work in full-stack development and cloud architecture, without ever abandoning my interest in embedded systems and PCB design. I am fascinated by both the practical and tinkering aspects and the conceptual and creative aspects, because they allow me to have a complete vision of the projects I work on.
    I love sharing my knowledge with the open-source community, collaborating on new ideas, and building innovative solutions. My goal is to combine software, hardware, and creativity to create systems that are both efficient and inspiring.
`;


var SplashString = "GIACOMO_GUARESI";
const MarqueeSpeed = 150; // px per second, shared by every marquee
var darkMode = false;

window.addEventListener('hashchange', function () {
    changePage();
})

$(document).ready(function () {
    $("#aboutText").html(textIta);
    changePage();
    recalcSize();

    loadingChangeLecter();

    initMarquees();

    // Web fonts land after ready() and change how wide the text measures,
    // so lay the marquees out again once they are in.
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(initMarquees);
    }


    //Tema chiaro/scuro mobile 
    if ($(window).width() < 1023) {
        var t = new Date()
        if (t.getHours() % 2 == 1) {
            changeMode();
        }
    }

    $("#phoneNumberClick").click(function () {
        var topVal = $("#phoneNumberClick").offset().top - 30;


        $(".phonePopupNumber").css("top", topVal + "px");
        $(".phonePopupContainer").fadeIn();
    });

    $(".phonePopupContainer").click(function () {
        $(".phonePopupContainer").fadeOut();
    });

});

$(window).resize(function () {
    recalcSize();
    window.location.reload();
});

var i = 0;

// Builds (or rebuilds) every marquee: the authored .content is repeated until
// one half of the track covers the container, the whole half is duplicated so
// the CSS -50% slide loops seamlessly, and the duration is derived from the
// half width so long and short marquees scroll at the same speed.
function initMarquees() {
    $(".marquee, .marquee-reversed").each(function () {
        var $marquee = $(this);

        // The track gets thrown away on every rebuild, so keep the original.
        if ($marquee.data("marqueeSource") === undefined) {
            var $source = $marquee.find(".content").first();
            $marquee.data("marqueeSource", $source.length ? $source[0].outerHTML : "");
        }

        var source = $marquee.data("marqueeSource");
        var containerWidth = this.clientWidth;
        if (!source || !containerWidth) {
            return; // Hidden or not laid out yet; changePage() calls us again.
        }

        var $track = $('<div class="marquee-track"></div>').html(source);
        $marquee.empty().append($track);

        var contentWidth = $track.children(".content")[0].getBoundingClientRect().width;
        if (!contentWidth) {
            return;
        }

        // One extra copy of the content absorbs sub-pixel rounding, otherwise a
        // sliver of empty space shows up just before the loop restarts.
        var copies = Math.ceil(containerWidth / contentWidth) + 1;
        var halfWidth = copies * contentWidth;

        $track.html(new Array(copies * 2).fill(source).join(""));
        this.style.setProperty("--marquee-duration", halfWidth / MarqueeSpeed + "s");
    });
}

function loadingChangeLecter() {
    if(SplashString.charAt(i) == "_")
        $(".centerLecter").attr("src", "img\\lightLoadingImgs\\SPACE.svg");        
    else 
        $(".centerLecter").attr("src", "img\\lightLoadingImgs\\" + SplashString.charAt(i) + ".svg");        
    
    i++;
    if (i < SplashString.length)
        setTimeout(loadingChangeLecter, 100);
    else
        $(".part-loading").fadeOut();
}

function changeLang(lang) {
    if (lang == "ENG") {
        $("#aboutText").html(textEng);
        $("#AboutLangIta").text("ITA");
        $("#AboutLangEng").text("( ENG )");
    } else {
        $("#aboutText").html(textIta);
        $("#AboutLangIta").text("( ITA )");
        $("#AboutLangEng").text("ENG");
    }

}

function changePage() {
    var hash = window.location.hash;
    var notHome = false;

    // Pagina Skills 
    if (hash == "#Skills") {
        notHome = true;
        $("#pageSkills").fadeIn();
        $("#linkSkills").css("font-weight", "bold");
        var duration = 300;
        $("#pageSkills").find('.animEnter').each(function (i) {
            $(this).delay(i * (duration / 2)).animate({
                left: 0
            }, duration);
        });
    } else {
        $("#pageSkills").fadeOut();
        $("#linkSkills").css("font-weight", "normal");
        $("#pageSkills").find('.animEnter').css("left", "-100%");
    }

    // Pagina About 
    if (hash == "#About") {
        notHome = true;
        $("#pageAbout").fadeIn();
        $("#linkAbout").css("font-weight", "bold");
        $("#AboutLangSelector").show();
    } else {
        $("#pageAbout").fadeOut();
        $("#linkAbout").css("font-weight", "normal");
        $("#AboutLangSelector").hide();
    }

    // Pagina Works 
    if (hash == "#Works") {
        notHome = true;
        $("#pageWorks").fadeIn();
        $("#linkWorks").css("font-weight", "bold");
        var duration = 300;
        $("#pageWorks").find('.animEnter').each(function (i) {
            $(this).delay(i * (duration / 2)).animate({
                left: 0
            }, duration);
        });
    } else {
        $("#pageWorks").fadeOut();
        $("#linkWorks").css("font-weight", "normal");
        $("#pageWorks").find('.animEnter').css("left", "-100%");
    }

    // Pagina Contact 
    if (hash == "#Contact") {
        notHome = true;
        $("#pageContact").fadeIn();
        $("#linkContact").css("font-weight", "bold");
        // $('head').append('<link rel="stylesheet" href="css/styleContact.css" id="styleContact" type="text/css" />');

    } else {
        $("#pageContact").fadeOut();
        $("#linkContact").css("font-weight", "normal");
        // $("#styleContact").remove();
    }


    // Pagina Home
    if (notHome)
        $("#pageHome").fadeOut();
    else
        $("#pageHome").fadeIn();

    $(".phonePopupContainer").fadeOut();

    // The mobile contact marquees live in a hidden page: they measure 0 wide
    // until it is shown, so lay them out now that it is.
    initMarquees();
}

function recalcSize() {
    var height = ($(".part-body-ContactMobile").height() / 3) + "px";
    $(".part-body-ContactMobile").find(".textExtraLarge").css("font-size", height).css("line-height", height);
    $(".part-body-ContactMobile").find(".row").css("height", height);
    $(".part-body-ContactMobile").find(".marquee").css("height", height);


    var height = ($(".part-body-Contact").height() / 3) + "px";
    $(".part-body-Contact").find(".textExtraLarge").css("font-size", height).css("line-height", height);
}

function changeMode() {
    if (darkMode) {
        $('#styleDark').remove();
        $("#modeIcon").attr("src", "img/LightMode.svg");
        $("#homeButton").attr("src", "img/LightHomeButton.svg");
        $("#DownloadBtn").attr("src", "img/LightDownloadIcon.svg");
    } else {
        $('head').append('<link rel="stylesheet" href="css/styleDark.css" id="styleDark" type="text/css" />');
        $("#modeIcon").attr("src", "img/DarkMode.svg");
        $("#homeButton").attr("src", "img/DarkHomeButton.svg");
        $("#DownloadBtn").attr("src", "img/DarkDownloadIcon.svg");
    }
    darkMode = !darkMode;
}