<?php

/*
Plugin Name: Sticky Menu
Plugin URI:  
Description: Sticky Menu
Version:     1.0.0
Author:      Grzegorz Kowalski
Author URI:  https://grzegorzkowalski.pl
*/

// add custom script to the header using wp_head hook
add_action( 'wp_head', 'sticky_menu_script' );

function sticky_menu_script() {
    ?>
    <script>
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
            };
        });
    </script>
    <?php
}