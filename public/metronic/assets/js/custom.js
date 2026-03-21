(function ($) {

  "use strict";

    // PRE LOADER — hide on window load, with a hard timeout fallback.
    // Edge + lazy-loaded images can defer or stall the "load" event; without fallback the overlay never clears.
    (function () {
      var hidden = false;
      function hidePreloader() {
        if (hidden) {
          return;
        }
        hidden = true;
        $('.preloader').stop(true, true).fadeOut(500);
      }
      $(function () {
        $(window).on('load', hidePreloader);
        window.setTimeout(hidePreloader, 1200);
      });
    })();


    // MENU
    $('.navbar-collapse a').on('click',function(){
      $(".navbar-collapse").collapse('hide');
    });

    $(window).scroll(function() {
      if ($(".navbar").offset().top > 50) {
        $(".navbar-fixed-top").addClass("top-nav-collapse");
          } else {
            $(".navbar-fixed-top").removeClass("top-nav-collapse");
          }
    });


    // PARALLAX EFFECT
    $.stellar({
      horizontalScrolling: false,
    }); 


    // ABOUT SLIDER
    $('.owl-carousel').owlCarousel({
      animateOut: 'fadeOut',
      items: 1,
      loop: true,
      autoplayHoverPause: false,
      autoplay: true,
      smartSpeed: 1000,
    });


    // SMOOTHSCROLL
    $(function() {
      $('.custom-navbar a').on('click', function(event) {
        var $anchor = $(this);
          $('html, body').stop().animate({
            scrollTop: $($anchor.attr('href')).offset().top - 49
          }, 1000);
            event.preventDefault();
      });
    });  

})(jQuery);
