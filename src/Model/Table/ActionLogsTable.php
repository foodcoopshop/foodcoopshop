<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\I18n\DateTime;

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
class ActionLogsTable extends AppTable
{

    public $types;

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id'
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'object_id',
            'conditions' => [
                'object_type' => 'products'
            ]
        ]);
        $this->belongsTo('Manufacturers', [
            'foreignKey' => 'object_id',
            'conditions' => [
                'object_type' => 'manufacturers'
            ]
        ]);
        $this->belongsTo('BlogPosts', [
            'foreignKey' => 'object_id',
            'conditions' => [
                'object_type' => 'blog_posts'
            ]
        ]);
        $this->belongsTo('Payments', [
            'foreignKey' => 'object_id',
            'conditions' => [
                'object_type' => 'payments'
            ]
        ]);
        $this->initTypes();
    }

    private function initTypes()
    {
        $this->types = [
            'invoice_added' => [
                'name' => __('Action_Log_Invoice_added'),
            ],
            'invoice_cancelled' => [
                'name' => __('Action_Log_Invoice_cancelled'),
            ],
            'product_added' => [
                'name' => __('Action_Log_Product_added'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_attribute_added' => [
                'name' => __('Action_Log_Product_attribute_added'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_attribute_changed' => [
                'name' => __('Action_Log_Product_attribute_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_image_added' => [
                'name' => __('Action_Log_Product_image_uploaded'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_image_deleted' => [
                'name' => __('Action_Log_Product_image_deleted'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_attribute_deleted' => [
                'name' => __('Action_Log_Product_attribute_deleted'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_set_inactive' => [
                'name' => __('Action_Log_Product_deactivated'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_set_active' => [
                'name' => __('Action_Log_Product_activated'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_quantity_changed' => [
                'name' => __('Action_Log_Product_quantity_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_purchase_price_changed' => [
                'name' => __('Action_Log_Product_purchase_price_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_price_changed' => [
                'name' => __('Action_Log_Product_price_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_name_changed' => [
                'name' => __('Action_Log_Product_name_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_unity_changed' => [
                'name' => __('Action_Log_Product_unity_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_description_changed' => [
                'name' => __('Action_Log_Product_description_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_description_short_changed' => [
                'name' => __('Action_Log_Product_description_short_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_deposit_changed' => [
                'name' => __('Action_Log_Product_deposit_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_tax_changed' => [
                'name' => __('Action_Log_Product_tax_rate_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_categories_changed' => [
                'name' => __('Action_Log_Product_categories_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_set_to_old' => [
                'name' => __('Action_Log_Product_set_to_not_new'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_set_to_new' => [
                'name' => __('Action_Log_Product_set_to_new'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_default_attribute_changed' => [
                'name' => __('Action_Log_Product_default_attribute_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_remotely_changed' => [
                'name' => __('Action_Log_Network_module_product_synchronized'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_delivery_rhythm_changed' => [
                'name' => __('Action_Log_Delivery_rhythm_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_deleted' => [
                'name' => __('Action_Log_Product_deleted'),
                'access' => [
                    'manufacturer'
                ]
            ],

            'orders_state_changed' => [
                'name' => __('Action_Log_Order_status_changed')
            ],
            'orders_closed' => [
                'name' => __('Action_Log_Orders_closed')
            ],
            'order_comment_changed' => [
                'name' => __('Action_Log_Order_comment_changed')
            ],
            'orders_date_changed' => [
                'name' => __('Action_Log_Order_date_changed')
            ],
            'instant_order_added' => [
                'name' => __('Action_Log_Instant_order_placed')
            ],
            'self_service_order_added' => [
                'name' => __('Action_Log_Self_service_order_placed')
            ],
            'order_detail_product_price_changed' => [
                'name' => __('Action_Log_Order_detail_product_price_changed'),
                'access' => [
                    'manufacturer',
                    'manufacturerAnonymizationEnabled',
                ]
            ],
            'order_detail_product_quantity_changed' => [
                'name' => __('Action_Log_Order_detail_product_quantity_changed'),
                'access' => [
                    'manufacturer',
                    'manufacturerAnonymizationEnabled',
                ]
            ],
            'order_detail_product_amount_changed' => [
                'name' => __('Action_Log_Order_detail_product_amount_changed'),
                'access' => [
                    'manufacturer',
                    'manufacturerAnonymizationEnabled',
                ]
            ],
            'order_detail_customer_changed' => [
                'name' => __('Action_Log_Order_detail_customer_changed')
            ],
            'order_detail_cancelled' => [
                'name' => __('Action_Log_Ordered_product_cancelled'),
                'access' => [
                    'manufacturer',
                    'manufacturerAnonymizationEnabled',
                ]
            ],
            'order_detail_pickup_day_changed' => [
                'name' => __('Action_Log_Ordered_product_pickup_day_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'order_detail_feedback_added' => [
                'name' => __('Action_Log_Ordered_product_feedback_added'),
                'access' => [
                    'manufacturer'
                ]
            ],

            'payment_product_added' => [
                'name' => __('Action_Log_Member_credit_upload_added')
            ],
            'payment_product_csv_imported' => [
                'name' => __('Action_Log_Credit_upload_csv_imported')
            ],
            'payment_product_approval_ok' => [
                'name' => __('Action_Log_Credit_upload_approval_ok')
            ],
            'payment_product_approval_open' => [
                'name' => __('Action_Log_Credit_upload_approval_open')
            ],
            'payment_product_approval_not_ok' => [
                'name' => __('Action_Log_Credit_upload_approval_not_ok')
            ],
            'payment_payback_added' => [
                'name' => __('Action_Log_Member_payback_added')
            ],
            'payment_payback_deleted' => [
                'name' => __('Action_Log_Member_payback_deleted')
            ],
            'payment_product_deleted' => [
                'name' => __('Action_Log_Member_credit_deleted')
            ],
            'payment_deposit_customer_added' => [
                'name' => __('Action_Log_Member_deposit_added')
            ],
            'payment_deposit_manufacturer_added' => [
                'name' => __('Action_Log_Manfufacturer_deposit_added'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'payment_deposit_customer_deleted' => [
                'name' => __('Action_Log_Member_deposit_deleted')
            ],
            'payment_deposit_manufacturer_deleted' => [
                'name' => __('Action_Log_Manufacturer_deposit_deleted'),
                'access' => [
                    'manufacturer'
                ]
            ],

            'blog_post_added' => [
                'name' => __('Action_Log_Blog_post_added')
            ],
            'blog_post_changed' => [
                'name' => __('Action_Log_Blog_post_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'blog_post_deleted' => [
                'name' => __('Action_Log_Blog_post_deleted'),
                'access' => [
                    'manufacturer'
                ]
            ],

            'page_added' => [
                'name' => __('Action_Log_Page_added')
            ],
            'page_changed' => [
                'name' => __('Action_Log_Page_changed')
            ],
            'page_deleted' => [
                'name' => __('Action_Log_Page_deleted')
            ],

            'category_added' => [
                'name' => __('Action_Log_Category_added')
            ],
            'category_changed' => [
                'name' => __('Action_Log_Category_changed')
            ],
            'category_deleted' => [
                'name' => __('Action_Log_Category_deleted')
            ],

            'remote_foodcoop_added' => [
                'name' => __('Action_Log_Remote_food_coop_added')
            ],
            'remote_foodcoop_changed' => [
                'name' => __('Action_Log_Remote_food_coop_changed')
            ],
            'remote_foodcoop_deleted' => [
                'name' => __('Action_Log_Remote_food_coop_deleted')
            ],

            'slider_added' => [
                'name' => __('Action_Log_Slider_added')
            ],
            'slider_changed' => [
                'name' => __('Action_Log_Slider_changed')
            ],
            'slider_deleted' => [
                'name' => __('Action_Log_Slider_deleted')
            ],

            'tax_added' => [
                'name' => __('Action_Log_Tax_rate_added')
            ],
            'tax_changed' => [
                'name' => __('Action_Log_Tax_rate_changed')
            ],
            'tax_deleted' => [
                'name' => __('Action_Log_Tax_rate_deleted')
            ],

            'customer_registered' => [
                'name' => __('Action_Log_Member_account_created')
            ],
            'customer_profile_changed' => [
                'name' => __('Action_Log_Member_profile_changed')
            ],
            'customer_password_changed' => [
                'name' => __('Action_Log_Member_password_changed')
            ],
            'customer_order_finished' => [
                'name' => __('Action_Log_Member_order_placed')
            ],
            'customer_set_inactive' => [
                'name' => __('Action_Log_Member_set_inactive')
            ],
            'customer_set_active' => [
                'name' => __('Action_Log_Member_set_active')
            ],
            'customer_comment_changed' => [
                'name' => __('Action_Log_Member_comment_changed')
            ],
            'customer_group_changed' => [
                'name' => __('Action_Log_Member_group_changed')
            ],
            'customer_deleted' => [
                'name' => __('Action_Log_Member_deleted')
            ],
            'manufacturer_options_changed' => [
                'name' => __('Action_Log_Manufacturer_settings_changed')
            ],
            'manufacturer_password_changed' => [
                'name' => __('Action_Log_Manufacturer_password_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'manufacturer_added' => [
                'name' => __('Action_Log_Manufacturer_added')
            ],
            'manufacturer_changed' => [
                'name' => __('Action_Log_Manufacturer_changed'),
                'access' => [
                    'manufacturer'
                ]
            ],

            'user_feedback_added' => [
                'name' => __('Action_Log_Feedback_created'),
                'access' => [
                    'manufacturer',
                ],
            ],
            'user_feedback_changed' => [
                'name' => __('Action_Log_Feedback_changed'),
                'access' => [
                    'manufacturer',
                ],
            ],
            'user_feedback_approved' => [
                'name' => __('Action_Log_Feedback_approved'),
                'access' => [
                    'manufacturer',
                ],
            ],
            'user_feedback_deleted' => [
                'name' => __('Action_Log_Feedback_deleted'),
                'access' => [
                    'manufacturer',
                ],
            ],

            'cronjob_changed' => [
                'name' => __('Action_Log_Cronjob_changed'),
            ],
            'cronjob_backup_database' => [
                'name' => __('Action_Log_Cronjob_database_backup_done')
            ],
            'cronjob_send_order_lists' => [
                'name' => __('Action_Log_Cronjob_order_lists_sent')
            ],
            'cronjob_send_invoices' => [
                'name' => __('Action_Log_Cronjob_invoices_sent')
            ],
            'cronjob_send_delivery_notes' => [
                'name' => __('Action_Log_Cronjob_delivery_notes_sent')
            ],
            'cronjob_email_order_reminder' => [
                'name' => __('Action_Log_Cronjob_email_order_reminder_sent')
            ],
            'cronjob_pickup_reminder' => [
                'name' => __('Action_Log_Cronjob_pickup_reminder_sent')
            ],
            'cronjob_check_credit_balance' => [
                'name' => __('Action_Log_Cronjob_check_credit_balance')
            ],
            'superadmin_deploy_successful' => [
                'name' => __('Action_Log_Superadmin_deploy_successful'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'attribute_added' => [
                'name' => __('Action_Log_Attribute_added')
            ],
            'attribute_changed' => [
                'name' => __('Action_Log_Attribute_changed')
            ],
            'attribute_deleted' => [
                'name' => __('Action_Log_Attribute_deleted')
            ],

            'configuration_changed' => [
                'name' => __('Action_Log_Setting_changed')
            ]
        ];
    }

    public function removeCustomerNameFromAllActionLogs($customerName) {
        $query = 'UPDATE '.$this->getTable().' SET text = REPLACE(text, \'' . $customerName . '\', \''.Configure::read('app.htmlHelper')->getDeletedCustomerName().'\')';
        $statement = $this->getConnection()->getDriver()->prepare($query);
        return $statement->execute();
    }

    public function removeCustomerEmailFromAllActionLogs($email) {
        $query = 'UPDATE '.$this->getTable().' SET text = REPLACE(text, \'' . $email . '\', \''.Configure::read('app.htmlHelper')->getDeletedCustomerEmail().'\')';
        $statement = $this->getConnection()->getDriver()->prepare($query);
        return $statement->execute();
    }

    public function getHiddenTypesForManufacturersWithEnabledAnonymization(): array
    {
        $types = [];
        foreach($this->types as $key => $value) {
            if (isset($value['access']) && in_array('manufacturerAnonymizationEnabled', $value['access'])) {
                $types[] = $key;
            }
        }
        return $types;
    }

    public function customSave($type, $customerId, $objectId, $objectType, $text, $time=null)
    {
        $data2save = [
            'type' => $type,
            'customer_id' => $customerId,
            'object_id' => $objectId,
            'object_type' => $objectType,
            'text' => $text,
            'date' => is_null($time) ? DateTime::now() : $time
        ];
        return $this->save($this->newEntity($data2save));
    }

    public function getTypesForDropdown()
    {
        $result = [];
        $identity = Router::getRequest()->getAttribute('identity');
        foreach ($this->types as $type => $value) {
            if ($identity->isManufacturer()) {
                if (isset($value['access']) && in_array('manufacturer', $value['access'])) {
                    if ($identity->getManufacturerAnonymizeCustomers() && in_array('manufacturerAnonymizationEnabled', $value['access'])) {
                        continue;
                    }
                    $result[$type] = $value['name'];
                }
            } else {
                $result[$type] = $value['name'];
            }
        }
        return $result;
    }
}
