// var vlastDate = Math.floor(new Date().getTime() / 1000) - 86400; 

// const def_timeSpan = {
//     "1h":60*1,
//     "6h":60*6,
//     "12h":60*12,
//     "1g":60*24,
//     "2g":60*24*2
// }


(function($) {
  "use strict"; // Start of use strict

  // Toggle the side navigation
  $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
    $("body").toggleClass("sidebar-toggled");
    $(".sidebar").toggleClass("toggled");
    if ($(".sidebar").hasClass("toggled")) {
      $('.sidebar .collapse').collapse('hide');
    };
  });

  // Close any open menu accordions when window is resized below 768px
  $(window).resize(function() {
    if ($(window).width() < 768) {
      $('.sidebar .collapse').collapse('hide');
    };
  });

  // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
  $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
    if ($(window).width() > 768) {
      var e0 = e.originalEvent,
        delta = e0.wheelDelta || -e0.detail;
      this.scrollTop += (delta < 0 ? 1 : -1) * 30;
      e.preventDefault();
    }
  });

  // Scroll to top button appear
  $(document).on('scroll', function() {
    var scrollDistance = $(this).scrollTop();
    if (scrollDistance > 100) {
      $('.scroll-to-top').fadeIn();
    } else {
      $('.scroll-to-top').fadeOut();
    }
  });

  // Smooth scrolling using jQuery easing
  $(document).on('click', 'a.scroll-to-top', function(e) {
    var $anchor = $(this);
    $('html, body').stop().animate({
      scrollTop: ($($anchor.attr('href')).offset().top)
    }, 1000, 'easeInOutExpo');
    e.preventDefault();
  });


  // Chiama Update View
  window.setInterval(function(){
    updateView();
  }, 5000);
  
  

  setTimeout( function(){ 
    updateView();
  }  , 500 );


})(jQuery); // End of use strict


function updateView(){

  $.ajax({
    url: "php/api/get_vars.php",
    context: document.body
  }).done(function(data) {
      
    data = JSON.parse(data);
    var d = Date(Date.now()); 
    $("#lastUpdateTime").text(d.toString());

    for (i = 0; i < functionStack.length; i++) {
        functionStack[i](data);
    }

  });
}