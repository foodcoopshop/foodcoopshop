<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Block;
use ArrayObject;
use Cake\Event\EventInterface;
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

    use ButtonLinkValidationTrait;

    /**
     * @param EventInterface<\App\Model\Table\BlocksTable> $event
     * @param ArrayObject<string, mixed> $data
     * @param ArrayObject<string, mixed> $options
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        $this->normalizeHrefFields($data, ['primary_href', 'secondary_href']);

        $plainStringFields = [
            'heading',
            'image',
            'tmp_image',
            'primary_label',
            'primary_href',
            'secondary_label',
            'secondary_href',
        ];

        $rawData = $data->getArrayCopy();
        foreach ($plainStringFields as $field) {
            if (array_key_exists($field, $rawData)) {
                $data[$field] = $this->sanitizePlainStringValue($rawData[$field]);
            }
        }

        if (array_key_exists('content', $rawData)) {
            $data['content'] = trim(strip_tags(
                htmlspecialchars_decode((string)$rawData['content']),
                Block::ALLOWED_TAGS,
            ));
        }
    }

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

        $validator->maxLength('primary_label', 25, __('Please enter at most {0} characters for the primary button label.', [25]));
        $validator->allowEmptyString('primary_label');

        $validator->maxLength('secondary_label', 25, __('Please enter at most {0} characters for the secondary button label.', [25]));
        $validator->allowEmptyString('secondary_label');

        $validator->maxLength('primary_href', 255, __('Please enter at most {0} characters for the primary link.', [255]));
        $validator->allowEmptyString('primary_href');
        $this->addHrefValidationRule($validator, 'primary_href', 'validPrimaryHref');

        $validator->maxLength('secondary_href', 255, __('Please enter at most {0} characters for the secondary link.', [255]));
        $validator->allowEmptyString('secondary_href');
        $this->addHrefValidationRule($validator, 'secondary_href', 'validSecondaryHref');

        $this->addPairValidationRule($validator, 'primaryPair', 'primary_label', 'primary_href');
        $this->addPairValidationRule($validator, 'primaryPair', 'primary_href', 'primary_label');
        $this->addPairValidationRule($validator, 'secondaryPair', 'secondary_label', 'secondary_href');
        $this->addPairValidationRule($validator, 'secondaryPair', 'secondary_href', 'secondary_label');

        $validator->add('position', 'atLeastOneContentField', [
            'rule' => function (mixed $value, array $context): bool {
                $image = trim((string)($context['data']['image'] ?? ''));
                $tmpImage = trim((string)($context['data']['tmp_image'] ?? ''));
                $heading = trim(strip_tags((string)($context['data']['heading'] ?? '')));
                $content = trim(strip_tags((string)($context['data']['content'] ?? '')));
                $primaryLabel = trim((string)($context['data']['primary_label'] ?? ''));
                $primaryHref = trim((string)($context['data']['primary_href'] ?? ''));
                $secondaryLabel = trim((string)($context['data']['secondary_label'] ?? ''));
                $secondaryHref = trim((string)($context['data']['secondary_href'] ?? ''));

                return $image !== ''
                    || $tmpImage !== ''
                    || $heading !== ''
                    || $content !== ''
                    || $primaryLabel !== ''
                    || $primaryHref !== ''
                    || $secondaryLabel !== ''
                    || $secondaryHref !== '';
            },
            'message' => __('Please fill in at least one of image heading text or button.'),
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
