<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();" .
        Configure::read('app.jsNamespace') . ".Helper.initCkeditor('feedbacks-text');" .
        Configure::read('app.jsNamespace') . ".Admin.initForm();
    "
]);
?>

<div class="filter-container">
    <h1><?php echo $title_for_layout; ?></h1>
    <div class="right">
        <a href="javascript:void(0);" class="btn btn-success submit"><i
            class="fa-fw fas fa-check"></i> <?php echo __d('admin', 'Save'); ?></a> <a href="javascript:void(0);"
            class="btn btn-outline-light cancel"><i class="fa-fw fas fa-times"></i> <?php echo __d('admin', 'Cancel'); ?></a>
        <?php echo $this->element('headerIcons', ['helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_user_feedback'))]); ?>
    </div>
</div>

<div class="sc"></div>

<?php

echo $this->Form->create($feedback, [
    'class' => 'fcs-form',
    'novalidate' => 'novalidate',
    'url' => $isOwnForm ? $this->Slug->getMyFeedbackForm() : $this->Slug->getFeedbackForm($customer->id_customer),
]);


if (isset($feedback->approved)) {
    $approvedDate = $feedback->approved->i18nFormat(Configure::read('app.timeHelper')->getI18Format('Database'));
    $notApproved = $this->Time->isDatabaseDateNotSet($approvedDate);
    if ($notApproved) {
        $approvalWarning = __d('admin', 'Your_feedback_has_not_yet_been_reviewed_by_an_admin_and_is_therefore_not_yet_published.');
        echo '<h2 class="warning" style="margin-bottom:10px;">'.$approvalWarning.'</h2>';
    } else {
        $approvalInfo = __d('admin', 'Your_feedback_has_been_reviewed_and_is_published._Thank_you.');
        echo '<h2 class="info" style="margin-bottom:10px;">'.$approvalInfo.'</h2>';
    }
}

echo $this->Form->hidden('referer', ['value' => $referer]);

echo $this->Form->control('Feedbacks.text', [
    'label' => $title_for_layout,
    'class' => 'ckeditor',
    'type' => 'textarea',
]);

echo $this->Form->control('Feedbacks.privacy_type', [
    'label' => __d('admin', 'Privacy_type'),
    'options' => $privacyTypes,
]);

if ($appAuth->isAdmin() || $appAuth->isSuperadmin()) {
    echo $this->Form->control('Feedbacks.approved_checkbox', [
        'label' => __d('admin', 'Approved'),
        'type' => 'checkbox',
    ]);
}

echo '<div class="sc"></div>';

echo $this->Form->end(); ?>
