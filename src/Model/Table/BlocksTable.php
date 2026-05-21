<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Block;
use Cake\ORM\Query\SelectQuery;
use Cake\Validation\Validator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) FoodCoopShop, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 *
 * @extends \App\Model\Table\AppTable<\App\Model\Entity\Block>
 */
class BlocksTable extends AppTable
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey('id');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('position', __('Please_enter_a_number_between_{0}_and_{1}.', [0, 9999]));
        $validator->integer('position', __('Please_enter_a_number_between_{0}_and_{1}.', [0, 9999]));
        $validator->greaterThanOrEqual('position', 0, __('Please_enter_a_number_between_{0}_and_{1}.', [0, 9999]));

        $validator->boolean('active');
        $validator->allowEmptyString('image');
        $validator->notEmptyString('image_position', __('Please_choose_one_of_the_available_options.'));
        $validator->inList('image_position', [Block::IMAGE_POSITION_LEFT, Block::IMAGE_POSITION_RIGHT], __('Please_choose_one_of_the_available_options.'));
        $validator->allowEmptyString('heading');
        $validator->allowEmptyString('content');

        $validator->add('position', 'atLeastOneContentField', [
            'rule' => function (mixed $value, array $context): bool {
                $image = trim((string)($context['data']['image'] ?? ''));
                $tmpImage = trim((string)($context['data']['tmp_image'] ?? ''));
                $heading = trim(strip_tags((string)($context['data']['heading'] ?? '')));
                $content = trim(strip_tags((string)($context['data']['content'] ?? '')));

                return $image !== '' || $tmpImage !== '' || $heading !== '' || $content !== '';
            },
            'message' => __('Please fill in at least one of image heading or text.'),
        ]);

        return $validator;
    }

    /**
     * @return SelectQuery<\App\Model\Entity\Block>
     */
    public function getForHome(): SelectQuery
    {
        return $this->find('all',
            conditions: [
                'Blocks.active' => APP_ON,
            ],
            order: [
                'Blocks.position' => 'ASC',
                'Blocks.id' => 'ASC',
            ],
        );
    }
}
