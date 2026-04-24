<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;

$this->element('addScript', [
    'script' => Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Upload.initImageUpload('body.manufacturers .add-image-button', foodcoopshop.Upload.saveManufacturerTmpImageInForm);" .
        Configure::read('app.jsNamespace') . ".Upload.initFileUpload('body.manufacturers .add-general-terms-and-conditions-button', foodcoopshop.Upload.saveManufacturerTmpGeneralTermsAndConditionsInForm);" .
        Configure::read('app.jsNamespace') . ".Admin.initForm();"
]);

$idForUpload = !empty($manufacturer->id_manufacturer) ? $manufacturer->id_manufacturer : StringComponent::createRandomString(6);

if ($identity->isAdmin() || $identity->isSuperadmin()) {
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Editor.initSmall('manufacturers-short-description');"
    ]);
}
if (!empty($manufacturer->id_manufacturer)) {
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Editor.initBig('manufacturers-description');"
    ]);
}

?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa-fw fas fa-check"></i> <?php echo __('Save'); ?></a>
        <?php if ($this->request->getRequestTarget() != $this->Slug->getManufacturerProfile()) { ?>
            <a href="javascript:void(0);" class="btn btn-outline-light cancel"><i
            class="fa-fw fas fa-times"></i> <?php echo __('Cancel'); ?></a>
        <?php } ?>
        <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__('docs_route_manufacturers'))]); ?>
    </div>
</div>

<div class="sc"></div>

<?php

if ($identity->isManufacturer()) {
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

    echo '<h2>'.__('General').'</h2>';

    $imprintString = '';
    if (Configure::read('app.showManufacturerImprint')) {
        if ($identity->isManufacturer()) {
            $imprintString = __('in_your_imprint');
        } else {
            $imprintString = __('in_the_imprint_of_the_manufacturer');
        }
    }

    echo $this->Form->control('Manufacturers.name', [
        'type' => 'text',
        'label' => __('Name'),
    ]);
    echo $this->Form->control('Manufacturers.address_manufacturer.email', [
        'type' => 'text',
        'label' => __('Email') . ($imprintString != '' ? '<span class="after small">'.__('Will_be_shown_in_imprint_{0}_and_spamprotected.', [$imprintString]).'</span>' : ''),
        'escape' => false,
    ]);
    echo $this->Form->control('Manufacturers.address_manufacturer.phone_mobile', [
        'label' => __('Mobile') . ($imprintString != '' ? ' <span class="after small">'.__('Will_be_shown_in_imprint_{0}.', [$imprintString]).'</span>' : ''),
        'escape' => false,
    ]);
    echo $this->Form->control('Manufacturers.address_manufacturer.phone', [
        'label' => __('Phone') . ($imprintString != '' ? ' <span class="after small">'.__('Will_be_shown_in_imprint_{0}.', [$imprintString]).'</span>' : ''),
        'escape' => false,
    ]);
    echo $this->Form->control('Manufacturers.homepage', [
        'placeholder' => __('Example_given_abbreviation') . ' https://www.foodcoopshop.com',
        'label' => __('Website') . ($imprintString != '' ? ' <span class="after small">'.__('Will_be_shown_in_imprint_{0}.', [$imprintString]).'</span>' : ''),
        'escape' => false,
    ]);
    if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
        echo $this->element('manufacturer/companyDetails');
    }

    if ($isEditMode) {
        $buttonOptions = ['class' => 'btn btn-outline-light', 'escape' => false];
        $buttonIcon = '<i class="fas fa-cog"></i> ';
        if ($identity->isManufacturer()) {
            $optionsLink = $this->Html->link($buttonIcon . __('To_your_settings'), $this->Slug->getManufacturerMyOptions(), $buttonOptions);
        } else {
            $optionsLink = $this->Html->link($buttonIcon . __('To_the_settings_of_manufacturer'), $this->Slug->getManufacturerEditOptions($manufacturer->id_manufacturer), $buttonOptions);
        }
        echo ' <span class="description">' . $optionsLink . '</span>';
    }

    echo '<div class="sc"></div>';

    if (Configure::read('app.showManufacturerListAndDetailPage')) {
        echo '<h2>' . __('Profile');
        if ($this->request->getRequestTarget() != $this->Slug->getManufacturerAdd()) {
            echo ' <span>' . $this->Html->link(__('To_manufacturer_profile'), $this->Slug->getManufacturerDetail($manufacturer->id_manufacturer, $manufacturer->name), [
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
        echo '<label>'.__('Logo');
        if ($imageExists) {
            echo '<br /><span class="small">'.__('Click_on_logo_to_change_it.').'</span>';
        }
        echo '</label>';
        echo '<div style="float:right;">';
        echo $this->Html->link(
            $imageExists ? $this->Html->image($imageSrc) : '<i class="fas fa-plus-square"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light add-image-button ' . ($imageExists ? 'uploaded' : ''),
                'title' => __('Upload_new_logo_or_change_it'),
                'data-object-id' => $idForUpload,
                'escape' => false
            ]
        );
        echo '</div>';
        echo $this->Form->hidden('Manufacturers.tmp_image');
        $this->Form->unlockField('Manufacturers.tmp_image');
        echo '</div>';

        echo '<div class="warning">';
            echo $this->Form->control('Manufacturers.delete_image', [
            'label' => __('Delete_logo?'). '<span class="after small">'.__('Check_and_do_not_forget_to_click_save_button._admin').'</span>',
            'type' => 'checkbox',
            'escape' => false,
            ]);
        echo '</div>';

        if ($identity->isSuperadmin() || $identity->isAdmin()) {
            echo '<div style="margin-top:10px;"></div>';
            echo $this->Form->control('Manufacturers.short_description', [
                'type' => 'textarea',
                'label' => __('Short_description').'<br /><br /><span class="small">'.__('Will_be_shown_on_manufacturers_overview_page_and_cannot_be_changed_by_the_manufacturer.').'</span>',
                'escape' => false,
            ]);
        }

        $label = __('Long_description');
        if (!$isEditMode) {
            echo '<div class="input text">';
            echo '<label>' . $label . '</label>';
            echo '<p>'.__('To_save_long_description_press_save_and_then_edit_manufacturer.').'</p>';
            echo '</div>';
        } else {
            echo $this->Form->control('Manufacturers.description', [
                'type' => 'textarea',
                'label' => $label . '<br /><br /><span class="small">'.__('Will_be_shown_on_the_manufacturer_profile.').'<br /><br /><a href="'.$this->Html->getDocsUrl(__('docs_route_wysiwyg_editor')).'" target="_blank">'.__('How_do_I_use_the_WYSIWYG_editor?').'</a></span>',
                'escape' => false,
            ]);
        }
        echo '<div class="sc"></div>';

        echo $this->element('manufacturer/bankDetails');

    }

    if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
        echo '<h2>'.__('Company_data') . ($imprintString != '' ? ' <span>'.__('for_your_imprint_and_your_invoices_the_imprint_is_on_your_manufacturer_profile_bottom_right.').'</span>' : '') . '</h2>';
        echo $this->element('manufacturer/companyDetails');
    }

    if (!Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
        echo $this->Form->control('Manufacturers.uid_number', [
        'label' => __('VAT_number').' <span class="after small">'.__('if_it_is_available').'</span>',
        'escape' => false,
        ]);

        $fileUploadSrc = $this->Html->getManufacturerTermsOfUseSrc($idForUpload);
        if (!empty($manufacturer->tmp_general_terms_and_conditions) && $manufacturer->tmp_general_terms_and_conditions != '') {
            $fileUploadSrc = str_replace('\\', '/', $manufacturer->tmp_general_terms_and_conditions);
        }
        $fileUploadExists = $fileUploadSrc !== false;

        echo '<div class="input fcs-upload">';
        echo '<label>'.__('General_terms_and_conditions');
        echo '</label>';

        echo '<div style="float:right;">';
        echo $this->Html->link(
            '<span style="padding:8px;float:left;">' . ($fileUploadExists ? __('Change_general_terms_and_conditions') : __('Upload_general_terms_and_conditions')).'</span>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light add-general-terms-and-conditions-button' . ($fileUploadExists ? ' uploaded' : ''),
                'title' => __('Upload_general_terms_and_conditions_or_change_them'),
                'data-object-id' => $idForUpload,
                'escape' => false
            ]
        );
        echo ' <span class="after small">'.__('If_you_do_not_upload_your_own_general_terms_and_conditions_(as_pdf)_the_default_general_terms_and_conditions_are_applied.').'</span>';
        echo '</div>';
        echo $this->Form->hidden('Manufacturers.tmp_general_terms_and_conditions');
        $this->Form->unlockField('Manufacturers.tmp_general_terms_and_conditions');
        echo '</div>';

        if ($fileUploadExists) {
            echo $this->Form->control('Manufacturers.delete_general_terms_and_conditions', [
                'label' => __('Delete_general_terms_and_conditions?'). '<span class="after small">'.__('Check_and_do_not_forget_to_click_save_button._admin').'</span>',
                'type' => 'checkbox',
                'escape' => false,
            ]);
        }

        echo $this->Form->control('Manufacturers.firmenbuchnummer', [
        'label' => __('Commercial_register_number_admin').' <span class="after small">'.__('if_it_is_available').'</span>',
        'escape' => false,
        ]);

        echo $this->Form->control('Manufacturers.firmengericht', [
        'label' => __('Company_court').' <span class="after small">'.__('if_it_is_available').'</span>',
        'escape' => false,
        ]);

        echo $this->Form->control('Manufacturers.aufsichtsbehoerde', [
        'label' => __('Supervisory_authority').' <span class="after small">'.__('if_it_is_available').'</span>',
        'escape' => false,
        ]);

        echo $this->Form->control('Manufacturers.kammer', [
        'placeholder' => __('e. g. chamber_of_agriculture'),
        'label' => __('Chamber').' <span class="after small">'.__('if_it_is_available').'</span>',
        'escape' => false,
        ]);

        echo $this->Form->control('Manufacturers.additional_text_for_invoice', [
        'type' => 'textarea',
        'label' => __('Additional_text_for_invoice') . '<br /><br /><span class="small">'.__('Will_be_printed_on_the_end_of_the_overview_page_of_your_invoice.').'<br />'.__('Example_for_additional_invoice_text').'</span>',
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
        'fileName' => __('Filename_General-terms-and-conditions_admin').'.pdf',
        'fileUploadExists' => $fileUploadExists,
        'existingFileUploadSrc' => $fileUploadSrc,
    ]);
}
?>
