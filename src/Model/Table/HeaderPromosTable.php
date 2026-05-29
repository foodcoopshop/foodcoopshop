<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Event\EventInterface;
use Cake\Validation\Validator;
use App\Model\Entity\HeaderPromo;

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
 * @extends \App\Model\Table\AppTable<\App\Model\Entity\HeaderPromo>
 */
class HeaderPromosTable extends AppTable
{

    use ButtonLinkValidationTrait;

    /**
     * @param EventInterface<\App\Model\Table\HeaderPromosTable> $event
     * @param ArrayObject<string, mixed> $data
     * @param ArrayObject<string, mixed> $options
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        $this->normalizeHrefFields($data, ['primary_href', 'secondary_href']);

        $plainStringFields = [
            'title',
            'lead_text',
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

        if (array_key_exists('text', $rawData)) {
            $data['text'] = trim(strip_tags(
                htmlspecialchars_decode((string)$rawData['text']),
                HeaderPromo::ALLOWED_TAGS_TEXT,
            ));
        }
    }

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->maxLength('title', 55, __('Please enter at most {0} characters for the title.', [55]));
        $validator->allowEmptyString('title');

        $validator->maxLength('lead_text', 100, __('Please enter at most {0} characters for the subtitle.', [100]));
        $validator->allowEmptyString('lead_text');

        $validator->maxLength('text', 300, __('Please enter at most {0} characters for the text.', [300]));
        $validator->allowEmptyString('text');

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

        return $validator;
    }
}
