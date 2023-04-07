<?php

/*
Plugin Name: Sticky Menu
Plugin URI:  
Description: Sticky Menu
Version:     1.0.6
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
		'type' => 'text',
	));

    $wp_customize->add_setting('gk_sticky_menu_header_selector', array(
		'default' => '',
		'transport' => 'refresh',
		'sanitize_callback' => 'sanitize_text_field',
	));

	$wp_customize->add_control('gk_sticky_menu_header_selector', array(
		'label' => __('Header selector', 'gk_theme'),
		'section' => 'gk_sticky_menu_settings',
		'type' => 'text',
	));

    // add dropdown with options 'fixed' and 'sticky' to select sticky menu type
    $wp_customize->add_setting('gk_sticky_menu_type', array(
        'default' => 'sticky',
        'transport' => 'refresh',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('gk_sticky_menu_type', array(
        'label' => __('Sticky menu type', 'gk_theme'),
        'section' => 'gk_sticky_menu_settings',
        'type' => 'select',
        'choices' => array(
            'fixed' => 'Fixed',
            'sticky' => 'Sticky',
        ),
    ));
}

function gk_sticky_menu_header_selector() {
    $selector = get_theme_mod('gk_sticky_menu_header_selector');
    if (empty($selector)) {
        $selector = 'header';
    }
    return $selector;
}

// add custom script to the header using wp_head hook
add_action( 'wp_head', 'sticky_menu_script' );

function sticky_menu_script() {
    ?>
    <script>
        // function to set class active to menu link when section targeted by menu links with hash in href is in viewport
        function setActiveMenuLink() {
            var menuLinks = document.querySelectorAll('<?php echo gk_sticky_menu_header_selector() ?> a[href*="#"]');

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
            const menuLinks = document.querySelectorAll('<?php echo gk_sticky_menu_header_selector() ?> a[href*="#"]');
            const header_element = document.querySelector('<?php echo gk_sticky_menu_header_selector() ?>');
            
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

        // function to get body element background color and convert it to rgba
        function getBodyBgColor(opacity) {
            let bodyBgColor = window.getComputedStyle(document.body).backgroundColor;
            const bodyBgColorRgba = bodyBgColor.replace(')', ', ' + opacity +')').replace('rgb', 'rgba');
            return bodyBgColorRgba;
        }

        // run when DOM is loaded
        document.addEventListener("DOMContentLoaded", function(event) {
            const header = document.querySelector('<?php echo gk_sticky_menu_header_selector() ?>');
            const header_original_offsetTop = header.offsetTop;
            const header_original_background = window.getComputedStyle(header).backgroundColor;
            const header_original_position = window.getComputedStyle(header).position;
            const sticky_type = '<?php echo get_theme_mod('gk_sticky_menu_type'); ?>';

            console.log('sticky type: ' + sticky_type);

            if (sticky_type == 'sticky') {
                const wpadminbar = document.getElementById('wpadminbar');
                const wpadminbar_position =  wpadminbar ? window.getComputedStyle(wpadminbar).position : 'static';

                if (wpadminbar_position == 'fixed') {
                    header.style.top = wpadminbar.offsetHeight + 'px';
                } else {
                    header.style.top = 0;
                }
            }

            window.onscroll = function(e) {
                let make_header_sticky = false;

                if (sticky_type == 'sticky') {
                    make_header_sticky = window.pageYOffset > header_original_offsetTop;
                }
                if (sticky_type == 'fixed') {
                    make_header_sticky = window.pageYOffset > header.offsetTop;
                }

                if (make_header_sticky) {
                    header.classList.add("sticky-header");
                    header.style.position = sticky_type;
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