<?php
declare(strict_types=1);

namespace Admin\Controller;

use Admin\Traits\UploadTrait;

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
class SlidersController extends AdminAppController
{

    use UploadTrait;

    public function index(): void
    {
        $conditions = [
            'Sliders.active > ' . APP_DEL
        ];

        $slidersTable = $this->getTableLocator()->get('Sliders');
        $query = $slidersTable->find('all', conditions: $conditions);
        $sliders = $this->paginate($query, [
            'sortableFields' => [
                'Sliders.position', 'Sliders.active', 'Sliders.link', 'Sliders.is_private'
            ],
            'order' => [
                'Sliders.position' => 'ASC'
            ]
        ]);

        $this->set('sliders', $sliders);
        $this->set('title_for_layout', __('Slideshow'));
    }
}
