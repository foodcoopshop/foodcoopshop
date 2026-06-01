<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Validation\Validator;

trait ButtonLinkValidationTrait
{

    protected function addPairValidationRule(Validator $validator, string $ruleName, string $field, string $pairedField): void
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

    protected function isEitherBothFilledOrBothEmpty(string $firstValue, string $secondValue): bool
    {
        return ($firstValue === '' && $secondValue === '') || ($firstValue !== '' && $secondValue !== '');
    }
}