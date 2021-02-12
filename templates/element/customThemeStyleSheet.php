<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

?>

<style>

    ::selection {
      background: <?php echo Configure::read('app.customThemeMainColor'); ?>; /* WebKit/Blink Browsers */
      color: #fff;
    }
    ::-moz-selection {
      background: <?php echo Configure::read('app.customThemeMainColor'); ?>; /* Gecko Browsers */
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
    #flashMessage.success,
    .modal-header,
    .cookieConsentWrapper,
    .drop a.upload-button {
        background-color: <?php echo Configure::read('app.customThemeMainColor'); ?>;
    }

    h1,
    h2,
    a.blog-post-wrapper h3,
    .product-wrapper .price,
    #scroll-to-top a,
    #scroll-to-top a i,
    .vertical.menu a i.fas,
    .vertical.menu span.additional-info,
    a:not(.btn), a:not(.btn):visited, a:not(.btn):active,
    #footer i.fab, #footer i.far, #footer i.fas,
    a.btn.edit-shortcut-button,
    a.btn.prev-button i, a.btn.next-button i,
    .product-wrapper .price-asterisk,
    i.fa.ok, i.fas.ok, i.far.ok,
    body.carts.detail .cart:not(#cart) span.amount .btn,
    #filter-loader i {
        color: <?php echo Configure::read('app.customThemeMainColor'); ?>;
    }

    .blog-wrapper .swiper-button-prev,
    .blog-wrapper .swiper-button-next,
    body.blog_posts.detail #inner-content h2.further-news,
    body.customers.registration_successful #inner-content h2.further-news,
    a.btn-arrow:hover {
        color: <?php echo Configure::read('app.customThemeMainColor'); ?> ! important;
    }

    .btn-success,
    .btn-success:active:hover,
    #flashMessage.success {
        border-color: <?php echo Configure::read('app.customThemeMainColor'); ?>;
    }

    .btn-success:hover,
    .btn-success:focus,
    .btn-success:active,
    .btn-success.disabled {
        background-color: <?php echo Configure::read('app.customThemeMainColor'); ?> ! important;
        border-color: <?php echo Configure::read('app.customThemeMainColor'); ?>;
    }
    .btn-success:focus:active,
    .bootstrap-select .dropdown-item.active,
    .bootstrap-select .dropdown-item:active,
    table.list tr.selected {
        background-color: <?php echo Configure::read('app.customThemeMainColor'); ?> ! important;
    }
    .vertical.menu a {
        color: #333333;
    }
    body.customers.login #self-service {
        box-shadow: inset 0 0 3em <?php echo Configure::read('app.customThemeMainColor'); ?>;
    }
    body.admin #content {
        background: linear-gradient(-75deg, #ffffff 0%, #ffffff 50%, <?php echo Configure::read('app.customThemeMainColor'); ?> 100%);
        background: -moz-linear-gradient(-75deg, #ffffff 0%, #ffffff 50%, <?php echo Configure::read('app.customThemeMainColor'); ?> 100%);
        background: -webkit-linear-gradient(-75deg, #ffffff 0%, #ffffff 50%, <?php echo Configure::read('app.customThemeMainColor'); ?> 100%);
        background: -ms-linear-gradient(-75deg, #ffffff 0%, #ffffff 50%, <?php echo Configure::read('app.customThemeMainColor'); ?> 100%);
        background: -o-linear-gradient(-75deg, #ffffff 0%, #ffffff 50%, <?php echo Configure::read('app.customThemeMainColor'); ?> 100%);
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#ffffff, endColorstr=<?php echo Configure::read('app.customThemeMainColor'); ?>,GradientType=1);
    }

    <?php if ($isMobile) { ?>
        @media only screen and (max-device-width: 768px) {
            #responsive-header a,
            body.self_services #responsive-header i.fa-circle-notch,
            :not(button)> i.fas
            :not(.fa-arrow-cycle-right)
            :not(.fa-arrow-cycle-left)
            :not(.fa-star)
            :not(.fa-circle-notch)
            :not(.fa-tags)
            :not(.fa-shopping-bag)
            :not(.fa-minus-circle)
            :not(.fa-plus-circle) {
                color: <?php echo Configure::read('app.customThemeMainColor'); ?> ! important;
            }
            body.self_services #responsive-header a.btn,
            body.self_services #responsive-header i.ok {
                color: #fff ! important;
            }
            body:not(.admin) .sb-slidebar,
            body:not(.admin) .sb-right h3 {
                background-color: <?php echo Configure::read('app.customThemeMainColor'); ?> ! important;
            }
            body:not(.admin) .sb-left li.header,
            body:not(.admin) .sb-left a:hover,
            body:not(.admin) .sb-left a.active,
            body:not(.admin) .sb-left a:hover i.fas:not(.gold),
            body:not(.admin) .sb-left a.active i.fas:not(.gold):not(.fa-pencil-alt) {
                background-color: #fff;
                color: <?php echo Configure::read('app.customThemeMainColor'); ?> ! important;
            }
            body.admin .sb-left li.header,
            body.admin .sb-left a:hover,
            body.admin .sb-left a.active,
            body.admin .sb-left a:hover i.fas:not(.gold),
            body.admin .sb-left a.active i.fas:not(.gold):not(.fa-pencil-alt) {
                background-color: <?php echo Configure::read('app.customThemeMainColor'); ?>;
                color: #fff ! important;
            }
            .sb-right .inner {
                border-color: <?php echo Configure::read('app.customThemeMainColor'); ?> ! important;
            }
        }
    <?php } ?>
</style>