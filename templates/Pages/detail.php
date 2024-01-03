<?php
declare(strict_types=1);

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

use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
]);

if ($page->full_width) {
    echo $this->Html->css('page-full-width');
}

echo '<h1>'.$page->title.'</h1>';

if (!empty($page['children'])) {
    foreach ($page['children'] as $childPage) {
        echo '<p>'.$this->Html->link(
            $childPage->title,
            $this->Slug->getPageDetail($childPage->id_page, $childPage->title),
            [
                'class' => 'btn btn-outline-light',
                'escape' => false
            ]
        ).'</p>';
    }
}

if ($identity !== null) {
    if ($identity->isSuperadmin() || $identity->isAdmin()) {
        echo $this->Html->link(
            '<i class="fas fa-pencil-alt"></i>',
            $this->Slug->getPageEdit($page->id_page),
            [
                'class' => 'btn btn-outline-light edit-shortcut-button',
                'title' => __('Edit'),
                'escape' => false
            ]
        );
    }
}

echo $page->content;

echo '<p><br /><i>'.__('Modified_on'). ' ' . $page->modified->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateNTimeShort')).'</i></p>';
