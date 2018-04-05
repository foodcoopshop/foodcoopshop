<?php

namespace App\Model\Table;

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
     *     'de' => 'text to show in German language',
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
    public $types = [
        'product_added' => [
            'de' => 'Produkt: erstellt',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_attribute_added' => [
            'de' => 'Produkt: Variante erstellt',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_image_added' => [
            'de' => 'Produkt: Bild hochgeladen',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_image_deleted' => [
            'de' => 'Produkt: Bild gelöscht',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_attribute_deleted' => [
            'de' => 'Produkt: Variante gelöscht',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_set_inactive' => [
            'de' => 'Produkt: deaktiviert',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_set_active' => [
            'de' => 'Produkt: aktiviert',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_quantity_changed' => [
            'de' => 'Produkt: Anzahl geändert',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_price_changed' => [
            'de' => 'Produkt: Preis geändert',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_name_changed' => [
            'de' => 'Produkt: Name geändert',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_unity_changed' => [
            'de' => 'Produkt: Einheit geändert',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_description_changed' => [
            'de' => 'Produkt: Beschreibung geändert',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_description_short_changed' => [
            'de' => 'Produkt: Beschreibung kurz geändert',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_deposit_changed' => [
            'de' => 'Produkt: Pfand geändert',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_tax_changed' => [
            'de' => 'Produkt: Steuersatz geändert',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_categories_changed' => [
            'de' => 'Produkt: Kategorien geändert',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_set_to_old' => [
            'de' => 'Produkt: nicht mehr als "neu" anzeigen',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_set_to_new' => [
            'de' => 'Produkt: als "neu" angezeigen',
            'access' => [
                'manufacturer'
            ]
        ],
        'product_default_attribute_changed' => [
            'de' => 'Produkt: Standard-Variante geändert',
            'access' => [
                'manufacturer'
            ]
        ],

        'product_remotely_changed' => [
            'de' => 'Netzwerk-Modul: Produkte synchronisiert',
            'access' => [
                'manufacturer'
            ]
        ],

        'orders_state_changed' => [
            'de' => 'Bestellung: Status geändert'
        ],
        'orders_closed' => [
            'de' => 'Bestellungen: abgeschlossen'
        ],
        'order_comment_changed' => [
            'de' => 'Bestellung: Kommentar geändert'
        ],
        'orders_date_changed' => [
            'de' => 'Bestellung: rückdatiert'
        ],
        'orders_shop_added' => [
            'de' => 'Bestellung: Sofort-Bestellung getätigt'
        ],
        'order_detail_product_price_changed' => [
            'de' => 'Bestelltes Produkt: Preis geändert',
            'access' => [
                'manufacturer'
            ]
        ],
        'order_detail_product_quantity_changed' => [
            'de' => 'Bestelltes Produkt: Anzahl geändert',
            'access' => [
                'manufacturer'
            ]
        ],
        'order_detail_cancelled' => [
            'de' => 'Bestelltes Produkt: storniert',
            'access' => [
                'manufacturer'
            ]
        ],

        'payment_product_added' => [
            'de' => 'Mitglied: Guthaben-Aufladung eingetragen'
        ],
        'payment_product_approval_ok' => [
            'de' => 'Guthaben-Aufladung: bestätigt'
        ],
        'payment_product_approval_open' => [
            'de' => 'Guthaben-Aufladung: Bestätigung offen'
        ],
        'payment_product_approval_not_ok' => [
            'de' => 'Guthaben-Aufladung: da stimmt was nicht...'
        ],
        'payment_payback_added' => [
            'de' => 'Mitglied: Rückzahlung eingetragen'
        ],
        'payment_payback_deleted' => [
            'de' => 'Mitglied: Rückzahlung gelöscht'
        ],
        'payment_product_deleted' => [
            'de' => 'Mitglied: Guthaben gelöscht'
        ],
        'payment_deposit_customer_added' => [
            'de' => 'Mitglied: Pfand-Betrag eingetragen'
        ],
        'payment_deposit_manufacturer_added' => [
            'de' => 'Hersteller: Pfand-Rücknahme eingetragen',
            'access' => [
                'manufacturer'
            ]
        ],
        'payment_deposit_customer_deleted' => [
            'de' => 'Mitglied: Pfand-Betrag gelöscht'
        ],
        'payment_deposit_manufacturer_deleted' => [
            'de' => 'Hersteller: Pfand-Rücknahme gelöscht',
            'access' => [
                'manufacturer'
            ]
        ],
        'payment_member_fee_added' => [
            'de' => 'Mitglied: Mitgliedsbeitrag eingetragen'
        ],
        'payment_member_fee_deleted' => [
            'de' => 'Mitglied: Mitgliedsbeitrag gelöscht'
        ],
        'payment_member_fee_flexible_added' => [
            'de' => 'Mitglied: Flexibler Mitgliedsbeitrag eingetragen (Funktion nicht mehr vorhanden)'
        ],
        'payment_member_fee_flexible_deleted' => [
            'de' => 'Mitglied: Flexibler Mitgliedsbeitrag gelöscht (Funktion nicht mehr vorhanden)'
        ],

        'blog_post_added' => [
            'de' => 'Blog-Artikel: erstellt'
        ],
        'blog_post_changed' => [
            'de' => 'Blog-Artikel: geändert',
            'access' => [
                'manufacturer'
            ]
        ],
        'blog_post_deleted' => [
            'de' => 'Blog-Artikel: gelöscht',
            'access' => [
                'manufacturer'
            ]
        ],

        'page_added' => [
            'de' => 'Seite: erstellt'
        ],
        'page_changed' => [
            'de' => 'Seite: geändert'
        ],
        'page_deleted' => [
            'de' => 'Seite: gelöscht'
        ],

        'category_added' => [
            'de' => 'Kategorie: erstellt'
        ],
        'category_changed' => [
            'de' => 'Kategorie: geändert'
        ],
        'category_deleted' => [
            'de' => 'Kategorie: gelöscht'
        ],

        'remote_foodcoop_added' => [
            'de' => 'Remote-Foodcoop: erstellt'
        ],
        'remote_foodcoop_changed' => [
            'de' => 'Remote-Foodcoop: geändert'
        ],
        'remote_foodcoop_deleted' => [
            'de' => 'Remote-Foodcoop: gelöscht'
        ],

        'slider_added' => [
            'de' => 'Slideshow-Bild: erstellt'
        ],
        'slider_changed' => [
            'de' => 'Slideshow-Bild: geändert'
        ],
        'slider_deleted' => [
            'de' => 'Slideshow-Bild: gelöscht'
        ],

        'tax_added' => [
            'de' => 'Steuersatz: erstellt'
        ],
        'tax_changed' => [
            'de' => 'Steuersatz: geändert'
        ],
        'tax_deleted' => [
            'de' => 'Steuersatz: gelöscht'
        ],

        'customer_registered' => [
            'de' => 'Mitglied: Mitgliedskonto erstellt'
        ],
        'customer_profile_changed' => [
            'de' => 'Mitglied: Profil geändert'
        ],
        'customer_password_changed' => [
            'de' => 'Mitglied: Passwort geändert'
        ],
        'customer_order_finished' => [
            'de' => 'Mitglied: Bestellung getätigt'
        ],
        'customer_set_inactive' => [
            'de' => 'Mitglied: deaktiviert'
        ],
        'customer_set_active' => [
            'de' => 'Mitglied: aktiviert'
        ],
        'customer_comment_changed' => [
            'de' => 'Mitglied: Kommentar geändert'
        ],
        'customer_group_changed' => [
            'de' => 'Mitglied: Gruppe geändert'
        ],

        'manufacturer_description_changed' => [
            'de' => 'Hersteller: Beschreibung geändert',
            'access' => [
                'manufacturer'
            ]
        ],
        'manufacturer_options_changed' => [
            'de' => 'Hersteller: Einstellungen geändert'
        ],
        'manufacturer_additional_text_for_invoice_changed' => [
            'de' => 'Hersteller: Zusatztext für Rechnung geändert',
            'access' => [
                'manufacturer'
            ]
        ],
        'manufacturer_password_changed' => [
            'de' => 'Hersteller: Passwort geändert',
            'access' => [
                'manufacturer'
            ]
        ],
        'manufacturer_set_inactive' => [
            'de' => 'Hersteller: deaktiviert',
            'access' => [
                'manufacturer'
            ]
        ],
        'manufacturer_set_active' => [
            'de' => 'Hersteller: aktiviert',
            'access' => [
                'manufacturer'
            ]
        ],
        'manufacturer_added' => [
            'de' => 'Hersteller: erstellt'
        ],
        'manufacturer_changed' => [
            'de' => 'Hersteller: geändert',
            'access' => [
                'manufacturer'
            ]
        ],

        'timebased_currency_payment_added' => [
            'de' => 'Zeitwährung: Zeit-Eintragung erstellt'
        ],
        'timebased_currency_payment_deleted' => [
            'de' => 'Zeitwährung: Zeit-Eintragung gelöscht'
        ],
        
        'cronjob_backup_database' => [
            'de' => 'Cronjob: Datenbank gebackupt'
        ],
        'cronjob_send_order_lists' => [
            'de' => 'Cronjob: Bestelllisten verschickt'
        ],
        'cronjob_send_invoices' => [
            'de' => 'Cronjob: Rechnungen verschickt'
        ],
        'cronjob_sms_reminder' => [
            'de' => 'Cronjob: SMS-Bestellerinnerung'
        ],
        'cronjob_email_order_reminder' => [
            'de' => 'Cronjob: E-Mail-Bestellerinnerung'
        ],
        'cronjob_check_credit_balance' => [
            'de' => 'Cronjob: Guthaben überprüfen'
        ],
        'superadmin_deploy_successful' => [
            'de' => 'Superadmin: Update eingespielt',
            'access' => [
                'manufacturer'
            ]
        ],
        'superadmin_deploy_failed' => [
            'de' => 'Superadmin: Update gescheitert',
            'access' => [
                'manufacturer'
            ],
            'class' => [
                'fail',
            ],
        ],

        'attribute_added' => [
            'de' => 'Variante erstellt'
        ],
        'attribute_changed' => [
            'de' => 'Variante geändert'
        ],
        'attribute_deleted' => [
            'de' => 'Variante gelöscht'
        ],

        'configuration_changed' => [
            'de' => 'Einstellung geändert'
        ]
    ];

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
                    $result[$type] = $value['de'];
                }
            } else {
                $result[$type] = $value['de'];
            }
        }
        return $result;
    }
}
