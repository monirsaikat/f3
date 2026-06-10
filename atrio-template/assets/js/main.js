/* ============================================================
   ATRIO — main.js (jQuery)
   Sticky header, mobile nav, counters, testimonial slider,
   portfolio filter, contact form validation, back-to-top,
   scroll reveal, active nav highlight
   ============================================================ */

(function ($) {
  "use strict";

  $(function () {

    /* ---------- Active nav link by filename ---------- */
    var page = window.location.pathname.split("/").pop() || "index.html";
    $(".main-nav a").each(function () {
      if ($(this).attr("href") === page) $(this).addClass("active");
    });

    /* ---------- Sticky header shadow ---------- */
    var $header = $(".site-header");
    function onScrollHeader() {
      $header.toggleClass("scrolled", $(window).scrollTop() > 10);
    }
    $(window).on("scroll", onScrollHeader);
    onScrollHeader();

    /* ---------- Mobile nav toggle ---------- */
    $(".nav-toggle").on("click", function () {
      $(this).toggleClass("open");
      $(".main-nav").toggleClass("open");
      $(this).attr("aria-expanded", $(".main-nav").hasClass("open"));
    });

    /* ---------- Back to top ---------- */
    var $top = $("#backToTop");
    $(window).on("scroll", function () {
      $top.toggleClass("show", $(window).scrollTop() > 500);
    });
    $top.on("click", function () {
      $("html, body").animate({ scrollTop: 0 }, 500);
    });

    /* ---------- Scroll reveal ---------- */
    function reveal() {
      var winBottom = $(window).scrollTop() + $(window).height();
      $(".reveal").each(function () {
        if (winBottom > $(this).offset().top + 60) $(this).addClass("visible");
      });
    }
    $(window).on("scroll resize", reveal);
    reveal();

    /* ---------- Animated counters ---------- */
    var countersDone = false;
    function runCounters() {
      if (countersDone || !$(".counter").length) return;
      var first = $(".counter").first();
      var trigger = first.offset().top - $(window).height() + 80;
      if ($(window).scrollTop() > trigger) {
        countersDone = true;
        $(".counter").each(function () {
          var $el = $(this);
          var target = parseInt($el.data("count"), 10);
          $({ val: 0 }).animate({ val: target }, {
            duration: 1800,
            easing: "swing",
            step: function (now) { $el.text(Math.floor(now)); },
            complete: function () { $el.text(target); }
          });
        });
      }
    }
    $(window).on("scroll", runCounters);
    runCounters();

    /* ---------- Testimonial slider ---------- */
    var $slides = $(".testi-slide");
    if ($slides.length) {
      var current = 0, timer = null;

      function showSlide(i) {
        current = (i + $slides.length) % $slides.length;
        $slides.removeClass("active").eq(current).addClass("active");
      }
      function autoplay() {
        clearInterval(timer);
        timer = setInterval(function () { showSlide(current + 1); }, 6000);
      }
      $(".testi-next").on("click", function () { showSlide(current + 1); autoplay(); });
      $(".testi-prev").on("click", function () { showSlide(current - 1); autoplay(); });
      showSlide(0);
      autoplay();
    }

    /* ---------- Portfolio filter ---------- */
    $(".filter-btn").on("click", function () {
      var filter = $(this).data("filter");
      $(".filter-btn").removeClass("active");
      $(this).addClass("active");

      if (filter === "all") {
        $(".work-col").fadeOut(150).promise().done(function () {
          $(".work-col").fadeIn(280);
        });
      } else {
        $(".work-col").fadeOut(150).promise().done(function () {
          $('.work-col[data-category="' + filter + '"]').fadeIn(280);
        });
      }
    });

    /* ---------- Contact form validation ---------- */
    $("#contactForm").on("submit", function (e) {
      e.preventDefault();
      var valid = true;

      function check($field, condition) {
        var $err = $field.closest(".mb-4, .mb-3, .col-md-6, .col-12").find(".field-error");
        if (!condition) {
          $field.addClass("is-invalid");
          $err.show();
          valid = false;
        } else {
          $field.removeClass("is-invalid");
          $err.hide();
        }
      }

      var $name = $("#cf-name"), $email = $("#cf-email"),
          $subject = $("#cf-subject"), $message = $("#cf-message");

      check($name, $.trim($name.val()).length >= 2);
      check($email, /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($.trim($email.val())));
      check($subject, $.trim($subject.val()).length >= 3);
      check($message, $.trim($message.val()).length >= 10);

      if (valid) {
        // Replace this block with your AJAX call / backend endpoint
        $("#contactForm")[0].reset();
        $(".form-success").slideDown(250);
        setTimeout(function () { $(".form-success").slideUp(250); }, 6000);
      }
    });

    $("#contactForm .form-control").on("input", function () {
      $(this).removeClass("is-invalid");
      $(this).closest(".mb-4, .mb-3, .col-md-6, .col-12").find(".field-error").hide();
    });

  });

})(jQuery);
