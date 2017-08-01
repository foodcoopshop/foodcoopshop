<?php

App::uses('Helper', 'View');

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

        $menu = array();
        foreach ($pages as $page) {
            $children = array();
            if (!empty($page['children'])) {
                foreach ($page['children'] as $childPage) {
                    if ($childPage['Page']['url'] != '') {
                        $slug = $childPage['Page']['url'];
                    } else {
                        $slug = Configure::read('slugHelper')->getPageDetail($childPage['Page']['id_cms'], $childPage['PageLang']['meta_title']);
                    }
                    $children[] = array(
                        'name' => $childPage['PageLang']['meta_title'],
                        'slug' => $slug
                    );
                }
            }
            if ($page['Page']['url'] != '') {
                $slug = $page['Page']['url'];
            } else {
                $slug = Configure::read('slugHelper')->getPageDetail($page['Page']['id_cms'], $page['PageLang']['meta_title']);
            }
            $menu[] = array(
                'name' => $page['PageLang']['meta_title'],
                'slug' => $slug,
                'children' => $children
            );
        }
        return $menu;
    }

    private function buildMenuItem($item, $index)
    {

        $liClass = array();
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

    private function renderMenuElement($slug, $name, $style = '', $class = array(), $fontAwesomeIconClass = '')
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

            if (in_array($this->plugin, array('Network', 'Admin'))) {
                $menuElement = array('slug' => 'javascript:void(0);', 'name' => 'Abmelden<br /><span>'.$userName.'</span>', 'options' => array('fa-icon' => 'fa-fw fa-sign-out', 'class' => array('logout-button')));
            } else {
                $menuElement = array('slug' => 'javascript:void(0);', 'name' => 'Abmelden', 'options' => array('class' => array('logout-button')));
            }
        } else {
            if (in_array($this->plugin, array('Network', 'Admin'))) {
                $menuElement = array('slug' => Configure::read('slugHelper')->getLogin(), 'name' => 'Anmelden');
            }
        }
        return $menuElement;
    }

    public function getPaymentProductMenuElement()
    {
        if (Configure::read('htmlHelper')->paymentIsCashless()) {
            return array('slug' => Configure::read('slugHelper')->getMyCreditBalance(), 'name' => 'Guthaben', 'options' => array('fa-icon' => 'fa-fw fa-euro'));
        }
        return array();
    }

    public function getPaymentMemberFeeMenuElement()
    {
        if (Configure::read('app.memberFeeEnabled')) {
            return array('slug' => Configure::read('slugHelper')->getMyMemberFeeBalance(), 'name' => 'Mitgliedsbeitrag', 'options' => array('fa-icon' => 'fa-fw fa-heart'));
        }
        return array();
    }
}
