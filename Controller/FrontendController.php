<?php

App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

/**
 * FrontendController
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
class FrontendController extends AppController
{

    /**
     * @param CakeRequest $request
     * @param CakeResponse $response
     * @return
     */
    public function __construct($request = null, $response = null)
    {
        $defaultUrl = Configure::read('slugHelper')->getAllProducts();
        $redirectUrl = ! empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $defaultUrl;
        $this->components['AppAuth']['logoutRedirect'] = $redirectUrl;
        return parent::__construct($request, $response);
    }

    public function isAuthorized($user)
    {
        return true;
    }

    /**
     * should be moved into component
     * adds product attributes and deposit
     *
     * @param array $products
     */
    protected function perpareProductsForFrontend($products)
    {
        $this->loadModel('Product');
        $this->loadModel('ProductAttribute');
        $this->ProductAttribute->recursive = 2; // for attribute lang

        foreach ($products as &$product) {

            $grossPrice = $this->Product->getGrossPrice($product['Product']['id_product'], $product['ProductShop']['price']);
            $product['Product']['gross_price'] = $grossPrice;
            $product['Product']['tax'] = $grossPrice - $product['ProductShop']['price'];
            $product['Product']['is_new'] = $this->Product->isNew($product['ProductShop']['date_add']);

            $product['attributes'] = $this->ProductAttribute->find('all', array(
                'conditions' => array(
                    'ProductAttribute.id_product' => $product['Product']['id_product']
                )
            ));
            foreach ($product['attributes'] as &$attribute) {
                $grossPrice = $this->Product->getGrossPrice($attribute['ProductAttributeShop']['id_product'], $attribute['ProductAttributeShop']['price']);
                $attribute['ProductAttributeShop']['gross_price'] = $grossPrice;
                $attribute['ProductAttributeShop']['tax'] = $grossPrice - $attribute['ProductAttributeShop']['price'];
            }
        }

        return $products;
    }

    protected function resetOriginalLoggedCustomer()
    {
        if ($this->AppSession->read('Auth.originalLoggedCustomer')) {
            $this->AppAuth->login($this->AppSession->read('Auth.originalLoggedCustomer'));
        }
    }

    protected function destroyShopOrderCustomer()
    {
        $this->AppSession->delete('Auth.shopOrderCustomer');
        $this->AppSession->delete('Auth.originalLoggedCustomer');
    }

    /*
     * Do the database migrations
     *
     * Database migrations works with a configuration table entry as a numeric
     * version number. The DB version is shown in the admin interface as a
     * read-only setting. There is a complementary "last update try" number
     * added to the configuration table, which is marked inactive and thus
     * hidden from the admin settings interface. Executed DB migrations are
     * logged in the action log, but there is no user stated.
     *
     * If there is no configuration table entry with the DB version, the start
     * of the auto-migrations is assumed and update to version 0 is executed.
     * This update is executed alone, then
     *
     * Initial version is 0, versions are unsigned ints afterwards. Gaps are
     * tolerated, but not recommended. Any SQL file having a numeric-only file
     * name in migrations folder [See getDbMigrationsFolder()] and an 'sql'
     * extension will be used in numeric sequence for updating [see
     * getDbMigrationsVersions()].
     *
     * There is a lot of affort to take to prevent endless looping on update
     * failures. And because DataSource::query() doesn't report errors to the
     * caller, but kills execution instead, there is even more to do. So
     * prevention of endless loops on failing updates is implemented like this:
     * - To prevent updating set a non-numeric DB version in configuration
     * - Any unrecoverable error that does no "Oooops" uses the above
     * - SQL Errors are caught by setting an inactive DB update marker in the
     *   configuration and comparing to the real DB version. On mismatch do set
     *   a non-numeric DB version
     *
     * @return  bool    true -> did DB update, false -> not done, maybe failed
     */
    protected function doDbMigrations()
    {
        $db = Configure::read('app.db_config_FCS_DB_VERSION');
        if (strlen($db) == 0) {  // the DB version config value doesn't exist
            $avail = array('0'); // do the very first DB migration
        }
        else if (!is_numeric($db)) {
            // on a previous fail, do not retry but inform user
            $this->AppSession->setFlashError('DB update error');
            return false;
        }
        else {
            $avail = $this->getDbMigrationsVersions($db);
        }

        if (empty($avail)) {
            return false;
        }

        if ($avail[0] !== '0') {
            $conf = $this->Configuration->find('first', array(
                'conditions' => array(
                    'Configuration.name' => 'FCS_DB_VERSION'
                )
            ));
            $tried = $this->Configuration->find('first', array(
                'conditions' => array(
                    'Configuration.name' => 'FCS_DB_UPDATE'
                )
            ));

            // catch unsuccessful update attempt
            if ($conf['Configuration']['value'] != $tried['Configuration']['value']) {
                $this->logDbMigrationsFailure(
                    $conf['Configuration']['value'],
                    $tried['Configuration']['value']
                );

                // prevent endless looping on unrecoverable error
                $conf['Configuration']['value'] = 'SQL not executed ' . $tried['Configuration']['value'];
                $this->Configuration->save($conf, array('validate' => false));

                // inform user
                $this->AppSession->setFlashError('DB update error');
                return false;
            }
        }
        else {
            $conf = false;
            $tried = false;
        }

        foreach ($avail as $migration) {

            // note the initial version before doing the actual update
            if (is_array($conf)) {
                $from = $conf['Configuration']['value'];
            }
            else {
                $from = '---';
            }

            // note the destination version before doing the actual update
            if (is_array($tried)) {
                $tried['Configuration']['value'] = $migration;
                $this->Configuration->save($tried, array('validate' => false));
            }

            $file = new File(Configure::read('app.folder.migrations') . DS . $migration . '.sql');

            if (!$file->readable()) {
                if (is_array($conf)) {
                    $this->logDbMigrationsFailure(
                        $conf['Configuration']['value'],
                        $migration
                    );

                    // prevent endless looping on unrecoverable error
                    $conf['Configuration']['value'] = 'Cannot Read File ' . $migration;
                    $this->Configuration->save($conf, array('validate' => false));
                }
                else {
                    $this->logDbMigrationsFailure(
                        '---',
                        $migration
                    );
                }

                // inform user
                $this->AppSession->setFlashError('DB update error');
                return false;
            }

            $sql = $file->read();
            $file->close();
            unset($file);

            // Doing schema update as one transaction prevents from partially
            // executed updates. They are rolled back automatically on errors.
            // Adding the DB version update into the transaction allows for
            // execution control as query() doesn't report errors.
            $sql = 'START TRANSACTION;'
                . PHP_EOL
                . $sql
                . PHP_EOL
                . 'UPDATE `fcs_configuration` SET `value` = \''
                . $migration
                . '\' WHERE `fcs_configuration`.`name` = \'FCS_DB_VERSION\';'
                . PHP_EOL
                . 'COMMIT;'
                . PHP_EOL
                ;

            $this->Configuration->query($sql);

            // now try to get the updated version number
            $conf = $this->Configuration->find('first', array(
                'conditions' => array(
                    'Configuration.name' => 'FCS_DB_VERSION'
                )
            ));

            if (is_array($conf)) {
                // catch unsuccessful update attempt
                if ($conf['Configuration']['value'] != $migration) {
                    $this->logDbMigrationsFailure(
                        $conf['Configuration']['value'],
                        $migration
                    );

                    // prevent endless looping on unrecoverable error
                    $conf['Configuration']['value'] = 'SQL not executed ' . $migration;
                    $this->Configuration->save($conf, array('validate' => false));

                    // inform user
                    $this->AppSession->setFlashError('DB update error');
                    return false;
                }
            }
            else {
                $this->logDbMigrationsFailure($from, $migration);

                // inform user
                $this->AppSession->setFlashError('DB update error');
                return false;
            }

            $this->logDbMigrationsSuccess($from, $migration);
        }

        return true;
    }

    /*
     * Get the list of database migrations to execute
     *
     * @return  array   list of existing numeric versions to execute
     */
    protected function getDbMigrationsVersions($activeVersion)
    {
        $result = array();
        $activeVersion = (int)$activeVersion;
        $dir = new Folder(Configure::read('app.folder.migrations'));
        $files = $dir->find('^[0-9]+\.sql$');
        unset($dir);

        foreach ($files as $key => $file) {
            $thisVersion = (int)basename($file, '.sql');
            if ($thisVersion > $activeVersion) {
                $result[] = $thisVersion;
            }
        }
        unset($files, $key, $file, $thisVersion);

        sort($result, SORT_NUMERIC);
        return $result;
    }

    /*
     * Log DB update failure
     */
    protected function logDbMigrationsFailure($activeVersion, $triedVersion)
    {
        $this->loadModel('CakeActionLog');
        $this->CakeActionLog->customSave(
            'superadmin_deploy_failed',
            0,  // user id illegal
            0,
            '',
            sprintf(
                'Die Datenbank konnte nicht von "Version %1$s" aktualisiert werden auf <i>"Version %2$s"</i>',
                $activeVersion,
                $triedVersion
            )
        );
    }

    /*
     * Log DB update success
     */
    protected function logDbMigrationsSuccess($activeVersion, $triedVersion)
    {
        $this->loadModel('CakeActionLog');
        $this->CakeActionLog->customSave(
            'superadmin_deploy_successful',
            0,  // user id illegal
            0,
            '',
            sprintf(
                'Die Datenbank wurde von "Version %1$s" aktualisiert auf <i>"Version %2$s"</i>',
                $activeVersion,
                $triedVersion
            )
        );
    }

    // is not called on ajax actions!
    public function beforeRender()
    {

        parent::beforeRender();

        // when a shop order was placed, the pdfs that are rendered for the order confirmation email
        // called this method and therefore called resetOriginalLoggedCustomer() => email was sent t
        // the user who placed the order for a member and not to the member
        if ($this->response->type() != 'text/html') {
            return;
        }

        $this->resetOriginalLoggedCustomer();

        if ($this->doDbMigrations()) {
            $this->redirect('/');
        }

        $categoriesForMenu = array();
        if (Configure::read('app.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS') || $this->AppAuth->loggedIn()) {
            $this->loadModel('Category');
            $allProductsCount = $this->Category->getProductsByCategoryId(Configure::read('app.categoryAllProducts'), false, '', 0, true);
            $newProductsCount = $this->Category->getProductsByCategoryId(Configure::read('app.categoryAllProducts'), true, '', 0, true);
            $categoriesForMenu = $this->Category->getForMenu();
            array_unshift($categoriesForMenu, array(
                'slug' => '/neue-produkte',
                'name' => 'Neue Produkte (' . $newProductsCount . ')',
                'options' => array(
                    'fa-icon' => 'fa-star'
                )
            ));
            array_unshift($categoriesForMenu, array(
                'slug' => Configure::read('slugHelper')->getAllProducts(),
                'name' => 'Alle Produkte (' . $allProductsCount . ')',
                'options' => array(
                    'fa-icon' => 'fa-tags'
                )
            ));
        }
        $this->set('categoriesForMenu', $categoriesForMenu);

        $this->loadModel('Manufacturer');
        $manufacturersForMenu = $this->Manufacturer->getForMenu($this->AppAuth);
        $this->set('manufacturersForMenu', $manufacturersForMenu);

        $this->loadModel('Page');
        $conditions['Page.active'] = APP_ON;
        $conditions[] = 'Page.position > 0';
        if (! $this->AppAuth->loggedIn()) {
            $conditions['Page.is_private'] = APP_OFF;
        }

        $pages = $this->Page->findAllGroupedByMenu($conditions);
        $pagesForHeader = array();
        $pagesForFooter = array();
        foreach ($pages as $page) {
            if ($page['Page']['menu_type'] == 'header') {
                $pagesForHeader[] = $page;
            }
            if ($page['Page']['menu_type'] == 'footer') {
                $pagesForFooter[] = $page;
            }
        }
        $this->set('pagesForHeader', $pagesForHeader);
        $this->set('pagesForFooter', $pagesForFooter);
    }

    public function beforeFilter()
    {
        parent::beforeFilter();

        if (($this->name == 'Categories' && $this->action == 'detail') || $this->name == 'Carts') {
            // do not allow but call isAuthorized
        } else {
            $this->AppAuth->allow();
        }

        /*
         * changed the acutally logged in customer to the desired shopOrderCustomer
         * but only in controller beforeFilter(), beforeRender() sets the customer back to the original one
         * this means, in views $appAuth ALWAYS returns the original customer, in controllers ALWAYS the desired shopOrderCustomer
         */
        if ($this->AppSession->read('Auth.shopOrderCustomer')) {
            $this->AppSession->write('Auth.originalLoggedCustomer', $this->AppAuth->user());
            $this->AppAuth->login($this->AppSession->read('Auth.shopOrderCustomer')['Customer']);
        }

        if ($this->AppAuth->loggedIn() && Configure::read('htmlHelper')->paymentIsCashless()) {

            $creditBalance = $this->AppAuth->getCreditBalance();
            $this->set('creditBalance', $creditBalance);

            $shoppingLimitReached = Configure::read('app.db_config_FCS_MINIMAL_CREDIT_BALANCE') != - 1 && $creditBalance < Configure::read('app.db_config_FCS_MINIMAL_CREDIT_BALANCE') * - 1;
            $this->set('shoppingLimitReached', $shoppingLimitReached);
        }

        $this->AppAuth->setCakeCart($this->AppAuth->getCakeCart());
    }
}

?>