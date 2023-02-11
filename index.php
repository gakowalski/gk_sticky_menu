<?php

/*
Plugin Name: Sticky Menu
Plugin URI:  
Description: Sticky Menu
Version:     1.0.2
Author:      Grzegorz Kowalski
Author URI:  https://grzegorzkowalski.pl
*/

// add custom script to the header using wp_head hook
add_action( 'wp_head', 'sticky_menu_script' );

function sticky_menu_script() {
    ?>
    <script>
        // function to set class active to menu link when section targeted by menu links with hash in href is in viewport
        function setActiveMenuLink() {
            var menuLinks = document.querySelectorAll('header a[href*="#"]');

            menuLinks.forEach(function(link) {
                var target = document.getElementById(link.getAttribute('href').split('#')[1]);
                if (!target) return; // if target element doesn't exist, skip to next iteration
                var targetRect = target.getBoundingClientRect();
                var targetTop = targetRect.top;
                var targetBottom = targetRect.bottom;
                const innerHeight = window.innerHeight || document.documentElement.clientHeight;

                if (targetTop >= 0 && targetTop < innerHeight) {
                    menuLinks.forEach(function(link) {
                        link.classList.remove('active');
                    });
                    link.classList.add('active');
                    link.focus();
                    //console.log('focus on ' + link.getAttribute('href').split('#')[1]);
                }
            });
        }

        // function to set scroll-margin-top to all elements targeted by menu links with hash in href
        function setScrollMarginTop() {
            const menuLinks = document.querySelectorAll('header a[href*="#"]');
            const header_element = document.querySelector('header');
            //const headerHeight = header_element.offsetHeight;
            const headerHeight = 120;
            console.log('header height: ' + headerHeight);
            const wpadminbar_height = document.getElementById('wpadminbar') ? document.getElementById('wpadminbar').offsetHeight : 0;
            const scrollMarginTop = headerHeight - wpadminbar_height;

            menuLinks.forEach(function(link) {
                let target = document.getElementById(link.getAttribute('href').split('#')[1]);
                if (target) {
                    target.style.scrollMarginTop = scrollMarginTop + 'px';
                    console.log('scroll-margin-top set to ' + scrollMarginTop + 'px for ' + link.getAttribute('href').split('#')[1]);
                }
            });
        }

        // run when DOM is loaded
        document.addEventListener("DOMContentLoaded", function(event) {
            var header = document.querySelector('header');
            var header_original_background = window.getComputedStyle(header).backgroundColor;
            var header_original_position = window.getComputedStyle(header).position;
            var sticky = header.offsetTop;

            // function to get body element background color and convert it to rgba
            function getBodyBgColor(opacity) {
                var bodyBgColor = window.getComputedStyle(document.body).backgroundColor;
                var bodyBgColorRgba = bodyBgColor.replace(')', ', ' + opacity +')').replace('rgb', 'rgba');
                return bodyBgColorRgba;
            }

            window.onscroll = function(e) {
                if (window.pageYOffset > sticky) {
                    header.classList.add("sticky-header");
                    header.style.position = "fixed";
                    header.style.backgroundColor = getBodyBgColor(1);
                } else {
                    header.classList.remove("sticky-header");
                    header.style.position = header_original_position;
                    header.style.backgroundColor = header_original_background;
                }
                setActiveMenuLink();
            };

            setScrollMarginTop();
        });
    </script>
    <?php
}