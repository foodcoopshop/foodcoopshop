<?php
declare(strict_types=1);

echo '<div class="header-promo-fields">';

    echo $this->Form->control('header_promo.title', [
        'label' => __('Title'),
    ]);
    echo $this->Form->control('header_promo.lead_text', [
        'label' => __('Subtitle'),
    ]);

    echo $this->Form->control('header_promo.text', [
        'label' => __('Text'),
        'type' => 'textarea',
        'id' => 'header-promo-text',
    ]);

    echo $this->element('addScript', [
        'script' => "foodcoopshop.Editor.initSmallWithLink('header-promo-text');",
    ]);
    echo $this->Form->control('header_promo.primary_label', [
        'label' => __('Button label {0}', ['#1']),
    ]);
    echo $this->Form->control('header_promo.primary_href', [
        'label' => __('Link'),
    ]);
    echo $this->Form->control('header_promo.secondary_label', [
        'label' => __('Button label {0}', ['#2']),
    ]);
    echo $this->Form->control('header_promo.secondary_href', [
        'label' => __('Link'),
    ]);

echo '</div>';
