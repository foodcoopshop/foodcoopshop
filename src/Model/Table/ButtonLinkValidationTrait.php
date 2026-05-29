<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Validation\Validation;
use Cake\Validation\Validator;

trait ButtonLinkValidationTrait
{

    /**
     * @param ArrayObject<string, mixed> $data
     * @param array<int, string> $fields
     */
    protected function normalizeHrefFields(ArrayObject $data, array $fields): void
    {
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->stripAppFullBaseUrlFromHref((string)$data[$field]);
            }
        }
    }

    protected function addHrefValidationRule(Validator $validator, string $field, string $ruleName): void
    {
        $validator->add($field, $ruleName, [
            'rule' => function (mixed $value): bool {
                return $this->isValidHref((string)$value);
            },
            'message' => __('Please enter a valid link.'),
        ]);
    }

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

    protected function isValidHref(string $href): bool
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

    protected function stripAppFullBaseUrlFromHref(string $href): string
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

    protected function isEitherBothFilledOrBothEmpty(string $firstValue, string $secondValue): bool
    {
        return ($firstValue === '' && $secondValue === '') || ($firstValue !== '' && $secondValue !== '');
    }
}