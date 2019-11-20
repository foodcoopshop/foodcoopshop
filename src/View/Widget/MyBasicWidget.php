<?php
namespace App\View\Widget;

use Cake\View\Form\ContextInterface;
use Cake\View\Widget\BasicWidget;

class MyBasicWidget extends BasicWidget
{

    public function render(array $data, ContextInterface $context)
    {
        $data += [
            'name' => '',
            'val' => null,
            'type' => 'text',
            'escape' => true,
            'templateVars' => []
        ];
        $data['value'] = $data['val'];
        
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
