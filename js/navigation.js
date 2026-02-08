// jQuery(function($) {
$(function () {

    "use strict";
    
    // ****************************
    // 
    // Desktop Menu
    // 
    // ****************************


    // Desktop header dropdown logic - consistent breakpoints and proper hover handling
    $(".desktop-header > nav > ul > li").hover(
        function (e) {
            // Mouse enter - show dropdown
            if ($(window).width() > 768) {
                $(this).children("ul").stop(true, true).fadeIn(150);
                e.preventDefault();
            }
        },
        function (e) {
            // Mouse leave - hide dropdown
            if ($(window).width() > 768) {
                $(this).children("ul").stop(true, true).fadeOut(150);
                e.preventDefault();
            }
        }
    );

    // Classify dropdown menu types based on nested ul elements
    $('nav.menu > ul > li > ul:not(:has(ul))').addClass('normal-sub');
    $('nav.menu > ul > li > ul:not(.normal-sub)').addClass('mega-menu');

    // Main navigation menu dropdown logic
    $("nav.menu > ul > li").hover(
        function (e) {
            // Mouse enter - show dropdown
            if ($(window).width() > 768) {
                $(this).children("ul").stop(true, true).fadeIn(150);
                e.preventDefault();
            }
        },
        function (e) {
            // Mouse leave - hide dropdown
            if ($(window).width() > 768) {
                $(this).children("ul").stop(true, true).fadeOut(150);
                e.preventDefault();
            }
        }
    );

    // Optional: Handle window resize to close dropdowns on mobile
    $(window).resize(function () {
        if ($(window).width() <= 768) {
            $(".desktop-header > nav > ul > li > ul, nav.menu > ul > li > ul").hide();
        }
    });

    // Optional: Close dropdowns when clicking outside (for better UX)
    $(document).click(function (e) {
        if (!$(e.target).closest('.desktop-header nav, nav.menu').length) {
            $(".desktop-header > nav > ul > li > ul, nav.menu > ul > li > ul").fadeOut(150);
        }
    });







    // ****************************
    // 
    // Mobile Menu
    // 
    // ****************************

    //if you change this breakpoint in the "nav-mobile.scss" files, don't forget to update this value as well
    // let MqL = 768;
    let MqL = 992;

    // Move nav element position according to window width
    moveNavigation();
    $(window).on('resize', function () {
        (!window.requestAnimationFrame) ? setTimeout(moveNavigation, 300) : window.requestAnimationFrame(moveNavigation);
    });

    // Open menu when icon clicked
    $('.mobile-nav-trigger').on('click', function (e) {
        e.preventDefault();
        if ($('main').hasClass('nav-is-visible')) {
            closeNav();
        } else {
            $(this).addClass('nav-is-visible');
            $('.mobile-nav').addClass('nav-is-visible');
            $('header').addClass('nav-is-visible');
            $('main').addClass('nav-is-visible');
        }
    });

    // Add CSS Classes to Submenu for has-children & go-back
    $('.mobile-nav > li:has(ul)').addClass('has-children');
    $('.mobile-nav > li > ul:has(li)').addClass('is-hidden').prepend('<li class="go-back"><a href="#0">Go Back</a></li>');

    // Prevent default clicking on direct children of .mobile-nav 
    $('.mobile-nav').children('.has-children').children('a').on('click', function (e) {
        e.preventDefault();
    });

    // Open submenu
    $('.has-children').children('a').on('click', function (e) {
        if (!checkWindowWidth()) e.preventDefault();
        let selected = $(this);
        if (selected.next('ul').hasClass('is-hidden')) {
            //desktop version only
            selected.addClass('selected').next('ul').removeClass('is-hidden').end().parent('.has-children').parent('ul').addClass('moves-out');
            selected.parent('.has-children').siblings('.has-children').children('ul').addClass('is-hidden').end().children('a').removeClass('selected');
        } else {
            selected.removeClass('selected').next('ul').addClass('is-hidden').end().parent('.has-children').parent('ul').removeClass('moves-out');
        }
    });

    // Submenu "go back link"
    $('.go-back').on('click', function () {
        $(this).parent('ul').addClass('is-hidden').parent('.has-children').parent('ul').removeClass('moves-out');
    });

    function closeNav() {
        $('.mobile-nav-trigger').removeClass('nav-is-visible');
        $('header').removeClass('nav-is-visible');
        $('.mobile-nav').removeClass('nav-is-visible');
        $('.has-children ul').addClass('is-hidden');
        $('.has-children a').removeClass('selected');
        $('.moves-out').removeClass('moves-out');
        $('main').removeClass('nav-is-visible');
    }

    function checkWindowWidth() {
        // Check window width (scrollbar included)
        let e = window,
            a = 'inner';
        if (!('innerWidth' in window)) {
            a = 'client';
            e = document.documentElement || document.body;
        }
        if (e[a + 'Width'] >= MqL) {
            return true;
        } else {
            return false;
        }
    }

    function moveNavigation() {
        let navigation = $('.mobile-nav');
        let desktop = checkWindowWidth();
        if (desktop) {
            // navigation.detach();
            navigation.insertBefore('.mobile-menu-button');
        } else {
            // navigation.detach();
            navigation.insertAfter('main');
        }
    }
});