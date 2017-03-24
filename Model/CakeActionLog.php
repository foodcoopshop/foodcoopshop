<?php
/**
 * CakeActionLog
 *
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
class CakeActionLog extends AppModel
{
    /*
     * Supported structure:
     * {type_id} => array(
     *     'de' => 'text to show in German language',
     *     'access' => array(
     *         'manufacturer',  // uncertain how that works
     *     ),
     *     'class' => array(  // classification of log entry
     *         'info',  // for info only, default
     *         'warn',  // warning, something should be taken care of
     *         'fail',  // failure while doing something, must be taken care of
     *     ),
     * ),
     */
    public $types = array(
        'product_added' => array(
            'de' => 'Artikel: erstellt',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_attribute_added' => array(
            'de' => 'Artikel: Variante erstellt',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_image_added' => array(
            'de' => 'Artikel: Bild hochgeladen',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_image_deleted' => array(
            'de' => 'Artikel: Bild gelöscht',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_attribute_deleted' => array(
            'de' => 'Artikel: Variante gelöscht',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_set_inactive' => array(
            'de' => 'Artikel: deaktiviert',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_set_active' => array(
            'de' => 'Artikel: aktiviert',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_quantity_changed' => array(
            'de' => 'Artikel: Anzahl geändert',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_price_changed' => array(
            'de' => 'Artikel: Preis geändert',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_name_changed' => array(
            'de' => 'Artikel: Name geändert',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_unity_changed' => array(
            'de' => 'Artikel: Einheit geändert',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_description_changed' => array(
            'de' => 'Artikel: Beschreibung geändert',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_description_short_changed' => array(
            'de' => 'Artikel: Beschreibung kurz geändert',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_deposit_changed' => array(
            'de' => 'Artikel: Pfand geändert',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_tax_changed' => array(
            'de' => 'Artikel: Steuersatz geändert',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_categories_changed' => array(
            'de' => 'Artikel: Kategorien geändert',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_set_to_old' => array(
            'de' => 'Artikel: nicht mehr als "neu" anzeigen',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_set_to_new' => array(
            'de' => 'Artikel: als "neu" angezeigen',
            'access' => array(
                'manufacturer'
            )
        ),
        'product_default_attribute_changed' => array(
            'de' => 'Artikel: Standard-Variante geändert',
            'access' => array(
                'manufacturer'
            )
        ),
        'orders_state_changed' => array(
            'de' => 'Bestellung: Status geändert'
        ),
        'orders_closed' => array(
            'de' => 'Bestellungen: abgeschlossen'
        ),
        'orders_date_changed' => array(
            'de' => 'Bestellung: rückdatiert'
        ),
        'orders_shop_added' => array(
            'de' => 'Bestellung: Sofort-Bestellung getätigt'
        ),
        'order_detail_product_price_changed' => array(
            'de' => 'Bestellter Artikel: Preis geändert',
            'access' => array(
                'manufacturer'
            )
        ),
        'order_detail_product_quantity_changed' => array(
            'de' => 'Bestellter Artikel: Anzahl geändert',
            'access' => array(
                'manufacturer'
            )
        ),
        'order_detail_cancelled' => array(
            'de' => 'Bestellter Artikel: storniert',
            'access' => array(
                'manufacturer'
            )
        ),

        'payment_product_added' => array(
            'de' => 'Mitglied: Guthaben-Aufladung eingetragen'
        ),
        'payment_product_approval_ok' => array(
            'de' => 'Guthaben-Aufladung: bestätigt'
        ),
        'payment_product_approval_open' => array(
            'de' => 'Guthaben-Aufladung: Bestätigung offen'
        ),
        'payment_product_approval_not_ok' => array(
            'de' => 'Guthaben-Aufladung: da stimmt was nicht...'
        ),
        'payment_payback_added' => array(
            'de' => 'Mitglied: Rückzahlung eingetragen'
        ),
        'payment_payback_deleted' => array(
            'de' => 'Mitglied: Rückzahlung gelöscht'
        ),
        'payment_product_deleted' => array(
        	'de' => 'Mitglied: Guthaben gelöscht'
        ),
        'payment_deposit_customer_added' => array(
            'de' => 'Mitglied: Pfand-Betrag eingetragen'
        ),
        'payment_deposit_manufacturer_added' => array(
            'de' => 'Hersteller: Pfand-Rücknahme eingetragen',
            'access' => array(
                'manufacturer'
            )
        ),
        'payment_deposit_customer_deleted' => array(
            'de' => 'Mitglied: Pfand-Betrag gelöscht'
        ),
        'payment_deposit_manufacturer_deleted' => array(
            'de' => 'Hersteller: Pfand-Rücknahme gelöscht',
            'access' => array(
                'manufacturer'
            )
        ),
        'payment_member_fee_added' => array(
            'de' => 'Mitglied: Mitgliedsbeitrag eingetragen'
        ),
        'payment_member_fee_deleted' => array(
            'de' => 'Mitglied: Mitgliedsbeitrag gelöscht'
        ),
        'payment_member_fee_flexible_added' => array(
            'de' => 'Mitglied: Flexibler Mitgliedsbeitrag eingetragen'
        ),
        'payment_member_fee_flexible_deleted' => array(
            'de' => 'Mitglied: Flexibler Mitgliedsbeitrag gelöscht'
        ),

        'blog_post_added' => array(
            'de' => 'Blog-Artikel: erstellt'
        ),
        'blog_post_changed' => array(
            'de' => 'Blog-Artikel: geändert',
            'access' => array(
                'manufacturer'
            )
        ),
        'blog_post_deleted' => array(
            'de' => 'Blog-Artikel: gelöscht',
            'access' => array(
                'manufacturer'
            )
        ),

        'page_added' => array(
            'de' => 'Seite: erstellt'
        ),
        'page_changed' => array(
            'de' => 'Seite: geändert'
        ),
        'page_deleted' => array(
            'de' => 'Seite: gelöscht'
        ),

        'category_added' => array(
            'de' => 'Kategorie: erstellt'
        ),
        'category_changed' => array(
            'de' => 'Kategorie: geändert'
        ),
        'category_deleted' => array(
            'de' => 'Kategorie: gelöscht'
        ),

        'slider_added' => array(
            'de' => 'Slideshow-Bild: erstellt'
        ),
        'slider_changed' => array(
            'de' => 'Slideshow-Bild: geändert'
        ),
        'slider_deleted' => array(
            'de' => 'Slideshow-Bild: gelöscht'
        ),

        'tax_added' => array(
            'de' => 'Steuersatz: erstellt'
        ),
        'tax_changed' => array(
            'de' => 'Steuersatz: geändert'
        ),
        'tax_deleted' => array(
            'de' => 'Steuersatz: gelöscht'
        ),

        'customer_registered' => array(
            'de' => 'Mitglied: Mitgliedskonto erstellt'
        ),
        'customer_profile_changed' => array(
            'de' => 'Mitglied: Profil geändert'
        ),
        'customer_password_changed' => array(
            'de' => 'Mitglied: Passwort geändert'
        ),
        'customer_order_finished' => array(
            'de' => 'Mitglied: Bestellung getätigt'
        ),
        'customer_set_inactive' => array(
            'de' => 'Mitglied: deaktiviert'
        ),
        'customer_set_active' => array(
            'de' => 'Mitglied: aktiviert'
        ),
        'customer_comment_changed' => array(
            'de' => 'Mitglied: Kommentar geändert'
        ),
        'customer_group_changed' => array(
            'de' => 'Mitglied: Gruppe geändert'
        ),

        'manufacturer_description_changed' => array(
            'de' => 'Hersteller: Beschreibung geändert',
            'access' => array(
                'manufacturer'
            )
        ),
        'manufacturer_options_changed' => array(
            'de' => 'Hersteller: Einstellungen geändert'
        ),
        'manufacturer_additional_text_for_invoice_changed' => array(
            'de' => 'Hersteller: Zusatztext für Rechnung geändert',
            'access' => array(
                'manufacturer'
            )
        ),
        'manufacturer_password_changed' => array(
            'de' => 'Hersteller: Passwort geändert',
            'access' => array(
                'manufacturer'
            )
        ),
        'manufacturer_set_inactive' => array(
            'de' => 'Hersteller: deaktiviert',
            'access' => array(
                'manufacturer'
            )
        ),
        'manufacturer_set_active' => array(
            'de' => 'Hersteller: aktiviert',
            'access' => array(
                'manufacturer'
            )
        ),
        'manufacturer_added' => array(
            'de' => 'Hersteller: erstellt'
        ),
        'manufacturer_changed' => array(
            'de' => 'Hersteller: geändert',
            'access' => array(
                'manufacturer'
            )
        ),

        'cronjob_backup_database' => array(
            'de' => 'Cronjob: Datenbank gebackupt'
        ),
        'cronjob_send_order_lists' => array(
            'de' => 'Cronjob: Bestelllisten verschickt'
        ),
        'cronjob_send_invoices' => array(
            'de' => 'Cronjob: Rechnungen verschickt'
        ),
        'cronjob_sms_reminder' => array(
            'de' => 'Cronjob: SMS-Bestellerinnerung'
        ),
        'cronjob_email_order_reminder' => array(
            'de' => 'Cronjob: E-Mail-Bestellerinnerung'
        ),
        'cronjob_check_credit_balance' => array(
            'de' => 'Cronjob: Guthaben überprüfen'
        ),
        'superadmin_deploy_successful' => array(
            'de' => 'Superadmin: Update eingespielt',
            'access' => array(
                'manufacturer'
            )
        ),
        'superadmin_deploy_failed' => array(
            'de' => 'Superadmin: Update gescheitert',
            'access' => array(
                'manufacturer'
            ),
            'class' => array(
                'fail',
            ),
        ),

        'attribute_added' => array(
            'de' => 'Variante erstellt'
        ),
        'attribute_changed' => array(
            'de' => 'Variante geändert'
        ),
        'attribute_deleted' => array(
            'de' => 'Variante gelöscht'
        ),

        'configuration_changed' => array(
            'de' => 'Einstellung geändert'
        )
    )
    ;

    public $belongsTo = array(
        'Customer' => array(
            'foreignKey' => 'customer_id'
        ),
        'Product' => array(
            'foreignKey' => 'object_id',
            'conditions' => array(
                'object_type' => 'products'
            )
        ),
        'Manufacturer' => array(
            'foreignKey' => 'object_id',
            'conditions' => array(
                'object_type' => 'manufacturers'
            )
        ),
        'BlogPost' => array(
            'foreignKey' => 'object_id',
            'conditions' => array(
                'object_type' => 'blog_posts'
            )
        ),
        'CakePayment' => array(
            'foreignKey' => 'object_id',
            'conditions' => array(
                'object_type' => 'payments'
            )
        )
    );

    public function customSave($type, $customerId, $objectId, $objectType, $text)
    {
        $this->id = null;
        $data2save = array(
            'type' => $type,
            'customer_id' => $customerId,
            'object_id' => $objectId,
            'object_type' => $objectType,
            'text' => $text,
            'date' => date('Y-m-d H:i:s')
        );
        $this->save($data2save);
    }

    public function getTypesForDropdown($appAuth)
    {
        $result = array();
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

?>