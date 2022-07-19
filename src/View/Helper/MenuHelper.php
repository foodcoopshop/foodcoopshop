<?php

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper;
use Cake\Datasource\FactoryLocator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
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

            $tmpMenuItem .= $this->renderMenuElement(
                $item['slug'],
                $item['name'],
                $item['options']['style'] ?? '',
                $item['options']['class'] ?? [],
                $item['options']['fa-icon'] ?? ''
            );

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

        if ($slug == $_SERVER['REQUEST_URI']) {
            $class[] = 'active';
        }

        if ($fontAwesomeIconClass != '') {
            $class[] = 'has-icon';
        }
        $fontAwesomeIconString = '<i class="fas ' . ($fontAwesomeIconClass ?? '') . '"></i>';

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
                $menuElement = ['slug' => 'javascript:void(0);', 'name' => __('Sign_out') . '<br /><span>'.$userName.'</span>', 'options' => ['fa-icon' => 'fa-fw ok fa-sign-out-alt', 'class' => ['logout-button']]];
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
            return [
                'slug' => Configure::read('app.slugHelper')->getMyCreditBalance(),
                'name' => __('Credit'),
                'options' => [
                    'fa-icon' => Configure::read('app.htmlHelper')->getFontAwesomeIconForCurrencyName(
                        Configure::read('app.currencyName'),
                    ),
                ],
            ];
        }
        return [];
    }


    public function getMyFeedbackMenuElement($appAuth)
    {
        if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED') && $appAuth->user()) {
            $feedbackTable = FactoryLocator::get('Table')->get('Feedbacks');
            $feedback = $feedbackTable->find('all', [
                'conditions' => [
                    'Feedbacks.customer_id' => $appAuth->getUserId(),
                ],
            ])->first();
            return [
                'slug' => Configure::read('app.slugHelper')->getMyFeedbackForm(),
                'name' => __('Feedback'),
                'options' => [
                    'fa-icon' => 'fa-heart' . (empty($feedback) ? ' not-ok' : ''),
                ],
            ];
        }
        return [];
    }

}
