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
const MarqueeSpeed = 0.15;
var darkMode = false;

window.addEventListener('hashchange', function () {
    changePage();
})

$(document).ready(function () {
    $("#aboutText").html(textIta);
    changePage();
    recalcSize();

    loadingChangeLecter();

    $(".marquee").each(function (i) {
        var width = $(this).width();
        var contWidth = $(this).find(".content").width();
        $(this).find(".content").css("left", -contWidth + "px");

        time = ((contWidth + width) / MarqueeSpeed);
        runMarqueeAnimation(this, width, time);

        //ripeti effetto
        var numDelay = time / ((contWidth + width) / contWidth);
        window.setInterval(runMarqueeAnimation, numDelay, this, width, time);

    });


    $(".marquee-reversed").each(function (i) {
        var width = $(this).width();
        var contWidth = $(this).find(".content").width();
        $(this).find(".content").css("right", -contWidth + "px");

        time = ((contWidth + width) / MarqueeSpeed);
        runMarqueeAnimationReversed(this, width, time);

        //ripeti effetto
        var numDelay = time / ((contWidth + width) / contWidth);
        window.setInterval(runMarqueeAnimationReversed, numDelay, this, width, time);

    });


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

function runMarqueeAnimation(thisObj, width, time) {
    if (width > 0) {

        $(thisObj).find(".content").first().clone().appendTo(thisObj).animate({
            left: width + 'px'
        }, time, "linear", function () {
            $(this).remove();
        });
    }
}

function runMarqueeAnimationReversed(thisObj, width, time) {
    if (width > 0) {

        $(thisObj).find(".content").first().clone().appendTo(thisObj).animate({
            right: width + 'px'
        }, time, "linear", function () {
            $(this).remove();
        });
    }
}

function loadingChangeLecter() {
    if (darkMode)
        $(".centerLecter").attr("src", "img\\darkLoadingImgs\\" + SplashString.charAt(i) + ".svg");
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
}

function recalcSize() {
    var height = ($(".part-body-ContactMobile").height() / 3) + "px";
    $(".part-body-ContactMobile").find(".textExtraLarge").css("font-size", height).css("line-height", height);
    $(".part-body-ContactMobile").find(".row").css("height", height);
    $(".part-body-ContactMobile").find(".marquee").css("height", height);


    var height = ($(".part-body-Contact").height() / 3) + "px";
    $(".part-body-Contact").find(".textExtraLarge").css("font-size", height).css("line-height", height);
}

function callMarqueeAnimation() {
    $(this).find(".content").clone().animate({
        left: width + 'px'
    }, {
        duration: 5000,
        specialEasing: {
            width: "linear",
        },
        complete: function () {
            $(this).remove();
        }
    });
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