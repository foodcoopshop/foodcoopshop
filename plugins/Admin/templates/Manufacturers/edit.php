<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Helper.initCkeditorBig('manufacturers-description');" .
        Configure::read('app.jsNamespace') . ".Helper.initCkeditor('manufacturers-short-description');" .
        Configure::read('app.jsNamespace') . ".Upload.initImageUpload('body.manufacturers .add-image-button', foodcoopshop.Upload.saveManufacturerTmpImageInForm);" .
        Configure::read('app.jsNamespace') . ".Upload.initFileUpload('body.manufacturers .add-general-terms-and-conditions-button', foodcoopshop.Upload.saveManufacturerTmpGeneralTermsAndConditionsInForm);" .
        Configure::read('app.jsNamespace') . ".Admin.initForm();"
]);

$idForUpload = !empty($manufacturer->id_manufacturer) ? $manufacturer->id_manufacturer : StringComponent::createRandomString(6);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fas fa-check"></i> <?php echo __d('admin', 'Save'); ?></a>
        <?php if ($this->request->getRequestTarget() != $this->Slug->getManufacturerProfile()) { ?>
            <a href="javascript:void(0);" class="btn btn-outline-light cancel"><i
            class="fas fa-times"></i> <?php echo __d('admin', 'Cancel'); ?></a>
        <?php } ?>
        <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_manufacturers'))]); ?>
    </div>
</div>

<div class="sc"></div>

<?php

if ($appAuth->isManufacturer()) {
    $url = $this->Slug->getManufacturerProfile();
} else {
    if ($isEditMode) {
        $url = $this->Slug->getManufacturerEdit($manufacturer->id_manufacturer);
    } else {
        $url = $this->Slug->getManufacturerAdd();
    }
}
    echo $this->Form->create($manufacturer, [
        'class' => 'fcs-form',
        'novalidate' => 'novalidate',
        'url' => $url,
        'id' => 'manufacturerEditForm',
    ]);

    echo $this->Form->hidden('referer', ['value' => $referer]);

    echo '<h2>'.__d('admin', 'General').'</h2>';

    $imprintString = '';
    if (Configure::read('app.showManufacturerImprint')) {
        if ($appAuth->isManufacturer()) {
            $imprintString = __d('admin', 'in_your_imprint');
        } else {
            $imprintString = __d('admin', 'in_the_imprint_of_the_manufacturer');
        }
    }

    echo $this->Form->control('Manufacturers.name', [
        'type' => 'text',
        'label' => __d('admin', 'Name'),
    ]);
    echo $this->Form->control('Manufacturers.address_manufacturer.email', [
        'type' => 'text',
        'label' => __d('admin', 'Email') . ($imprintString != '' ? '<span class="after small">'.__d('admin', 'Will_be_shown_in_imprint_{0}_and_spamprotected.', [$imprintString]).'</span>' : ''),
        'escape' => false,
    ]);
    echo $this->Form->control('Manufacturers.address_manufacturer.phone_mobile', [
        'label' => __d('admin', 'Mobile') . ($imprintString != '' ? ' <span class="after small">'.__d('admin', 'Will_be_shown_in_imprint_{0}.', [$imprintString]).'</span>' : ''),
        'escape' => false,
    ]);
    echo $this->Form->control('Manufacturers.address_manufacturer.phone', [
        'label' => __d('admin', 'Phone') . ($imprintString != '' ? ' <span class="after small">'.__d('admin', 'Will_be_shown_in_imprint_{0}.', [$imprintString]).'</span>' : ''),
        'escape' => false,
    ]);
    echo $this->Form->control('Manufacturers.homepage', [
        'placeholder' => __d('admin', 'Example_given_abbreviation') . ' https://www.foodcoopshop.com',
        'label' => __d('admin', 'Website') . ($imprintString != '' ? ' <span class="after small">'.__d('admin', 'Will_be_shown_in_imprint_{0}.', [$imprintString]).'</span>' : ''),
        'escape' => false,
    ]);
    if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
        echo $this->element('manufacturer/companyDetails');
    }

    if ($isEditMode) {
        $buttonOptions = ['class' => 'btn btn-outline-light', 'escape' => false];
        $buttonIcon = '<i class="fas fa-cog"></i> ';
        if ($appAuth->isManufacturer()) {
            $optionsLink = $this->Html->link($buttonIcon . __d('admin', 'To_your_settings'), $this->Slug->getManufacturerMyOptions(), $buttonOptions);
        } else {
            $optionsLink = $this->Html->link($buttonIcon . __d('admin', 'To_the_settings_of_manufacturer'), $this->Slug->getManufacturerEditOptions($manufacturer->id_manufacturer), $buttonOptions);
        }
        echo ' <span class="description">' . $optionsLink . '</span>';
    }

    echo '<div class="sc"></div>';

    if (Configure::read('app.showManufacturerListAndDetailPage')) {
        echo '<h2>' . __d('admin', 'Profile');
        if ($this->request->getRequestTarget() != $this->Slug->getManufacturerAdd()) {
            echo ' <span>' . $this->Html->link(__d('admin', 'To_manufacturer_profile'), $this->Slug->getManufacturerDetail($manufacturer->id_manufacturer, $manufacturer->name), [
            'target' => '_blank'
            ]) . '</span>';
        }
        echo '</h2>';

        $imageSrc = $this->Html->getManufacturerImageSrc($idForUpload, 'large');
        if (!empty($manufacturer->tmp_image) && $manufacturer->tmp_image != '') {
            $imageSrc = str_replace('\\', '/', $manufacturer->tmp_image);
        }
        $imageExists = ! preg_match('/de-default-large_default/', $imageSrc);
        echo '<div class="input">';
        echo '<label>'.__d('admin', 'Logo');
        if ($imageExists) {
            echo '<br /><span class="small">'.__d('admin', 'Click_on_logo_to_change_it.').'</span>';
        }
        echo '</label>';
        echo '<div style="float:right;">';
        echo $this->Html->link(
            $imageExists ? $this->Html->image($imageSrc) : '<i class="fas fa-plus-square"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light add-image-button ' . ($imageExists ? 'uploaded' : ''),
                'title' => __d('admin', 'Upload_new_logo_or_change_it'),
                'data-object-id' => $idForUpload,
                'escape' => false
            ]
        );
        echo '</div>';
        echo $this->Form->hidden('Manufacturers.tmp_image');
        echo '</div>';

        echo $this->Form->control('Manufacturers.delete_image', [
        'label' => __d('admin', 'Delete_logo?'). '<span class="after small">'.__d('admin', 'Check_and_do_not_forget_to_click_save_button.').'</span>',
        'type' => 'checkbox',
        'escape' => false,
        ]);

        if ($appAuth->isSuperadmin() || $appAuth->isAdmin()) {
            echo '<div style="margin-top:10px;"></div>';
            echo $this->Form->control('Manufacturers.short_description', [
                'class' => 'ckeditor',
                'type' => 'textarea',
                'label' => __d('admin', 'Short_description').'<br /><br /><span class="small">'.__d('admin', 'Will_be_shown_on_manufacturers_overview_page_and_cannot_be_changed_by_the_manufacturer.').'</span>',
                'escape' => false,
            ]);
        }

        $label = __d('admin', 'Long_description');
        if (!$isEditMode) {
            echo '<div class="input text">';
            echo '<label>' . $label . '</label>';
            echo '<p>'.__d('admin', 'To_save_long_description_press_save_and_then_edit_manufacturer.').'</p>';
            echo '</div>';
        } else {
            echo $this->Form->control('Manufacturers.description', [
                'class' => 'ckeditor',
                'type' => 'textarea',
                'label' => $label . '<br /><br /><span class="small">'.__d('admin', 'Will_be_shown_on_the_manufacturer_profile.').'<br /><br /><a href="'.$this->Html->getDocsUrl(__d('admin', 'docs_route_wysiwyg_editor')).'" target="_blank">'.__d('admin', 'How_do_I_use_the_WYSIWYG_editor?').'</a></span>',
                'escape' => false,
            ]);
        }
        echo '<div class="sc"></div>';

        echo $this->element('manufacturer/bankDetails');

    }

    if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
        echo '<h2>'.__d('admin', 'Company_data') . ($imprintString != '' ? ' <span>'.__d('admin', 'for_your_imprint_and_your_invoices_the_imprint_is_on_your_manufacturer_profile_bottom_right.').'</span>' : '') . '</h2>';
        echo $this->element('manufacturer/companyDetails');
    }

    if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
        echo $this->Form->control('Manufacturers.uid_number', [
        'label' => __d('admin', 'VAT_number').' <span class="after small">'.__d('admin', 'if_it_is_available').'</span>',
        'escape' => false,
        ]);

        $fileUploadSrc = $this->Html->getManufacturerTermsOfUseSrc($manufacturer->id_manufacturer);
        if (!empty($manufacturer->tmp_general_terms_and_conditions) && $manufacturer->tmp_general_terms_and_conditions != '') {
            $fileUploadSrc = str_replace('\\', '/', $manufacturer->tmp_general_terms_and_conditions);
        }
        $fileUploadExists = $fileUploadSrc !== false;

        echo '<div class="input fcs-upload">';
        echo '<label>'.__d('admin', 'General_terms_and_conditions');
        echo '</label>';

        echo '<div style="float:right;">';
        echo $this->Html->link(
            '<span style="padding:8px;float:left;">' . ($fileUploadExists ? __d('admin', 'Change_general_terms_and_conditions') : __d('admin', 'Upload_general_terms_and_conditions')).'</span>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light add-general-terms-and-conditions-button' . ($fileUploadExists ? ' uploaded' : ''),
                'title' => __d('admin', 'Upload_general_terms_and_conditions_or_change_them'),
                'data-object-id' => $idForUpload,
                'escape' => false
            ]
        );
        echo ' <span class="after small">'.__d('admin', 'If_you_do_not_upload_your_own_general_terms_and_conditions_(as_pdf)_the_default_general_terms_and_conditions_are_applied.').'</span>';
        echo '</div>';
        echo $this->Form->hidden('Manufacturers.tmp_general_terms_and_conditions');
        echo '</div>';

        if ($fileUploadExists) {
            echo $this->Form->control('Manufacturers.delete_general_terms_and_conditions', [
                'label' => __d('admin', 'Delete_general_terms_and_conditions?'). '<span class="after small">'.__d('admin', 'Check_and_do_not_forget_to_click_save_button.').'</span>',
                'type' => 'checkbox',
                'escape' => false,
            ]);
        }

        echo $this->Form->control('Manufacturers.firmenbuchnummer', [
        'label' => __d('admin', 'Commercial_register_number').' <span class="after small">'.__d('admin', 'if_it_is_available').'</span>',
        'escape' => false,
        ]);

        echo $this->Form->control('Manufacturers.firmengericht', [
        'label' => __d('admin', 'Company_court').' <span class="after small">'.__d('admin', 'if_it_is_available').'</span>',
        'escape' => false,
        ]);

        echo $this->Form->control('Manufacturers.aufsichtsbehoerde', [
        'label' => __d('admin', 'Supervisory_authority').' <span class="after small">'.__d('admin', 'if_it_is_available').'</span>',
        'escape' => false,
        ]);

        echo $this->Form->control('Manufacturers.kammer', [
        'placeholder' => __d('admin', 'e. g. chamber_of_agriculture'),
        'label' => __d('admin', 'Chamber').' <span class="after small">'.__d('admin', 'if_it_is_available').'</span>',
        'escape' => false,
        ]);

        echo $this->Form->control('Manufacturers.additional_text_for_invoice', [
        'type' => 'textarea',
        'label' => __d('admin', 'Additional_text_for_invoice') . '<br /><br /><span class="small">'.__d('admin', 'Will_be_printed_on_the_end_of_the_overview_page_of_your_invoice.').'<br />'.__d('admin', 'Example_for_additional_invoice_text').'</span>',
        'cols' => 81,
        'escape' => false,
        ]);
    }

    echo $this->Form->end();

?>


<div class="sc"></div>

<?php
if (Configure::read('app.showManufacturerListAndDetailPage')) {
    echo $this->element('imageUploadForm', [
        'id' => $idForUpload,
        'action' => '/admin/tools/doTmpImageUpload/',
        'imageExists' => $imageExists,
        'existingImageSrc' => $imageSrc,
    ]);
}
if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
    echo $this->element('fileUploadForm', [
        'id' => $idForUpload,
        'action' => '/admin/tools/doTmpFileUpload/',
        'fileName' => __d('admin', 'Filename_General-terms-and-conditions').'.pdf',
        'fileUploadExists' => $fileUploadExists,
        'existingFileUploadSrc' => $fileUploadSrc,
    ]);
}
?>
