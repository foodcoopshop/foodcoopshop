<?php

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class MenuHelper extends Helper
{

    public function render($array, $options)
    {
        $tmpMenu = '<ul id="'.$options['id'].'" class="'.$options['class'].'">';
        if (!empty($options['header'])) {
            $tmpMenu .= '<li class="header">'.$options['header'].'</li>';
        }
        foreach ($array as $index => $item) {
            $tmpMenu .= $this->buildMenuItem($item, $index);
        }
        if (!empty($options['footer'])) {
            $tmpMenu .= '<li class="footer">'.$options['footer'].'</li>';
        }
        $tmpMenu .= '</ul>';
        return $tmpMenu;
    }

    public function buildPageMenu($pages)
    {

        $menu = [];
        foreach ($pages as $page) {
            $children = [];
            if (!empty($page->children)) {
                foreach ($page->children as $childPage) {
                    if ($childPage->extern_url != '') {
                        $slug = $childPage->extern_url;
                    } else {
                        $slug = Configure::read('app.slugHelper')->getPageDetail($childPage->id_page, $childPage->title);
                    }
                    $children[] = [
                        'name' => $childPage->title,
                        'slug' => $slug
                    ];
                }
            }
            if ($page->extern_url != '') {
                $slug = $page->extern_url;
            } else {
                $slug = Configure::read('app.slugHelper')->getPageDetail($page->id_page, $page->title);
            }
            $menu[] = [
                'name' => $page->title,
                'slug' => $slug,
                'children' => $children
            ];
        }
        return $menu;
    }

    private function buildMenuItem($item, $index)
    {

        $liClass = [];
        if (!empty($item['children'])) {
            $liClass[] = 'has-children';
            $liClass[] = 'has-icon';
        }
        $tmpMenuItem = '<li' . (!empty($liClass) ? ' class="' . join(' ', $liClass).'"' : '').'>';

            $tmpMenuItem .= $this->renderMenuElement($item['slug'], $item['name'], @$item['options']['style'], @$item['options']['class'], @$item['options']['fa-icon']);

        if (!empty($item['children'])) {
            $tmpMenuItem .= '<ul>';
            foreach ($item['children'] as $index => $child) {
                $tmpMenuItem .= $this->buildMenuItem($child, $index);
            }
            $tmpMenuItem .= '</ul>';
        }

        $tmpMenuItem .= '</li>';

        return $tmpMenuItem;
    }

    private function renderMenuElement($slug, $name, $style = '', $class = [], $fontAwesomeIconClass = '')
    {

        if ($style != '') {
            $style = ' style="'.$style.'"';
        }
        if ($slug != '/' && preg_match('`' . preg_quote($slug) . '`', $_SERVER['REQUEST_URI'])) {
            $applyActiveClass = true;

            // START hack: sometimes two menu items are selected, because of same url
            if ((   $name == __('Members')  && preg_match('/(profile|changePassword)/', $_SERVER['REQUEST_URI']))
                || ($name == __('News') && preg_match('/'.__('route_manufacturer_list').'/', $_SERVER['REQUEST_URI']))
                || ($name == __('Activities') && preg_match('/order_detail_cancelled|payment_deposit_customer_added/', $_SERVER['REQUEST_URI']))) {
                     $applyActiveClass = false;
            }

            if ($applyActiveClass) {
                $class[] = 'active';
            }
        }

        if ($slug == '/' && $_SERVER['REQUEST_URI'] == '/') {
            $class[] = 'active';
        }

        if ($fontAwesomeIconClass != '') {
            $class[] = 'has-icon';
        }
        $fontAwesomeIconString = '<i class="fa '.@$fontAwesomeIconClass.'"></i>';

        $classString = '';
        if (!empty($class)) {
            $classString = ' class="' . join(' ', $class). '" ';
        }

        $naviElement = '<a' . $classString . $style.' href="'.$slug.'" title="'.h(strip_tags($name)).'">'.$fontAwesomeIconString.$name.'</a>';

        return $naviElement;
    }

    public function getAuthMenuElement($appAuth)
    {
        if ($appAuth->user()) {
            $userName = $appAuth->getAbbreviatedUserName();
            if ($appAuth->isManufacturer()) {
                $userName = $appAuth->getManufacturerName();
            }
            if ($this->getView()->getPlugin() != '') {
                $menuElement = ['slug' => 'javascript:void(0);', 'name' => __('Sign_out') . '<br /><span>'.$userName.'</span>', 'options' => ['fa-icon' => 'fa-fw fa-sign-out', 'class' => ['logout-button']]];
            } else {
                $menuElement = ['slug' => 'javascript:void(0);', 'name' => __('Sign_out'), 'options' => ['class' => ['logout-button']]];
            }
        } else {
            if ($this->getView()->getPlugin() == '') {
                $menuElement = ['slug' => Configure::read('app.slugHelper')->getLogin(), 'name' => __('Sign_in')];
            }
        }
        return $menuElement;
    }

    public function getPaymentProductMenuElement()
    {
        if (Configure::read('app.htmlHelper')->paymentIsCashless()) {
            return ['slug' => Configure::read('app.slugHelper')->getMyCreditBalance(), 'name' => __('Credit'), 'options' => ['fa-icon' => 'fa-fw fa-' . strtolower(Configure::read('app.currencyName'))]];
        }
        return [];
    }

    public function getPaymentMemberFeeMenuElement()
    {
        if (Configure::read('app.memberFeeEnabled')) {
            return ['slug' => Configure::read('app.slugHelper')->getMyMemberFeeBalance(), 'name' => __('Member_fee'), 'options' => ['fa-icon' => 'fa-fw fa-heart']];
        }
        return [];
    }

    public function getTimebasedCurrencyPaymentForCustomersMenuElement($appAuth)
    {
        if ($appAuth->isTimebasedCurrencyEnabledForCustomer()) {
            return ['slug' => Configure::read('app.slugHelper')->getMyTimebasedCurrencyBalanceForCustomers(), 'name' => Configure::read('app.timebasedCurrencyHelper')->getName(), 'options' => ['fa-icon' => 'fa-fw fa-clock-o']];
        }
        return [];
    }

    public function getTimebasedCurrencyPaymentForManufacturersMenuElement($appAuth)
    {
        if ($appAuth->isTimebasedCurrencyEnabledForManufacturer()) {
            return ['slug' => Configure::read('app.slugHelper')->getMyTimebasedCurrencyBalanceForManufacturers(), 'name' => Configure::read('app.timebasedCurrencyHelper')->getName(), 'options' => ['fa-icon' => 'fa-fw fa-clock-o']];
        }
        return [];
    }

}
