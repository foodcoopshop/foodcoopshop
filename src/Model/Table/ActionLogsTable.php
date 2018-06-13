<?php

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\I18n\Time;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ActionLogsTable extends AppTable
{
    /*
     * Supported structure:
     * {type_id} => array(
     *     'name' => 'text to show in German language',
     *     'access' => array(
     *         'manufacturer', // uncertain how that works
     *     ),
     *     'class' => array(  // classification of log entry
     *         'info',  // for info only, default
     *         'warn',  // warning, something should be taken care of
     *         'fail',  // failure while doing something, must be taken care of
     *     ),
     * ),
     */
    public $types;
    
    
    public function initialize(array $config)
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
            'product_added' => [
                'name' => __('Action_Log_Product_created'),
                'access' => [
                    'manufacturer'
                ]
            ],
            'product_attribute_added' => [
                'name' => __('Action_Log_Product_attribute_created'),
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
            'orders_shop_added' => [
                'name' => __('Action_Log_Order_shop_order_placed')
            ],
            'order_detail_product_price_changed' => [
                'name' => 'Bestelltes Produkt: Preis geändert',
                'access' => [
                    'manufacturer'
                ]
            ],
            'order_detail_product_quantity_changed' => [
                'name' => 'Bestelltes Produkt: Gewicht geändert',
                'access' => [
                    'manufacturer'
                ]
            ],
            'order_detail_product_amount_changed' => [
                'name' => 'Bestelltes Produkt: Anzahl geändert',
                'access' => [
                    'manufacturer'
                ]
            ],
            'order_detail_cancelled' => [
                'name' => 'Bestelltes Produkt: storniert',
                'access' => [
                    'manufacturer'
                ]
            ],
    
            'payment_product_added' => [
                'name' => 'Mitglied: Guthaben-Aufladung eingetragen'
            ],
            'payment_product_approval_ok' => [
                'name' => 'Guthaben-Aufladung: bestätigt'
            ],
            'payment_product_approval_open' => [
                'name' => 'Guthaben-Aufladung: Bestätigung offen'
            ],
            'payment_product_approval_not_ok' => [
                'name' => 'Guthaben-Aufladung: da stimmt was nicht...'
            ],
            'payment_payback_added' => [
                'name' => 'Mitglied: Rückzahlung eingetragen'
            ],
            'payment_payback_deleted' => [
                'name' => 'Mitglied: Rückzahlung gelöscht'
            ],
            'payment_product_deleted' => [
                'name' => 'Mitglied: Guthaben gelöscht'
            ],
            'payment_deposit_customer_added' => [
                'name' => 'Mitglied: Pfand-Betrag eingetragen'
            ],
            'payment_deposit_manufacturer_added' => [
                'name' => 'Hersteller: Pfand-Rücknahme eingetragen',
                'access' => [
                    'manufacturer'
                ]
            ],
            'payment_deposit_customer_deleted' => [
                'name' => 'Mitglied: Pfand-Betrag gelöscht'
            ],
            'payment_deposit_manufacturer_deleted' => [
                'name' => 'Hersteller: Pfand-Rücknahme gelöscht',
                'access' => [
                    'manufacturer'
                ]
            ],
            'payment_member_fee_added' => [
                'name' => 'Mitglied: Mitgliedsbeitrag eingetragen'
            ],
            'payment_member_fee_deleted' => [
                'name' => 'Mitglied: Mitgliedsbeitrag gelöscht'
            ],
            'payment_member_fee_flexible_added' => [
                'name' => 'Mitglied: Flexibler Mitgliedsbeitrag eingetragen (Funktion nicht mehr vorhanden)'
            ],
            'payment_member_fee_flexible_deleted' => [
                'name' => 'Mitglied: Flexibler Mitgliedsbeitrag gelöscht (Funktion nicht mehr vorhanden)'
            ],
    
            'blog_post_added' => [
                'name' => 'Blog-Artikel: erstellt'
            ],
            'blog_post_changed' => [
                'name' => 'Blog-Artikel: geändert',
                'access' => [
                    'manufacturer'
                ]
            ],
            'blog_post_deleted' => [
                'name' => 'Blog-Artikel: gelöscht',
                'access' => [
                    'manufacturer'
                ]
            ],
    
            'page_added' => [
                'name' => 'Seite: erstellt'
            ],
            'page_changed' => [
                'name' => 'Seite: geändert'
            ],
            'page_deleted' => [
                'name' => 'Seite: gelöscht'
            ],
    
            'category_added' => [
                'name' => 'Kategorie: erstellt'
            ],
            'category_changed' => [
                'name' => 'Kategorie: geändert'
            ],
            'category_deleted' => [
                'name' => 'Kategorie: gelöscht'
            ],
    
            'remote_foodcoop_added' => [
                'name' => 'Remote-Foodcoop: erstellt'
            ],
            'remote_foodcoop_changed' => [
                'name' => 'Remote-Foodcoop: geändert'
            ],
            'remote_foodcoop_deleted' => [
                'name' => 'Remote-Foodcoop: gelöscht'
            ],
    
            'slider_added' => [
                'name' => 'Slideshow-Bild: erstellt'
            ],
            'slider_changed' => [
                'name' => 'Slideshow-Bild: geändert'
            ],
            'slider_deleted' => [
                'name' => 'Slideshow-Bild: gelöscht'
            ],
    
            'tax_added' => [
                'name' => 'Steuersatz: erstellt'
            ],
            'tax_changed' => [
                'name' => 'Steuersatz: geändert'
            ],
            'tax_deleted' => [
                'name' => 'Steuersatz: gelöscht'
            ],
    
            'customer_registered' => [
                'name' => 'Mitglied: Mitgliedskonto erstellt'
            ],
            'customer_profile_changed' => [
                'name' => 'Mitglied: Profil geändert'
            ],
            'customer_password_changed' => [
                'name' => 'Mitglied: Passwort geändert'
            ],
            'customer_order_finished' => [
                'name' => 'Mitglied: Bestellung getätigt'
            ],
            'customer_set_inactive' => [
                'name' => 'Mitglied: deaktiviert'
            ],
            'customer_set_active' => [
                'name' => 'Mitglied: aktiviert'
            ],
            'customer_comment_changed' => [
                'name' => 'Mitglied: Kommentar geändert'
            ],
            'customer_group_changed' => [
                'name' => 'Mitglied: Gruppe geändert'
            ],
            'customer_deleted' => [
                'name' => 'Mitglied: gelöscht'
            ],
            
            'manufacturer_description_changed' => [
                'name' => 'Hersteller: Beschreibung geändert',
                'access' => [
                    'manufacturer'
                ]
            ],
            'manufacturer_options_changed' => [
                'name' => 'Hersteller: Einstellungen geändert'
            ],
            'manufacturer_additional_text_for_invoice_changed' => [
                'name' => 'Hersteller: Zusatztext für Rechnung geändert',
                'access' => [
                    'manufacturer'
                ]
            ],
            'manufacturer_password_changed' => [
                'name' => 'Hersteller: Passwort geändert',
                'access' => [
                    'manufacturer'
                ]
            ],
            'manufacturer_set_inactive' => [
                'name' => 'Hersteller: deaktiviert',
                'access' => [
                    'manufacturer'
                ]
            ],
            'manufacturer_set_active' => [
                'name' => 'Hersteller: aktiviert',
                'access' => [
                    'manufacturer'
                ]
            ],
            'manufacturer_added' => [
                'name' => 'Hersteller: erstellt'
            ],
            'manufacturer_changed' => [
                'name' => 'Hersteller: geändert',
                'access' => [
                    'manufacturer'
                ]
            ],
    
            'timebased_currency_payment_added' => [
                'name' => 'Stundenabrechnung: Zeit-Eintragung erstellt'
            ],
            'timebased_currency_payment_changed' => [
                'name' => 'Stundenabrechnung: Zeit-Eintragung geändert'
            ],
            'timebased_currency_payment_deleted' => [
                'name' => 'Stundenabrechnung: Zeit-Eintragung gelöscht'
            ],
            
            'cronjob_backup_database' => [
                'name' => 'Cronjob: Datenbank gebackupt'
            ],
            'cronjob_send_order_lists' => [
                'name' => 'Cronjob: Bestelllisten verschickt'
            ],
            'cronjob_send_invoices' => [
                'name' => 'Cronjob: Rechnungen verschickt'
            ],
            'cronjob_sms_reminder' => [
                'name' => 'Cronjob: SMS-Bestellerinnerung'
            ],
            'cronjob_email_order_reminder' => [
                'name' => 'Cronjob: E-Mail-Bestellerinnerung'
            ],
            'cronjob_check_credit_balance' => [
                'name' => 'Cronjob: Guthaben überprüfen'
            ],
            'superadmin_deploy_successful' => [
                'name' => 'Superadmin: Update eingespielt',
                'access' => [
                    'manufacturer'
                ]
            ],
            'superadmin_deploy_failed' => [
                'name' => 'Superadmin: Update gescheitert',
                'access' => [
                    'manufacturer'
                ],
                'class' => [
                    'fail',
                ],
            ],
    
            'attribute_added' => [
                'name' => 'Variante erstellt'
            ],
            'attribute_changed' => [
                'name' => 'Variante geändert'
            ],
            'attribute_deleted' => [
                'name' => 'Variante gelöscht'
            ],
    
            'configuration_changed' => [
                'name' => 'Einstellung geändert'
            ]
        ];
    }
    
    public function removeCustomerNameFromAllActionLogs($customerName) {
        $query = 'UPDATE '.$this->getTable().' SET text = REPLACE(text, \'' . $customerName . '\', \''.Configure::read('app.htmlHelper')->getDeletedCustomerName().'\')';
        $statement = $this->getConnection()->prepare($query);
        return $statement->execute();
    }
    public function removeCustomerEmailFromAllActionLogs($email) {
        $query = 'UPDATE '.$this->getTable().' SET text = REPLACE(text, \'' . $email . '\', \''.Configure::read('app.htmlHelper')->getDeletedCustomerEmail().'\')';
        $statement = $this->getConnection()->prepare($query);
        return $statement->execute();
    }
    
    public function customSave($type, $customerId, $objectId, $objectType, $text)
    {
        $data2save = [
            'type' => $type,
            'customer_id' => $customerId,
            'object_id' => $objectId,
            'object_type' => $objectType,
            'text' => $text,
            'date' => Time::now()
        ];
        $this->save($this->newEntity($data2save));
    }

    public function getTypesForDropdown($appAuth)
    {
        $result = [];
        foreach ($this->types as $type => $value) {
            if ($appAuth->isManufacturer()) {
                if (isset($value['access']) && in_array('manufacturer', $value['access'])) {
                    $result[$type] = $value['name'];
                }
            } else {
                $result[$type] = $value['name'];
            }
        }
        return $result;
    }
}
