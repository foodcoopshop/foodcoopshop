<?php
declare(strict_types=1);

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
use Cake\Datasource\FactoryLocator;
use Cake\ORM\TableRegistry;

$this->element('addScript', [
    'script' =>
        Configure::read('app.jsNamespace') . ".Admin.init();" .
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

$approvalInfoText = '';
if (isset($feedback->approved)) {
    $feedbacksTable = TableRegistry::getTableLocator()->get('Feedbacks');
    $approved = $feedbacksTable->isApproved($feedback);
    if (!$approved) {
        $approvalWarning = __d('admin', 'Your_feedback_has_not_yet_been_reviewed_by_an_admin_and_is_therefore_not_yet_published.');
        echo '<h2 class="warning" style="margin-bottom:10px;">'.$approvalWarning.'</h2>';
    } else {
        $approvalInfo = __d('admin', 'Your_feedback_has_been_reviewed_and_is_published_on_this_site_{0}._Thank_you.', [
            $this->Slug->getFeedbackList(),
        ]);
        echo '<h2 class="info" style="margin-bottom:10px;">'.$approvalInfo.'</h2>';
    }
}

if (!$isEditMode || (isset($feedback->approved) && !$approved)) {
    $approvalInfoText = '<p>' . __d('admin', 'Approval_info_text_{0}.', [
        $this->Slug->getFeedbackList(),
    ]) . '</p>';
}

$feedbackIntroText = __d('admin', 'Feedback_intro_text_customer_{0}.', [
    '<b>' . Configure::read('appDb.FCS_APP_NAME') . '</b>',
]);
if ($isManufacturer) {
    $feedbackIntroText = __d('admin', 'Feedback_intro_text_manufacturer_{0}.', [
        '<b>' . Configure::read('appDb.FCS_APP_NAME') . '</b>',
    ]);
}
echo '<p>' . $feedbackIntroText . '</p>';

if ($approvalInfoText != '') {
    echo $approvalInfoText;
}

echo $this->Form->hidden('referer', ['value' => $referer]);

$maxChars = 1000;
$feedbackFieldExplanationText = __d('admin', 'Feedback_field_explanation_text_customer.');
if ($isManufacturer) {
    $feedbackFieldExplanationText = __d('admin', 'Feedback_field_explanation_text_manufacturer.');
}
echo $this->Form->control('Feedbacks.text', [
    'label' => '<span class="small">'. $feedbackFieldExplanationText . '<br /><br /><i>' . __d('admin', 'Max._{0}_characters.', [
        $this->Number->formatAsDecimal($maxChars, 0),
    ]) . '</i>',
    'type' => 'textarea',
    'placeholder' => __d('admin', 'Please_enter_your_feedback_here.'),
    'maxlength' => $maxChars,
    'escape' => false,
]);

echo $this->Form->control('Feedbacks.privacy_type', [
    'label' => __d('admin', 'Show_name?') .' <span class="after small">'.__d('admin', 'Privacy_type_explanatin_text.').'</span>',
    'options' => $privacyTypes,
    'escape' => false,
]);

if (isset($feedback->approved_checkbox) && $identity->isSuperadmin()) {
    echo $this->Form->control('Feedbacks.approved_checkbox', [
        'label' => __d('admin', 'Approved') . ' <span class="after small">'.__d('admin', 'Only_superadmins_can_approve_feedbacks.').'</span>',
        'type' => 'checkbox',
        'escape' => false,
    ]);
}

if ($isEditMode) {
    echo '<div class="warning">';
        echo $this->Form->control('Feedbacks.delete_feedback', [
            'label' => __d('admin', 'Delete_feedback?') . ' <span class="after small">'.__d('admin', 'Check_and_do_not_forget_to_click_save_button.').'</span>',
            'type' => 'checkbox',
            'escape' => false,
        ]);
    echo '</div>';
}

echo '<div class="sc"></div>';

echo $this->Form->end(); ?>
