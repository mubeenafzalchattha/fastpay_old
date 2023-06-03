'use strict';


$(document).ready(function () {
  // menu options custom affix
  var fixed_top = $(".header");
  $(window).on("scroll", function () {
    if ($(window).scrollTop() > 50) {
      fixed_top.addClass("animated fadeInDown menu-fixed");
    } else {
      fixed_top.removeClass("animated fadeInDown menu-fixed");
    }
  });
});

// mobile menu js
$(".navbar-collapse>ul>li>a, .navbar-collapse ul.sub-menu>li>a").on("click", function () {
  const element = $(this).parent("li");
  if (element.hasClass("open")) {
    element.removeClass("open");
    element.find("li").removeClass("open");
  } else {
    element.addClass("open");
    element.siblings("li").removeClass("open");
    element.siblings("li").find("li").removeClass("open");
  }
});

$('li.author-widget-menu-has-child>a, ul.author-widget-submenu>li.author-widget-menu-has-child>a').on('click', function (e) {
  var element = $(this).parent('li');
  if (element.hasClass('open')) {
    element.removeClass('open');
    element.find('li').removeClass('open');
    element.find('ul').slideUp(500, "swing");
  } else {
    element.addClass('open');
    element.children('ul').slideDown(800, "swing");
    element.siblings('li').children('ul').slideUp(800, "swing");
    element.siblings('li').removeClass('open');
    element.siblings('li').find('li').removeClass('open');
    element.siblings('li').find('ul').slideUp(1000, "swing");
  }
});

let img = $('.bg_img');
img.css('background-image', function () {
  let bg = ('url(' + $(this).data('background') + ')');
  return bg;
});

new WOW().init();

// Show or hide the sticky footer button
$(window).on("scroll", function () {
  if ($(this).scrollTop() > 200) {
    $(".scroll-to-top").fadeIn(200);
  } else {
    $(".scroll-to-top").fadeOut(200);
  }
});

// Animate the scroll to top
$(".scroll-to-top").on("click", function (event) {
  event.preventDefault();
  $("html, body").animate({
    scrollTop: 0
  }, 300);
});


//preloader js code
$(".preloader").delay(300).animate({
  "opacity": "0"
}, 300, function () {
  $(".preloader").css("display", "none");
});

$(function () {
  $('[data-toggle="tooltip"]').tooltip({
    boundary: 'window'
  })
})



$(".header-serch-btn").on('click', function () {
  //$(".header-top-search-area").toggleClass("open");
  if ($(this).hasClass('toggle-close')) {
    $(this).removeClass('toggle-close').addClass('toggle-open');
    $('.header-top-search-area').addClass('open');
  } else {
    $(this).removeClass('toggle-open').addClass('toggle-close');
    $('.header-top-search-area').removeClass('open');
  }
});

//close when click off of container
$(document).on('click touchstart', function (e) {
  if (!$(e.target).is('.header-serch-btn, .header-serch-btn *, .header-top-search-area, .header-top-search-area *')) {
    $('.header-top-search-area').removeClass('open');
    $('.header-serch-btn').addClass('toggle-close');
  }
});


/* ==============================
          slider area
================================= */

// testimonial slider 
$('.testimonial-slider').slick({
  dots: true,
  speed: 800,
  slidesToShow: 3,
  slidesToScroll: 1,
  arrows: false,
  centerMode: true,
  centerPadding: '0px',
  responsive: [{
    breakpoint: 1200,
    settings: {
      slidesToShow: 2,
    }
  },
  {
    breakpoint: 992,
    settings: {
      slidesToShow: 1,
    }
  },
  {
    breakpoint: 576,
    settings: {
      slidesToShow: 1,
    }
  }
  ]
});


function triggerTooltip() {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title], [data-title], [data-bs-title]'))
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  });
}

triggerTooltip();


$('.search-toggler').on('click', function () {
  $(this).parent('div').siblings('.coin-search-form').slideToggle()
})

$('.review-input').on('change', function () {
  if ($('#positive-review').is(':checked')) {
    $('.positive-label .icon').html("<i class='fas fa-thumbs-up text--success'></i>")
  } else {
    $('.positive-label .icon').html("<i class='far fa-thumbs-up'></i>")

  }
  if ($('#negative-review').is(':checked')) {
    $('.negative-label .icon').html("<i class='fas fa-thumbs-down text--danger'></i>")
  } else {
    $('.negative-label .icon').html("<i class='far fa-thumbs-down'></i>")

  }
});

function tableResponsive() {
  Array.from(document.querySelectorAll('table')).forEach(table => {
    let heading = table.querySelectorAll('thead tr th');
    Array.from(table.querySelectorAll('tbody tr')).forEach(row => {
      Array.from(row.querySelectorAll('td')).forEach((column, i) => {
        if (heading[i]) {
          (column.colSpan == 100) || column.setAttribute('data-label', heading[i].innerText)
        }
      });
    });
  });
}

tableResponsive();