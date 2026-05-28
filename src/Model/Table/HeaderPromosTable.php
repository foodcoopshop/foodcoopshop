<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Validation\Validation;
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

    /**
     * @param EventInterface<\App\Model\Table\HeaderPromosTable> $event
     * @param ArrayObject<string, mixed> $data
     * @param ArrayObject<string, mixed> $options
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        foreach (['primary_href', 'secondary_href'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->stripAppFullBaseUrlFromHref((string)$data[$field]);
            }
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
        $validator->add('primary_href', 'validPrimaryHref', [
            'rule' => function (mixed $value): bool {
                return $this->isValidHref((string)$value);
            },
            'message' => __('Please enter a valid link.'),
        ]);

        $validator->maxLength('secondary_href', 255, __('Please enter at most {0} characters for the secondary link.', [255]));
        $validator->allowEmptyString('secondary_href');
        $validator->add('secondary_href', 'validSecondaryHref', [
            'rule' => function (mixed $value): bool {
                return $this->isValidHref((string)$value);
            },
            'message' => __('Please enter a valid link.'),
        ]);

        $this->addPairValidationRule($validator, 'primaryPair', 'primary_label', 'primary_href');
        $this->addPairValidationRule($validator, 'primaryPair', 'primary_href', 'primary_label');
        $this->addPairValidationRule($validator, 'secondaryPair', 'secondary_label', 'secondary_href');
        $this->addPairValidationRule($validator, 'secondaryPair', 'secondary_href', 'secondary_label');

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

        if (Validation::url($href, true)) {
            return true;
        }

        return false;
    }

    private function stripAppFullBaseUrlFromHref(string $href): string
    {
        $href = trim($href);
        if ($href === '') {
            return '';
        }

        $fullBaseUrl = rtrim(trim((string)Configure::read('App.fullBaseUrl')), '/');
        if ($fullBaseUrl === '' || !str_starts_with($href, $fullBaseUrl)) {
            return $href;
        }

        $strippedHref = substr($href, strlen($fullBaseUrl));
        if ($strippedHref === '') {
            return '/';
        }

        if (!in_array($strippedHref[0], ['/', '?', '#'], true)) {
            return $href;
        }

        if ($strippedHref[0] === '/') {
            return $strippedHref;
        }

        return '/' . $strippedHref;
    }

    private function addPairValidationRule(Validator $validator, string $ruleName, string $field, string $pairedField): void
    {
        $validator->add($field, $ruleName, [
            'rule' => function (mixed $value, array $context) use ($pairedField): bool {
                $firstValue = trim((string)$value);
                $secondValue = trim((string)($context['data'][$pairedField] ?? ''));
                return $this->isEitherBothFilledOrBothEmpty($firstValue, $secondValue);
            },
            'message' => __('Button label and link must both be filled or both be empty.'),
        ]);
    }

    private function isEitherBothFilledOrBothEmpty(string $firstValue, string $secondValue): bool
    {
        return ($firstValue === '' && $secondValue === '') || ($firstValue !== '' && $secondValue !== '');
    }
}
