<?php

use Cake\Core\Configure;
use Cake\View\Helper;

/**
 * MenuHelper
 *
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
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
            if (!empty($page['children'])) {
                foreach ($page['children'] as $childPage) {
                    if ($childPage['Page']['extern_url'] != '') {
                        $slug = $childPage['Page']['extern_url'];
                    } else {
                        $slug = Configure::read('AppConfig.slugHelper')->getPageDetail($childPage['Page']['id_page'], $childPage['Page']['title']);
                    }
                    $children[] = [
                        'name' => $childPage['Page']['title'],
                        'slug' => $slug
                    ];
                }
            }
            if ($page['Page']['extern_url'] != '') {
                $slug = $page['Page']['extern_url'];
            } else {
                $slug = Configure::read('AppConfig.slugHelper')->getPageDetail($page['Page']['id_page'], $page['Page']['title']);
            }
            $menu[] = [
                'name' => $page['Page']['title'],
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

        if ($slug != '/' && preg_match('/'.str_replace('/', '\/', $slug).'/', $_SERVER['REQUEST_URI'])) {
            $applyActiveClass = true;

            // START hack: sometimes two menu items are selected, because of same url
            if ((    $name == 'Mitglieder'  && preg_match('/(profile|changePassword)/', $_SERVER['REQUEST_URI']))
                 || ($name == 'Aktuelles' && preg_match('/hersteller/', $_SERVER['REQUEST_URI']))
                 || ($name == 'Aktivitäten' && preg_match('/order_detail_cancelled/', $_SERVER['REQUEST_URI']))) {
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

        $naviElement = '<a' . $classString . $style.' href="'.$slug.'">'.$fontAwesomeIconString.$name.'</a>';

        return $naviElement;
    }

    public function getAuthMenuElement($appAuth)
    {
        if ($appAuth->loggedIn()) {
            $userName = $appAuth->getAbbreviatedUserName();
            if ($appAuth->isManufacturer()) {
                $userName = $appAuth->getManufacturerName();
            }

            if ($this->plugin != '') {
                $menuElement = ['slug' => 'javascript:void(0);', 'name' => 'Abmelden<br /><span>'.$userName.'</span>', 'options' => ['fa-icon' => 'fa-fw fa-sign-out', 'class' => ['logout-button']]];
            } else {
                $menuElement = ['slug' => 'javascript:void(0);', 'name' => 'Abmelden', 'options' => ['class' => ['logout-button']]];
            }
        } else {
            if ($this->plugin == '') {
                $menuElement = ['slug' => Configure::read('AppConfig.slugHelper')->getLogin(), 'name' => 'Anmelden'];
            }
        }
        return $menuElement;
    }

    public function getPaymentProductMenuElement()
    {
        if (Configure::read('AppConfig.htmlHelper')->paymentIsCashless()) {
            return ['slug' => Configure::read('AppConfig.slugHelper')->getMyCreditBalance(), 'name' => 'Guthaben', 'options' => ['fa-icon' => 'fa-fw fa-euro']];
        }
        return [];
    }

    public function getPaymentMemberFeeMenuElement()
    {
        if (Configure::read('AppConfig.memberFeeEnabled')) {
            return ['slug' => Configure::read('AppConfig.slugHelper')->getMyMemberFeeBalance(), 'name' => 'Mitgliedsbeitrag', 'options' => ['fa-icon' => 'fa-fw fa-heart']];
        }
        return [];
    }
}
