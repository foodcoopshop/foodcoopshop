<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Validation\Validator;
use Cake\Routing\Router;
use Cake\ORM\Query\SelectQuery;

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
class SlidersTable extends AppTable
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey('id_slider');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('image', __('Please_upload_an_image.'));
        $validator->notEmptyString('position', __('Please_enter_a_number_between_{0}_and_{1}.', [0,100]));
        $validator->range('position', [-1, 101], __('Please_enter_a_number_between_{0}_and_{1}.', [0,100]));
        $validator->allowEmptyString('link');
        $validator->urlWithProtocol('link', __('Please_enter_a_valid_internet_address.'));
        return $validator;
    }

    public function getForHome(): SelectQuery
    {

        $conditions = [
            'Sliders.active' => APP_ON
        ];

        $identity = Router::getRequest()->getAttribute('identity');
        if ($identity === null) {
            $conditions['Sliders.is_private'] = APP_OFF;
        }

        $slides = $this->find('all',
        conditions: $conditions,
        order: [
            'Sliders.position' => 'ASC'
        ]);

        return $slides;

    }
}
