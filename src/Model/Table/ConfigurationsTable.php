<?php

namespace App\Model\Table;
use Cake\Core\Configure;

/**
 * Configuration
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
class ConfigurationsTable extends AppTable
{

    public function initialize(array $config)
    {
        $this->setTable('configuration');
        parent::initialize($config);
        $this->setPrimaryKey('id_configuration');
    }

    /**
     * @param string $plugin
     * @throws ConfigFileMissingException
     * @return string (version)
     */
    public function getVersion($plugin = null)
    {
        $versionFile = 'VERSION.txt';
        if ($plugin) {
            $versionFileWithPath = APP . 'Plugin' . DS . $plugin . DS . $versionFile;
        } else {
            $versionFileWithPath = APP . $versionFile;
        }

        if (!file_exists($versionFileWithPath)) {
            throw new ConfigFileMissingException('version file not found: ' . $versionFileWithPath);
        }
        $file = new File($versionFileWithPath);
        $version = $file->read(true, 'r');

        return $version;
    }

    public function enableValidations($name)
    {
        $validationRules = [];

        switch ($name) {
            // booleans
            case 'FCS_CART_ENABLED':
            case 'FCS_SHOW_PRODUCTS_FOR_GUESTS':
            case 'FCS_DEFAULT_NEW_MEMBER_ACTIVE':
            case 'FCS_SHOW_FOODCOOPSHOP_BACKLINK':
            case 'FCS_ORDER_COMMENT_ENABLED':
                $validationRules = $this->getNumberRangeConfigurationRule(0, 1);
                break;
            case 'FCS_PRODUCT_AVAILABILITY_LOW':
                $validationRules = $this->getNumberRangeConfigurationRule(1, 10);
                break;
            case 'FCS_DAYS_SHOW_PRODUCT_AS_NEW':
                $validationRules = $this->getNumberRangeConfigurationRule(1, 14);
                break;
            case 'FCS_PAYMENT_PRODUCT_MAXIMUM':
                $validationRules = $this->getNumberRangeConfigurationRule(50, 1000);
                break;
            case 'FCS_SHOP_ORDER_DEFAULT_STATE':
                $validationRules = $this->getEqualsToMultipleValuesRule(Configure::read('AppConfig.htmlHelper')->getVisibleOrderStates());
                break;
            case 'FCS_CUSTOMER_GROUP':
                $validationRules = $this->getNumberRangeConfigurationRule(CUSTOMER_GROUP_MEMBER, CUSTOMER_GROUP_ADMIN);
                break;
            case 'FCS_FACEBOOK_URL':
                $validationRules = $this->getUrlValidationRule(true);
                break;
            case 'FCS_APP_NAME':
                $validationRules = $this->getCharactersRangeRule(5, 255);
                break;
            case 'FCS_ACCOUNTING_EMAIL':
            case 'FCS_APP_EMAIL':
                $validationRules = $this->getEmailValidationRule();
                break;
            case 'FCS_BACKUP_EMAIL_ADDRESS_BCC':
                $validationRules = $this->getEmailValidationRule(true);
                break;
            case 'FCS_MINIMAL_CREDIT_BALANCE':
                $validationRules = $this->getNumberRangeConfigurationRule(0, 500);
                break;
        }

        $this->validator()['value'] = $validationRules;
    }

    private function getEqualsToMultipleValuesRule($values)
    {
        $validationRules = [];
        $validationRules[] = [
            'rule' => [
                'inList',
                array_keys($values)
            ],
            'message' => 'Folgende Werte sind gültig: ' . implode(', ', array_keys($values))
        ];

        return $validationRules;
    }

    private function getEmailValidationRule($allowEmpty = false)
    {
        $validationRules = [];
        $validationRules[] = [
            'rule' => [
                'email',
                true
            ],
            'message' => 'Bitte gibt eine gültige E-Mail-Adresse an.',
            'allowEmpty' => $allowEmpty
        ];
        return $validationRules;
    }

    private function getUrlValidationRule($allowEmpty = false)
    {
        $validationRules = [];
        $validationRules[] = [
            'rule' => [
                'url',
                true
            ],
            'message' => 'Bitte gibt eine gültige Url an.',
            'allowEmpty' => $allowEmpty
        ];
        return $validationRules;
    }

    private function getCharactersRangeRule($min, $max)
    {
        $validationRules = [];
        $message = 'Die Anzahl der Zeichen muss zwischen ' . $min . ' und ' . $max . ' liegen.';
        $validationRules[] = [
            'rule' => [
                'minLength',
                $min
            ],
            'message' => $message
        ];
        $validationRules[] = [
            'rule' => [
                'maxLength',
                $max
            ],
            'message' => $message
        ];
        return $validationRules;
    }

    public function getConfigurations()
    {
        $configurations = $this->find('all', [
            'fields' => ['id_configuration', 'name', 'value', 'type', 'text'],
            'conditions' => [
                'active' => APP_ON
            ],
            'order' => [
                'position' => 'ASC'
            ]
        ]);
        return $configurations;
    }

    public function loadConfigurations()
    {
        $configurations = $this->getConfigurations();
        foreach ($configurations as $configuration) {
            Configure::write('AppConfigDb.' . $configuration->name, $configuration->value);
        }
    }
}
