<?php
namespace App\View\Widget;

use Cake\View\Form\ContextInterface;
use Cake\View\Widget\BasicWidget;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.7.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class MyBasicWidget extends BasicWidget
{

    public function render(array $data, ContextInterface $context): string
    {

        $data = $this->mergeDefaults($data, $context);

        $data['value'] = $data['val'];
        unset($data['val']);

        $fieldName = $data['fieldName'] ?? null;
        if ($fieldName) {
            if (
                $data['type'] === 'number'
                && !isset($data['step'])
            ) {
                $data = $this->setStep($data, $context, $fieldName);
            }

            $typesWithMaxLength = ['text', 'email', 'tel', 'url', 'search'];
            if (
                !array_key_exists('maxlength', $data)
                && in_array($data['type'], $typesWithMaxLength, true)
                ) {
                    $data = $this->setMaxLength($data, $context, $fieldName);
            }
        }

        // since HtmlPurifier converts all database updates to htmlspecialchars (eg. & => &amp;, > => &lt;)
        // they need to be reconverted for being displayed properly in form inputs
        $data['value'] = html_entity_decode($data['value']);

        return $this->_templates->format('input', [
            'name' => $data['name'],
            'type' => $data['type'],
            'templateVars' => $data['templateVars'],
            'attrs' => $this->_templates->formatAttributes(
                $data,
                ['name', 'type']
            ),
        ]);

    }

}
