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

use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Admin.initForm();" .
        Configure::read('app.jsNamespace') . ".Admin.bindDeleteCustomerButton(".$customer->id_customer.");".
        Configure::read('app.jsNamespace') . ".Upload.initImageUpload('body.customers .add-image-button', foodcoopshop.Upload.saveCustomerTmpImageInForm, foodcoopshop.AppFeatherlight.closeLightbox);
    "
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fas fa-check"></i> <?php echo __d('admin', 'Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fas fa-times"></i> <?php echo __d('admin', 'Cancel'); ?></a>
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

echo $this->Form->control('Customers.firstname', [
    'label' => __d('admin', 'Firstname'),
    'maxLength' => NAME_MAX_CHARS,
    'required' => true
]);
echo $this->Form->control('Customers.lastname', [
    'label' => __d('admin', 'Lastname'),
    'maxLength' => NAME_MAX_CHARS,
    'required' => true
]);

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
echo '</div>';

echo $this->Form->control('Customers.delete_image', [
    'label' => __d('admin', 'Delete_profile_image?'). '<span class="after small">'.__d('admin', 'Check_and_do_not_forget_to_click_save_button.').'</span>',
    'type' => 'checkbox',
    'escape' => false
]);

echo $this->Form->control('Customers.address_customer.email', [
    'label' => __d('admin', 'Email')
]);
echo $this->Form->control('Customers.address_customer.address1', [
    'label' => __d('admin', 'Street'),
    'maxLength' => STREET_MAX_CHARS,
]);
echo $this->Form->control('Customers.address_customer.address2', [
    'label' => __d('admin', 'Additional_address_information'),
    'maxLength' => STREET_MAX_CHARS,
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

if (Configure::read('app.emailOrderReminderEnabled')) {
    echo $this->Form->control('Customers.email_order_reminder', [
        'label' => __d('admin', 'Order_reminder').'<span class="after small">'.__d('admin', 'Want_to_receive_reminder_emails?').'</span>',
        'type' => 'checkbox',
        'escape' => false
    ]);
}

if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
    $label = __d('admin', 'Paying_with_time_module_active?') . ' ';
    $label .= '<span class="after small">'.__d('admin', 'I_want_to_be_able_to_pay_my_products_also_in_{0}.', [Configure::read('appDb.FCS_TIMEBASED_CURRENCY_NAME')]).' <a href="'.$this->Html->getDocsUrl(__d('admin', 'docs_route_paying_with_time_module')).'" target="_blank">'.__d('admin', 'How_do_I_use_the_paying_with_time_module?').'</a>';
    if (!$timebasedCurrencyDisableOptionAllowed) {
        $label .= ' Zum Deaktivieren der Option muss dein ' . $this->TimebasedCurrency->getName() . ' ausgeglichen sein, derzeit beträgt es '.$this->TimebasedCurrency->formatSecondsToTimebasedCurrency($timebasedCurrencyCreditBalance).'.';
    }
    $label .= '</span>';
    echo $this->Form->control('Customers.timebased_currency_enabled', [
        'label' => $label,
        'type' => 'checkbox',
        'disabled' => (!$timebasedCurrencyDisableOptionAllowed ? 'disabled' : ''),
        'escape' => false
    ]);
}

if ($isOwnProfile && Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED')) {
    echo '<a target="_blank" class="generate-my-member-card-button btn btn-outline-light" href="/admin/customers/generateMyMemberCard.pdf"><i class="far fa-address-card"></i> ' . __d('admin', 'Generate_my_member_card') . '</a>';
}

if ($appAuth->isSuperadmin()) {
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
