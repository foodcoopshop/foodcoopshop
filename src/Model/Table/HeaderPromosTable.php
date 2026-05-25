<?php
declare(strict_types=1);

namespace App\Model\Table;

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
 * @extends \App\Model\Table\AppTable<\App\Model\Entity\HeaderPromo>
 */
class HeaderPromosTable extends AppTable
{

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
        $validator->add('primary_href', 'validPrimaryHref', [
            'rule' => function (mixed $value): bool {
                return $this->isValidHref((string)$value);
            },
            'message' => __('Please enter a valid primary link.'),
        ]);

        $validator->maxLength('secondary_href', 255, __('Please enter at most {0} characters for the secondary link.', [255]));
        $validator->allowEmptyString('secondary_href');
        $validator->add('secondary_href', 'validSecondaryHref', [
            'rule' => function (mixed $value): bool {
                return $this->isValidHref((string)$value);
            },
            'message' => __('Please enter a valid secondary link.'),
        ]);

        $validator->add('primary_label', 'primaryPair', [
            'rule' => function (mixed $value, array $context): bool {
                $label = trim((string)$value);
                $href = trim((string)($context['data']['primary_href'] ?? ''));
                return ($label === '' && $href === '') || ($label !== '' && $href !== '');
            },
            'message' => __('Primary button label and link must both be filled or both be empty.'),
        ]);

        $validator->add('secondary_label', 'secondaryPair', [
            'rule' => function (mixed $value, array $context): bool {
                $label = trim((string)$value);
                $href = trim((string)($context['data']['secondary_href'] ?? ''));
                return ($label === '' && $href === '') || ($label !== '' && $href !== '');
            },
            'message' => __('Secondary button label and link must both be filled or both be empty.'),
        ]);

        return $validator;
    }

    private function isValidHref(string $href): bool
    {
        $href = trim($href);
        if ($href === '') {
            return true;
        }

        if (preg_match('/^\//', $href) === 1) {
            return true;
        }

        if (filter_var($href, FILTER_VALIDATE_URL) !== false) {
            return true;
        }

        return false;
    }
}
