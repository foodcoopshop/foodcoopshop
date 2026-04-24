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

use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Admin.initForm();" .
        Configure::read('app.jsNamespace') . ".ModalCustomerDelete.init(".$customer->id_customer.");".
        Configure::read('app.jsNamespace') . ".Upload.initImageUpload('body.customers .add-image-button', foodcoopshop.Upload.saveCustomerTmpImageInForm);
    "
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa-fw fas fa-check"></i> <?php echo __('Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fa-fw fas fa-times"></i> <?php echo __('Cancel'); ?></a>
        <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__('docs_route_members'))]); ?>
    </div>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create($customer, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $isOwnProfile ? $this->Slug->getCustomerProfile() : $this->Slug->getCustomerEdit($customer->id_customer)
]);

echo $this->Form->hidden('referer', ['value' => $referer]);

if ($customer->is_company) {
    echo $this->Form->control('Customers.firstname', [
        'label' => __('Company_name'),
        'required' => true
    ]);
    echo $this->Form->control('Customers.lastname', [
        'label' => __('Contact_person_admin') . '<span class="after small">'.__('Will_be_shown_on_invoices.').'</span>',
        'escape' => false,
    ]);
} else {
    echo $this->Form->control('Customers.firstname', [
        'label' => __('Firstname'),
        'required' => true
    ]);
    echo $this->Form->control('Customers.lastname', [
        'label' => __('Lastname'),
        'required' => true
    ]);
}
echo $this->Form->hidden('Customers.is_company');

$imageSrc = $this->Html->getCustomerImageSrc($customer->id_customer, 'large');
if (!empty($customer->tmp_image) && $customer->tmp_image != '') {
    $imageSrc = str_replace('\\', '/', $customer->tmp_image);
}
$imageExists = ! preg_match('/de-default-large_default/', $imageSrc);
$imageSrc = $this->Html->privateImage($imageSrc);
echo '<div class="input">';
echo '<label>'.__('Profile_image');
echo '<br /><span class="small">';
if ($imageExists) {
    echo __('Click_on_profile_image_to_change_it.').'<br /><br />';
}
echo __('Only_visible_for_other_membes_in_the_member_list.');
if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
    echo '<br />' . __('Is_shown_on_member_card.');
}
echo '</span>';
echo '</label>';
echo '<div style="float:right;">';
echo $this->Html->link(
    $imageExists ? '<img src="' . $imageSrc . '" />' : '<i class="fas fa-plus-square"></i>',
    'javascript:void(0);',
    [
        'class' => 'btn btn-outline-light add-image-button ' . ($imageExists ? 'uploaded' : ''),
        'title' => __('Upload_new_profile_image_or_change_it'),
        'data-object-id' => $customer->id_customer,
        'escape' => false
    ]
    );
echo '</div>';
echo $this->Form->hidden('Customers.tmp_image');
$this->Form->unlockField('Customers.tmp_image');
echo '</div>';

echo '<div class="warning">';
    echo $this->Form->control('Customers.delete_image', [
        'label' => __('Delete_profile_image?'). '<span class="after small">'.__('Check_and_do_not_forget_to_click_save_button._admin').'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
echo '</div>';

echo $this->Form->control('Customers.address_customer.email', [
    'label' => __('Email')
]);
echo $this->Form->control('Customers.address_customer.address1', [
    'label' => __('Street_and_number'),
]);
echo $this->Form->control('Customers.address_customer.address2', [
    'label' => __('Additional_address_information'),
    'required' => false,
]);
echo $this->Form->control('Customers.address_customer.postcode', [
    'label' => __('Zip')
]);
echo $this->Form->control('Customers.address_customer.city', [
    'label' => __('City')
]);
echo $this->Form->control('Customers.address_customer.phone_mobile', [
    'label' => __('Mobile')
]);
echo $this->Form->control('Customers.address_customer.phone', [
    'label' => __('Phone')
]);

echo '<div class="sc"></div>';
echo '<h2 style="margin-top:20px;">'.__('Notifications').'</h2>';

if (Configure::read('app.emailOrderReminderEnabled')) {
    echo $this->Form->control('Customers.email_order_reminder_enabled', [
        'label' => __('Order_reminder').'<span class="after small">'.__('Want_to_receive_order_reminder_emails?').'</span>',
        'type' => 'checkbox',
        'escape' => false,
    ]);
}

echo $this->Form->control('Customers.pickup_day_reminder_enabled', [
    'label' => __('Pickup_day_reminder').'<span class="after small">'.__('Want_to_receive_pickup_day_reminder_emails?').'</span>',
    'type' => 'checkbox',
    'escape' => false,
]);
if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
    echo $this->Form->control('Customers.invoices_per_email_enabled', [
        'label' => __('Invoices_per_email').'<span class="after small">'.__('Want_to_receive_invoices_per_email?').'</span>',
        'type' => 'checkbox',
        'escape' => false,
    ]);
}
echo $this->Form->control('Customers.send_cancellation_email', [
    'label' => __('Cancellations').'<span class="after small">'.__('I want to receive an email on every cancellation.').'</span>',
    'type' => 'checkbox',
    'escape' => false,
]);

if ($this->Html->paymentIsCashless()) {
    echo $this->Form->control('Customers.check_credit_reminder_enabled', [
        'label' => __('Check_credit_reminder').'<span class="after small">'.__('Want_to_receive_check_credit_reminder_emails_when_your_credit_is_lower_than_{0}?', [
            $this->Number->formatAsCurrency(Configure::read('appDb.FCS_CHECK_CREDIT_BALANCE_LIMIT')),
        ]).'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
    if (!$this->Configuration->isCashlessPaymentTypeManual()) {
        echo $this->Form->control('Customers.credit_upload_reminder_enabled', [
            'label' => __('Credit_upload_reminder').'<span class="after small">'.__('Want_to_receive_credit_upload_reminder?').'</span>',
            'type' => 'checkbox',
            'escape' => false,
        ]);
    }
}

if (Configure::read('appDb.FCS_NEWSLETTER_ENABLED')) {
    echo $this->Form->control('Customers.newsletter_enabled', [
        'label' => __('Newsletter').'<span class="after small">'.__('Want_to_receive_the_newsletter_per_email?').'</span>',
        'type' => 'checkbox',
        'escape' => false,
    ]);
}

if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')
    && (
        !Configure::read('appDb.FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED') || $identity->isSuperadmin())
    ) {
    echo '<div class="sc"></div>';
    echo '<h2 style="margin-top:20px;">' . __('Self_service_mode') . '</h2>';
    if ($isOwnProfile) {
        echo '<a target="_blank" class="generate-my-member-card-button btn btn-outline-light" href="/admin/customers/generateMyMemberCard.pdf"><i class="far fa-address-card"></i> ' . __('Generate_my_member_card') . '</a>';
    }
    echo $this->Form->control('Customers.use_camera_for_barcode_scanning', [
        'label' => __('I_want_to_use_my_smartphones_camera_for_barcode_scanning.'),
        'type' => 'checkbox',
        'escape' => false
    ]);
}

if ($identity->isSuperadmin()) {

    echo '<div class="sc"></div>';
    echo '<h2>'.__('Superadmin_functions').'</h2>';

    if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
        echo $this->Form->control('Customers.shopping_price', [
            'type' => 'select',
            'label' => __('Prices'),
            'options' => $this->Html->getShoppingPricesForDropdown(),
            'escape' => false,
        ]);
    }
    echo '<a class="delete-customer-button btn btn-danger" href="javascript:void(0);">'.__('Delete_member_irrevocably?').'</a>';
}

echo $this->Form->end(); ?>

<div class="sc"></div>

<?php
echo $this->element('imageUploadForm', [
    'id' => $customer->id_customer,
    'action' => '/admin/tools/doTmpImageUpload/',
    'imageExists' => $imageExists,
    'existingImageSrc' => $imageSrc
]);
