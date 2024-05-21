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
use Cake\Datasource\FactoryLocator;
use App\Model\Entity\Customer;

$paginator = $this->loadHelper('Paginator', [
    'className' => 'ArraySupportingSortOnlyPaginator',
]);

?>
<div id="customers-list">
    <?php
    $this->element('addScript', [
        'script' => Configure::read('app.jsNamespace') . ".Helper.initDatepicker();
            $('input.datepicker').datepicker();".
            Configure::read('app.jsNamespace') . ".Admin.init();" .
            Configure::read('app.jsNamespace') . ".ModalCustomerStatusEdit.init();" .
            Configure::read('app.jsNamespace') . ".ModalCustomerGroupEdit.init();" .
            Configure::read('app.jsNamespace') . ".Helper.initTooltip('.customer-details-read-button, .customer-comment-edit-button, .customer-email-button, .feedback-button');" .
            Configure::read('app.jsNamespace') . ".ModalCustomerCommentEdit.init();"
    ]);
    ?>

    <div class="filter-container">
        <?php echo $this->Form->create(null, ['type' => 'get']); ?>
            <?php echo $this->Form->control('active', ['type' => 'select', 'label' => '', 'options' => $this->MyHtml->getActiveStates(), 'default' => isset($active) ? $active : '']); ?>
            <?php
                if (Configure::read('appDb.FCS_MEMBER_FEE_PRODUCTS') != '') {
                    echo $this->Form->control('year', [
                        'type' => 'select',
                        'label' => '',
                        'empty' => __d('admin', 'Member_fee') . ' - ' . __d('admin', 'Show_all_years'),
                        'options' => $years,
                        'default' => $year != '' ? $year : ''
                    ]);
                }
                if (Configure::read('appDb.FCS_NEWSLETTER_ENABLED')) {
                    echo $this->Form->control('newsletter', [
                        'type' => 'select',
                        'label' => '',
                        'empty' => __d('admin', 'Newsletter'),
                        'options' => $this->Html->getYesNoArray(),
                        'default' => $newsletter,
                    ]);
                }
            ?>
            <div class="right">
                <?php
                    echo $this->element('customerList/moreDropdown', [
                        'helperLink' => $this->Html->getDocsUrl(__d('admin', 'docs_route_members')),
                    ]);
                ?>
            </div>
        <?php echo $this->Form->end(); ?>
    </div>

<?php

echo '<table class="list">';
echo '<tr class="sort">';
echo $this->element('rowMarker/rowMarkerAll', [
    'enabled' => true
]);
echo '<th>' . $paginator->sort('Customers.id_customer', 'ID') . '</th>';
echo '<th>' . $paginator->sort('CustomerNameForOrder', __d('admin', 'Name')) . '</th>';
echo '<th>' . $paginator->sort('Customers.id_default_group', __d('admin', 'Group')) . '</th>';
echo '<th>' . $paginator->sort('Customers.email', __d('admin', 'Email')) . '</th>';
echo '<th>' . $paginator->sort('Customers.active', __d('admin', 'Status')) . '</th>';
if (Configure::read('app.htmlHelper')->paymentIsCashless()) {
    echo '<th>' . $paginator->sort('credit_balance',  __d('admin', 'Credit'), ['direction' => 'desc']) . '</th>';
}
if (Configure::read('app.emailOrderReminderEnabled')) {
    echo '<th>' . $paginator->sort('Customers.email_order_reminder_enabled',  __d('admin', 'Order_reminder')) . '</th>';
}
if (Configure::read('app.htmlHelper')->paymentIsCashless()) {
    echo '<th>' . $paginator->sort('Customers.check_credit_reminder_enabled',  __d('admin', 'Check_credit_reminder')) . '</th>';
}
if (Configure::read('appDb.FCS_NEWSLETTER_ENABLED')) {
    echo '<th>' . $paginator->sort('Customers.newsletter_enabled',  __d('admin', 'Newsletter')) . '</th>';
}
if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED') && $identity->isSuperadmin()) {
    echo '<th>' . $paginator->sort('Feedbacks.modified',  __d('admin', 'Feedback')) . '</th>';
}
echo '<th>' . $paginator->sort('Customers.date_add',  __d('admin', 'Register_date')) . '</th>';
echo '<th>' . $paginator->sort('last_pickup_day',  __d('admin', 'Last_pickup_day'), ['direction' => 'desc']) . '</th>';
if (Configure::read('appDb.FCS_MEMBER_FEE_PRODUCTS') != '') {
    echo '<th>' . $paginator->sort('member_fee', __d('admin', 'Member_fee')) . '</th>';
}
if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
    echo '<th>' . $this->Paginator->sort('Customers.shopping_price', __d('admin', 'Prices')) . '</th>';
}
echo '<th>'.__d('admin', 'Comment_abbreviation').'</th>';
echo '</tr>';

$i = 0;
$sumOrderReminders = 0;
$sumCreditReminders = 0;
$sumNewsletter = 0;
$sumFeedback = 0;
$sumFeedbackNotApproved = 0;
foreach ($customers as $customer) {
    $i ++;

    echo '<tr class="data" data-customer-id="'.$customer->id_customer.'">';

    echo $this->element('rowMarker/rowMarker', [
        'show' => true
    ]);

    echo '<td style="text-align:right;">';
    echo $customer->id_customer;
    echo '</td>';

    echo '<td class="name">';

        $customerName = $this->Html->getNameRespectingIsDeleted($customer);

        if ($identity->isSuperadmin()) {
            echo $this->Html->link(
                '<i class="fas fa-pencil-alt ok"></i>',
                $this->Slug->getCustomerEdit($customer->id_customer),
                [
                    'class' => 'btn btn-outline-light edit-link',
                    'title' => __d('admin', 'Edit'),
                    'escape' => false
                ]
            );
        }
        if ($customer->different_pickup_day_count <= 2) {
            $customerName = '<i class="fas fa-carrot" title="'.__d('admin', 'Newbie_has_{0}_orders.', [
                $customer->different_pickup_day_count,
            ]).'"></i> ' . $customerName;
        }

        if ($lastOrderYear === false && $firstOrderYear === false) {
            $customerLink = $customerName;
        } else {
            $customerLink = $this->Html->link($customerName, '/admin/order-details?&pickupDay[]='.Configure::read('app.timeHelper')->formatToDateShort($firstOrderYear . '-01-01').'&pickupDay[]=' . Configure::read('app.timeHelper')->formatToDateShort($lastOrderYear . '-12-31') . '&customerId=' . $customer->id_customer . '&sort=OrderDetails.pickup_day&direction=desc', [
                'title' => __d('admin', 'Show_all_orders_from_{0}', [$this->Html->getNameRespectingIsDeleted($customer)]),
                'escape' => false
            ]);
        }
        echo '<span class="name">' . $customerLink . '</span>';

        echo '<div class="customer-details-wrapper">';
            $imageSrc = $this->Html->getCustomerImageSrc($customer->id_customer, 'small');
            $imageExists = ! preg_match('/de-default-small_default/', $imageSrc);
            $fontawesomeClass = 'far';
            if ($imageExists) {
                $fontawesomeClass = 'fas';
                $imageSrc = $this->Html->privateImage($imageSrc);
                $customerDetails = '<div style="height:270px;">';
                $customerDetails .= $this->Html->getCustomerAddress($customer);
                $customerDetails .= '<br /><img style="margin-top:10px;" class="no-max-width" height="200" src="'.$imageSrc.'" />';
                $customerDetails .= '</div>';
            } else {
                $customerDetails = $this->Html->getCustomerAddress($customer);
            }
            echo '<i class="'.$fontawesomeClass.' fa-address-card ok fa-lg customer-details-read-button" title="'.h($customerDetails).'"></i>';
        echo '</div>';

    echo '</td>';

    echo '<td>';

    if ($identity->getGroupId() >= $customer->id_default_group) {
        echo '<div class="table-cell-wrapper group">';
        echo $this->Html->link(
            '<i class="fas fa-pencil-alt ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light customer-group-edit-button',
                'title' => __d('admin', 'Change_group'),
                'escape' => false
            ]
        );
        echo '<span>' . $this->Html->getGroupName($customer->id_default_group) . '</span>';
        echo '</div>';
    } else {
        echo $this->Html->getGroupName($customer->id_default_group);
    }
    echo '<span class="group-for-dialog">' . $customer->id_default_group . '</span>';
    echo '</td>';

    echo '<td style="text-align:center;">';
        $classes = ['far fa-envelope ok fa-lg customer-email-button'];
        $title = h($customer->email);
        if ($customer->activate_email_code != null) {
            $classes[] = 'not-activated';
            $title .= '<br /><br />' . __d('admin', 'This_email_address_is_not_yet_activated_you_can_activate_it_here_{0}.', [
                $this->Slug->getActivateEmailAddress($customer->activate_email_code),
            ]);
        }

        echo '<i class="'.join(' ', $classes).'" data-email="'.h($customer->email).'" title="'.h($title).'"></i>';
    echo '</td>';

    echo '<td style="text-align:center;width:42px;">';

    if ($customer->active == 1) {
        echo $this->Html->link(
            '<i class="fas fa-check-circle ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light set-state-to-inactive change-active-state',
                'id' => 'change-active-state-' . $customer->id_customer,
                'title' => __d('admin', 'deactivate'),
                'escape' => false
            ]
        );
    }

    if ($customer->active == 0 && is_null($customer->activate_email_code)) {
        echo $this->Html->link(
            '<i class="fas fa-minus-circle not-ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light set-state-to-active change-active-state',
                'id' => 'change-active-state-' . $customer->id_customer,
                'title' => __d('admin', 'activate'),
                'escape' => false
            ]
        );
    }

    echo '</td>';

    if ($this->Html->paymentIsCashless()) {
        $negativeClass = $customer->credit_balance < 0 ? 'negative' : '';
        echo '<td style="text-align:center" class="' . $negativeClass . '">';

        if ($identity->isSuperadmin()) {
            $creditBalanceHtml = '<span class="'.$negativeClass.'">' . $this->Number->formatAsCurrency($customer->credit_balance);
            echo $this->Html->link(
                $creditBalanceHtml,
                $this->Slug->getCreditBalance($customer->id_customer),
                [
                    'class' => 'btn btn-outline-light',
                    'title' => __d('admin', 'Show_credit'),
                    'escape' => false
                ]
            );
        } else {
            if ($customer->credit_balance != 0) {
                echo $this->Number->formatAsCurrency($customer->credit_balance);
            }
        }

        echo '</td>';
    }

    if (Configure::read('app.emailOrderReminderEnabled')) {
        echo '<td align="center">';
        if ($customer->email_order_reminder_enabled) {
            echo '<i class="fas fa-check-circle ok"></i>';
            $sumOrderReminders++;
        }
        echo '</td>';
    }

    if ($this->Html->paymentIsCashless()) {
        echo '<td align="center">';
        if ($customer->check_credit_reminder_enabled) {
            echo '<i class="fas fa-check-circle ok"></i>';
            $sumCreditReminders++;
        }
        echo '</td>';
    }

    if (Configure::read('appDb.FCS_NEWSLETTER_ENABLED')) {
        echo '<td align="center">';
        if ($customer->newsletter_enabled) {
            echo '<i class="fas fa-check-circle ok"></i>';
            $sumNewsletter++;
        }
        echo '</td>';
    }

    if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED') && $identity->isSuperadmin()) {
        echo '<td align="center">';
        if (!empty($customer->feedback)) {
            $feedbackTable = FactoryLocator::get('Table')->get('Feedbacks');
            $approved = $feedbackTable->isApproved($customer->feedback);
            $tooltipContent = __d('admin', 'created') . ': ' . $customer->feedback->created->i18nFormat($this->Time->getI18Format('DateNTimeShort2')) . '<br />';
            $tooltipContent .= __d('admin', 'changed') . ': ' . $customer->feedback->modified->i18nFormat($this->Time->getI18Format('DateNTimeShort2'));
            echo $this->Html->link(
                '<i class="fas fa-heart '.(!$approved ? 'not-ok' : 'ok').'"></i>',
                $this->Slug->getFeedbackForm($customer->id_customer),
                [
                    'class' => 'btn btn-outline-light feedback-button',
                    'escape' => false,
                    'title' => $tooltipContent,
                ],
            );
            $sumFeedback++;
            if (!$approved) {
                $sumFeedbackNotApproved++;
            }
        } else {
            $tooltipContent = __d('admin', 'Create_feedback_for_{0}.', [
                $customer->name,
            ]);
            echo $this->Html->link(
                '<i class="far fa-heart ok"></i>',
                $this->Slug->getFeedbackForm($customer->id_customer),
                [
                    'class' => 'btn btn-outline-light feedback-button',
                    'escape' => false,
                    'title' => $tooltipContent,
                ],
            );
        }
        echo '</td>';
    }

    echo '<td>';
    echo $customer->date_add->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateShort'));
    echo '</td>';

    echo '<td>';
        if (!empty($customer->last_pickup_day)) {
            echo $customer->last_pickup_day->pickup_day->i18nFormat(Configure::read('app.timeHelper')->getI18Format('DateShort'));
        }
    echo '</td>';

    if (Configure::read('appDb.FCS_MEMBER_FEE_PRODUCTS') != '') {
        echo '<td style="text-align:right;">';
            echo $this->Number->formatAsCurrency($customer->member_fee);
        echo '</td>';
    }

    if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
        echo '<td style="text-align:right;">';
            if ($customer->shopping_price == Customer::PURCHASE_PRICE) {
                echo __d('admin', 'Purchase_price_abbreviation');
            }
            if ($customer->shopping_price == Customer::ZERO_PRICE) {
                echo __d('admin', 'Zero_price_abbreviation');
            }
        echo '</td>';
    }

    echo '<td style="padding-left: 11px;">';
        $commentText = $customer->address_customer->comment != '' ? $customer->address_customer->comment : __d('admin', 'Add_comment');
        echo $this->Html->link(
            '<i class="fas fa-comment-dots ok"></i>',
            'javascript:void(0);',
            [
                'class' => 'btn btn-outline-light customer-comment-edit-button' . ($customer->address_customer->comment == '' ? ' btn-disabled' : ''),
                'title' => h($commentText),
                'originalTitle' => h($commentText),
                'escape' => false
            ]
        );
    echo '</td>';

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="6"><b>' . $i . '</b> '.__d('admin', '{0,plural,=1{record} other{records}}', $i).'</td>';
$colspan = 3;
if ($this->Html->paymentIsCashless()) {
    echo '<td></td>';
}
if (Configure::read('app.emailOrderReminderEnabled')) {
    echo '<td align="center"><b>' . $sumOrderReminders . '</b></td>';
}
if ($this->Html->paymentIsCashless()) {
    echo '<td align="center"><b>' . $sumCreditReminders . '</b></td>';
}
if (Configure::read('appDb.FCS_NEWSLETTER_ENABLED')) {
    echo '<td align="center"><b>' . $sumNewsletter . '</b></td>';
}
if (Configure::read('appDb.FCS_USER_FEEDBACK_ENABLED') && $sumFeedback > 0) {
    echo '<td align="center"><b>' . $sumFeedback . ($sumFeedbackNotApproved > 0 ? ' (' . $sumFeedbackNotApproved . ')' : ''). '</b></td>';
} else {
    $colspan++;
}
if (Configure::read('appDb.FCS_MEMBER_FEE_PRODUCTS') != '') {
    $colspan++;
}
if (Configure::read('appDb.FCS_SEND_INVOICES_TO_CUSTOMERS')) {
    $colspan++;
}
echo '<td colspan="'.$colspan.'"></td>';
echo '</tr>';

echo '</table>';

echo '<div class="sc"></div>';

echo '<div class="hide">';
    echo $this->Form->control('selectGroupId', [
        'type' => 'select',
        'label' => '',
        'options' => $this->Html->getAuthDependentGroups($identity->getGroupId())
    ]);
echo '</div>';

?>
    <div class="sc"></div>
</div>
