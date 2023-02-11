<?php

/*
Plugin Name: Sticky Menu
Plugin URI:  
Description: Sticky Menu
Version:     1.0.3
Author:      Grzegorz Kowalski
Author URI:  https://grzegorzkowalski.pl
*/

add_action( 'customize_register', 'gk_sticky_menu_customize_register' );

function gk_sticky_menu_customize_register( $wp_customize ) {
	$wp_customize->add_panel('gk_sticky_menu', array(
		'title' => __('Sticky menu plugin', 'gk_theme'),
		'description' => __('Ustawienia motywu', 'gk_theme'),
		'priority' => 160,
	));

	$wp_customize->add_section('gk_sticky_menu_settings', array(
		'title' => __('Settings', 'gk_theme'),
		'description' => __('Ustawienia formularzy', 'gk_theme'),
		'panel' => 'gk_sticky_menu',
	));

	$wp_customize->add_setting('gk_sticky_menu_header_height', array(
		'default' => '',
		'transport' => 'refresh',
		'sanitize_callback' => 'sanitize_text_field',
	));

	$wp_customize->add_control('gk_sticky_menu_header_height', array(
		'label' => __('Header height', 'gk_theme'),
		'section' => 'gk_sticky_menu_settings',
		'type' => 'email',
	));
}

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
            
            let headerHeight = '<?php echo get_theme_mod('gk_sticky_menu_header_height'); ?>';
            if (headerHeight == '') {
                headerHeight = header_element.offsetHeight;
            } else {
                headerHeight = parseInt(headerHeight);
            }

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