<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.3.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

?>

<style>

    ::selection {
      background: <?php echo Configure::read('app.customFrontendColorTheme'); ?>; /* WebKit/Blink Browsers */
      color: #fff;
    }
    ::-moz-selection {
      background: <?php echo Configure::read('app.customFrontendColorTheme'); ?>; /* Gecko Browsers */
      color: #fff;
    }

    .box h3,
    .btn-success,
    #main-menu li a:hover, #main-menu li a.active,
    #main-menu li ul li a,
    .vertical.menu a:hover, .vertical.menu a:hover i, .vertical.menu a.active, .vertical.menu a.active i,
    .vertical.menu li.heading,
    .menu.vertical a:hover span.additional-info,
    .menu.vertical a.active span.additional-info,
    #categories-menu li.header,
    #manufacturers-menu li.header,
    h2.info,
    #flashMessage.success {
        background-color: <?php echo Configure::read('app.customFrontendColorTheme'); ?>;
    }
    
    h1,
    h2,
    a.blog-post-wrapper h3,
    .product-wrapper .price,
    #scroll-to-top a,
    #scroll-to-top a i,
    body.pages.home .cycle-pager span.cycle-pager-active,
    .vertical.menu a i.fas,
    .vertical.menu span.additional-info,
    a:not(.btn), a:not(.btn):visited, a:not(.btn):active,
    #footer i.fab, #footer i.far, #footer i.fas,
    a.btn.edit-shortcut-button {
        color: <?php echo Configure::read('app.customFrontendColorTheme'); ?>;
    }
    
    .blog-wrapper .owl-nav button {
        color: <?php echo Configure::read('app.customFrontendColorTheme'); ?> ! important;
    }
    
    .btn-success,
    .btn-success:active:hover,
    #flashMessage.success {
        border-color: <?php echo Configure::read('app.customFrontendColorTheme'); ?>;
    }
    
    .btn-success:hover,
    .btn-success:focus,
    .btn-success:active,
    .btn-success.disabled {
        background-color: <?php echo Configure::read('app.customFrontendColorTheme'); ?> ! important;
        border-color: <?php echo Configure::read('app.customFrontendColorTheme'); ?>;
    }
    .btn-success:focus:active {
        background-color: <?php echo Configure::read('app.customFrontendColorTheme'); ?> ! important;
    }
    .vertical.menu a {
    	color: #333333;
    } 
    
    <?php if ($isMobile) { ?>
        @media only screen and (max-device-width: 768px) {
            #responsive-header a,
            :not(button)> i.fas:not(.fa-star):not(.fa-circle-notch):not(.fa-tags) {
                color: <?php echo Configure::read('app.customFrontendColorTheme'); ?> ! important;
            }
            .sb-slidebar i.fas:not(.gold),
            .sb-slidebar i.fas.fa-tags,
            a.btn i.fas:not(.fa-plus-circle):not(.fa-minus-circle):not(.fa-times-circle):not(.gold):not(.fa-pencil-alt):not(.fa-circle-notch) {
                color: #fff ! important;
            }
            .sb-slidebar,
            .sb-right h3 {
                background-color: <?php echo Configure::read('app.customFrontendColorTheme'); ?> ! important;
            }
            .sb-left li.header, .sb-left a:hover, .sb-left a.active,
            .sb-left a:hover i.fas:not(.gold), .sb-left a.active i.fas:not(.gold):not(.fa-pencil-alt) {
                background-color: #fff;
                color: <?php echo Configure::read('app.customFrontendColorTheme'); ?> ! important;
            }
            .sb-right .inner {
                border-color: <?php echo Configure::read('app.customFrontendColorTheme'); ?> ! important;
            }
        }
    <?php } ?>
</style>