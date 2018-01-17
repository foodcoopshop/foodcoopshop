<?php
/**
 * CategoriesController
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
class CategoriesController extends AdminAppController
{

    public function isAuthorized($user)
    {
        return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin();
    }

    public function add()
    {
        $this->edit();
        $this->set('title_for_layout', 'Kategorie erstellen');
        $this->render('edit');
    }

    public function edit($categoryId = null)
    {
        $this->setFormReferer();

        $categoriesForSelect = $this->Category->getForSelect($categoryId);
        $this->set('categoriesForSelect', $categoriesForSelect);

        if ($categoryId > 0) {
            $unsavedCategory = $this->Category->find('first', array(
                'conditions' => array(
                    'Category.id_category' => $categoryId
                )
            ));
        } else {
            $unsavedCategory = array(
                'Category' => array(
                    'active' => APP_ON
                )
            );
        }

        $this->set('unsavedCategory', $unsavedCategory);
        $this->set('title_for_layout', 'Kategorie bearbeiten');

        if (empty($this->request->data)) {
            $this->request->data = $unsavedCategory;
        } else {
            // validate data - do not use $this->Category->saveAll()
            $this->Category->id = $categoryId;
            $this->Category->set($this->request->data['Category']);

            if ($this->request->data['Category']['id_parent'] == 0) {
                $this->request->data['Category']['id_parent'] = 2;
            }

            foreach ($this->request->data['Category'] as $key => &$data) {
                $data = strip_tags(trim($data));
            }

            $errors = array();
            if (! $this->Category->validates()) {
                $errors = array_merge($errors, $this->Category->validationErrors);
            }

            if (empty($errors)) {
                $this->loadModel('ActionLog');

                $this->Category->save($this->request->data['Category'], array(
                    'validate' => false
                ));
                if (is_null($categoryId)) {
                    $messageSuffix = 'erstellt.';
                    $actionLogType = 'category_added';
                } else {
                    $messageSuffix = 'geändert.';
                    $actionLogType = 'category_changed';
                }

                if ($this->request->data['Category']['tmp_image'] != '') {
                    $this->saveUploadedImage($this->Category->id, $this->request->data['Category']['tmp_image'], Configure::read('htmlHelper')->getCategoryThumbsPath(), Configure::read('AppConfig.categoryImageSizes'));
                }

                if ($this->request->data['Category']['delete_image']) {
                    $this->deleteUploadedImage($this->Category->id, Configure::read('htmlHelper')->getCategoryThumbsPath(), Configure::read('AppConfig.categoryImageSizes'));
                }

                if (isset($this->request->data['Category']['delete_category']) && $this->request->data['Category']['delete_category']) {
                    $this->Category->delete($this->Category->id); // cascade does not work here
                    $message = 'Die Kategorie "' . $this->request->data['Category']['name'] . '" wurde erfolgreich gelöscht.';
                    $this->ActionLog->customSave('category_deleted', $this->AppAuth->getUserId(), $this->Category->id, 'categorys', $message);
                    $this->Flash->success('Die Kategorie wurde erfolgreich gelöscht.');
                } else {
                    $message = 'Die Kategorie "' . $this->request->data['Category']['name'] . '" wurde ' . $messageSuffix;
                    $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $this->Category->id, 'categories', $message);
                    $this->Flash->success('Die Kategorie wurde erfolgreich gespeichert.');
                }

                $this->Session->write('highlightedRowId', $this->Category->id);
                $this->redirect($this->data['referer']);
            } else {
                $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            }
        }
    }

    public function index()
    {
        $conditions = array();
        $conditions[] = $this->Category->getExcludeCondition();
        $conditions[] = 'Category.active > ' . APP_DEL;

        $totalCategoriesCount = $this->Category->find('count', array(
            'conditions' => $conditions
        ));
        $this->set('totalCategoriesCount', $totalCategoriesCount);

        $categories = $this->Category->getThreaded($conditions);

        $this->set('categories', $categories);

        $this->set('title_for_layout', 'Kategorien');
    }
}
