<?php

App::uses('StringComponent', 'Controller/Component');

/**
 * SlugHelper
 * 
 * TODO use cake's routing
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
class SlugHelper extends Helper {
    
    public function getManufacturerDetail($manufacturerId, $manufacturerName) {
        return '/hersteller/'.$manufacturerId.'-'.StringComponent::slugify($manufacturerName);
    }
    
    public function getManufacturerBlogList($manufacturerId, $manufacturerName) {
        return $this->getManufacturerDetail($manufacturerId, $manufacturerName) . '/aktuelles';
    }
    
    public function getPageDetail($pageId, $name) {
        return '/content/'.$pageId.'-'.StringComponent::slugify($name);
    }
    
    public function getManufacturerList() {
        return '/hersteller';
    }
    
    public function getCartDetail() {
        return '/warenkorb/anzeigen';
    }
    
    public function getCartFinish() {
        return '/warenkorb/abschliessen';
    }
    
    public function getCartFinished($orderId) {
        return '/warenkorb/abgeschlossen/' . $orderId;
    }
    
    public function getHome() {
        return '/';
    }
    
    public function getAllProducts() {
        return $this->getCategoryDetail(Configure::read('app.categoryAllProducts'), 'alle-produkte');
    }
    
    public function getCategoryDetail($categoryId, $name) {
        return '/kategorie/' . $categoryId . '-' . StringComponent::slugify($name);
    }
    
    public function getLogin() {
        return '/anmelden';
    }
    
    public function getLogout() {
        return '/logout';
    }
    
    public function getCreditBalance() {
        return '/admin/payments/product';
    }
    
    public function getChangePassword() {
        return '/admin/customers/changePassword';
    }

    public function getCustomerProfile() {
        return '/admin/customers/profile';
    }
    
    public function getManufacturerProfile() {
        return '/admin/manufacturers/profile';
    }
    
    public function getNewPasswordRequest() {
        return '/neues-passwort-anfordern';
    }
    
    public function getReport($paymentType) {
        return '/admin/reports/payments/'.$paymentType;
    }
    
    public function getBlogList() {
        return '/aktuelles';
    }
    
    public function getBlogPostDetail($blogPostId, $name) {
        return '/aktuelles/' . $blogPostId . '-' . StringComponent::slugify($name);
    }
    
    public function getBlogPostListAdmin() {
        return '/admin/blog_posts';
    }
    public function getBlogPostEdit($blogPostId) {
        return '/admin/blog_posts/edit/'.$blogPostId;
    }
    public function getBlogPostAdd() {
        return '/admin/blog_posts/add';
    }
    
    public function getPagesListAdmin() {
        return '/admin/pages';
    }
    
    public function getPageEdit($pageId) {
        return '/admin/pages/edit/'.$pageId;
    }
    
    public function getPageAdd() {
        return '/admin/pages/add';
    }
    
    public function getAttributesList() {
        return '/admin/attributes';
    }
    public function getAttributeAdd() {
        return '/admin/attributes/add';
    }
    public function getAttributeEdit($attributeId) {
        return '/admin/attributes/edit/'.$attributeId;
    }
    
    public function getCategoriesList() {
        return '/admin/categories';
    }
    public function getCategoryAdd() {
        return '/admin/categories/add';
    }
    public function getCategoryEdit($categoryId) {
        return '/admin/categories/edit/'.$categoryId;
    }
    
    public function getTaxesList() {
        return '/admin/taxes';
    }
    public function getTaxAdd() {
        return '/admin/taxes/add';
    }
    public function getTaxEdit($taxId) {
        return '/admin/taxes/edit/'.$taxId;
    }
    
    public function getManufacturerAdmin() {
        return '/admin/manufacturers';
    }
    public function getManufacturerEdit($manufacturerId) {
        return '/admin/manufacturers/edit/'.$manufacturerId;
    }
    public function getManufacturerAdd() {
        return '/admin/manufacturers/add';
    }
    
    public function getSliderList() {
        return '/admin/sliders';
    }
    public function getSliderEdit($slideId) {
        return '/admin/sliders/edit/'.$slideId;
    }
    public function getSliderAdd() {
        return '/admin/sliders/add';
    }
    
    public function getProductDetail($productId, $name) {
        return '/produkt/' . $productId . '-' . StringComponent::slugify($name);
    }
    
    public function getConfigurationsList() {
        return '/admin/configurations';
    }
    
    public function getConfigurationEdit($configurationId) {
        return '/admin/configurations/edit/'.$configurationId;
    }

}

?>