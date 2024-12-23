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
            class="fa-fw fas fa-check"></i> <?php echo __d('admin', 'Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fa-fw fas fa-times"></i> <?php echo __d('admin', 'Cancel'); ?></a>
        <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_members'))]); ?>
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
        'label' => __d('admin', 'Company_name'),
        'required' => true
    ]);
    echo $this->Form->control('Customers.lastname', [
        'label' => __d('admin', 'Contact_person') . '<span class="after small">'.__d('admin', 'Will_be_shown_on_invoices.').'</span>',
        'escape' => false,
    ]);
} else {
    echo $this->Form->control('Customers.firstname', [
        'label' => __d('admin', 'Firstname'),
        'required' => true
    ]);
    echo $this->Form->control('Customers.lastname', [
        'label' => __d('admin', 'Lastname'),
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
echo '<label>'.__d('admin', 'Profile_image');
echo '<br /><span class="small">';
if ($imageExists) {
    echo __d('admin', 'Click_on_profile_image_to_change_it.').'<br /><br />';
}
echo __d('admin', 'Only_visible_for_other_membes_in_the_member_list.');
if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
    echo '<br />' . __d('admin', 'Is_shown_on_member_card.');
}
echo '</span>';
echo '</label>';
echo '<div style="float:right;">';
echo $this->Html->link(
    $imageExists ? '<img src="' . $imageSrc . '" />' : '<i class="fas fa-plus-square"></i>',
    'javascript:void(0);',
    [
        'class' => 'btn btn-outline-light add-image-button ' . ($imageExists ? 'uploaded' : ''),
        'title' => __d('admin', 'Upload_new_profile_image_or_change_it'),
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
        'label' => __d('admin', 'Delete_profile_image?'). '<span class="after small">'.__d('admin', 'Check_and_do_not_forget_to_click_save_button.').'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
echo '</div>';

echo $this->Form->control('Customers.address_customer.email', [
    'label' => __d('admin', 'Email')
]);
echo $this->Form->control('Customers.address_customer.address1', [
    'label' => __d('admin', 'Street_and_number'),
]);
echo $this->Form->control('Customers.address_customer.address2', [
    'label' => __d('admin', 'Additional_address_information'),
    'required' => false,
]);
echo $this->Form->control('Customers.address_customer.postcode', [
    'label' => __d('admin', 'Zip')
]);
echo $this->Form->control('Customers.address_customer.city', [
    'label' => __d('admin', 'City')
]);
echo $this->Form->control('Customers.address_customer.phone_mobile', [
    'label' => __d('admin', 'Mobile')
]);
echo $this->Form->control('Customers.address_customer.phone', [
    'label' => __d('admin', 'Phone')
]);

echo '<div class="sc"></div>';
echo '<h2 style="margin-top:20px;">'.__d('admin', 'Notifications').'</h2>';

if (Configure::read('app.emailOrderReminderEnabled')) {
    echo $this->Form->control('Customers.email_order_reminder_enabled', [
        'label' => __d('admin', 'Order_reminder').'<span class="after small">'.__d('admin', 'Want_to_receive_order_reminder_emails?').'</span>',
        'type' => 'checkbox',
        'escape' => false,
    ]);
}

echo $this->Form->control('Customers.pickup_day_reminder_enabled', [
    'label' => __d('admin', 'Pickup_day_reminder').'<span class="after small">'.__d('admin', 'Want_to_receive_pickup_day_reminder_emails?').'</span>',
    'type' => 'checkbox',
    'escape' => false,
]);
if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
    echo $this->Form->control('Customers.invoices_per_email_enabled', [
        'label' => __d('admin', 'Invoices_per_email').'<span class="after small">'.__d('admin', 'Want_to_receive_invoices_per_email?').'</span>',
        'type' => 'checkbox',
        'escape' => false,
    ]);
}

if ($this->Html->paymentIsCashless()) {
    echo $this->Form->control('Customers.check_credit_reminder_enabled', [
        'label' => __d('admin', 'Check_credit_reminder').'<span class="after small">'.__d('admin', 'Want_to_receive_check_credit_reminder_emails_when_your_credit_is_lower_than_{0}?', [
            $this->Number->formatAsCurrency(Configure::read('appDb.FCS_CHECK_CREDIT_BALANCE_LIMIT')),
        ]).'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
    if (!$this->Configuration->isCashlessPaymentTypeManual()) {
        echo $this->Form->control('Customers.credit_upload_reminder_enabled', [
            'label' => __d('admin', 'Credit_upload_reminder').'<span class="after small">'.__d('admin', 'Want_to_receive_credit_upload_reminder?').'</span>',
            'type' => 'checkbox',
            'escape' => false,
        ]);
    }
}

if (Configure::read('appDb.FCS_NEWSLETTER_ENABLED')) {
    echo $this->Form->control('Customers.newsletter_enabled', [
        'label' => __d('admin', 'Newsletter').'<span class="after small">'.__d('admin', 'Want_to_receive_the_newsletter_per_email?').'</span>',
        'type' => 'checkbox',
        'escape' => false,
    ]);
}

if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')
    && (
        !Configure::read('appDb.FCS_SELF_SERVICE_MODE_TEST_MODE_ENABLED') || $identity->isSuperadmin())
    ) {
    echo '<div class="sc"></div>';
    echo '<h2 style="margin-top:20px;">' . __d('admin', 'Self_service_mode') . '</h2>';
    if ($isOwnProfile) {
        echo '<a target="_blank" class="generate-my-member-card-button btn btn-outline-light" href="/admin/customers/generateMyMemberCard.pdf"><i class="far fa-address-card"></i> ' . __d('admin', 'Generate_my_member_card') . '</a>';
    }
    echo $this->Form->control('Customers.use_camera_for_barcode_scanning', [
        'label' => __d('admin', 'I_want_to_use_my_smartphones_camera_for_barcode_scanning.'),
        'type' => 'checkbox',
        'escape' => false
    ]);
}

if ($identity->isSuperadmin()) {

    echo '<div class="sc"></div>';
    echo '<h2>'.__d('admin', 'Superadmin_functions').'</h2>';

    if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
        echo $this->Form->control('Customers.shopping_price', [
            'type' => 'select',
            'label' => __d('admin', 'Prices'),
            'options' => $this->Html->getShoppingPricesForDropdown(),
            'escape' => false,
        ]);
    }
    echo '<a class="delete-customer-button btn btn-danger" href="javascript:void(0);">'.__d('admin', 'Delete_member_irrevocably?').'</a>';
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
