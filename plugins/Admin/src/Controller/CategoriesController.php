<?php
namespace Admin\Controller;
use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

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
        $this->Category = TableRegistry::get('Categories');
        $category = $this->Category->newEntity(
            ['active' => APP_ON],
            ['validate' => false]
        );
        $this->set('title_for_layout', 'Kategorie erstellen');
        $this->_processForm($category, false);
        
        if (empty($this->request->getData())) {
            $this->render('edit');
        }
    }
    
    public function edit($categoryId)
    {
        if ($categoryId === null) {
            throw new NotFoundException;
        }
        
        $this->Category = TableRegistry::get('Categories');
        $category = $this->Category->find('all', [
            'conditions' => [
                'Categories.id_category' => $categoryId
            ]
        ])->first();
        
        if (empty($category)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', 'Kategorie bearbeiten');
        $this->_processForm($category, true);
    }
    
    private function _processForm($category, $isEditMode)
    {
        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);
        $categoriesForSelect = $this->Category->getForSelect($category->id);
        $this->set('categoriesForSelect', $categoriesForSelect);
        
        if (empty($this->request->getData())) {
            $this->set('category', $category);
            return;
        }
        
        $this->loadComponent('Sanitize');
        $this->request->data = $this->Sanitize->trimRecursive($this->request->data);
        $this->request->data = $this->Sanitize->stripTagsRecursive($this->request->data);
        
        $category = $this->Category->patchEntity($category, $this->request->getData());
        if (!empty($category->getErrors())) {
            $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            $this->set('category', $category);
            $this->render('edit');
        } else {
            $category = $this->Category->save($category);
            
            if (!$isEditMode) {
                $messageSuffix = 'erstellt';
                $actionLogType = 'category_added';
            } else {
                $messageSuffix = 'geändert';
                $actionLogType = 'category_changed';
            }

            if (!empty($this->request->getData('Categories.tmp_image'))) {
                $this->saveUploadedImage($category->id_category, $this->request->getData('Categories.tmp_image'), Configure::read('app.htmlHelper')->getCategoryThumbsPath(), Configure::read('app.categoryImageSizes'));
            }
            
            if (!empty($this->request->getData('Categories.delete_image'))) {
                $this->deleteUploadedImage($category->id_category, Configure::read('app.htmlHelper')->getCategoryThumbsPath(), Configure::read('app.categoryImageSizes'));
            }
            
            $this->ActionLog = TableRegistry::get('ActionLogs');
            if (!empty($this->request->getData('Categories.delete_category'))) {
                $this->Category->delete($category);
                $actionLogType = 'category_deleted';
                $messageSuffix = 'gelöscht';
            }
            $message = 'Die Kategorie <b>' . $category->name . '</b> wurde ' . $messageSuffix . '.';
            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $category->id_category, 'Categories', $message);
            $this->Flash->success($message);
            
            $this->request->getSession()->write('highlightedRowId', $category->id_category);
            $this->redirect($this->request->getData('referer'));
            
        }
        
        $this->set('category', $category);
        
    }

    public function index()
    {
        $conditions = [];
        $this->Category = TableRegistry::get('Categories');
        $conditions[] = $this->Category->getExcludeCondition();
        $conditions[] = 'Categories.active > ' . APP_DEL;

        $totalCategoriesCount = $this->Category->find('all', [
            'conditions' => $conditions
        ])->count();
        $this->set('totalCategoriesCount', $totalCategoriesCount);

        $categories = $this->Category->getThreaded($conditions);

        $this->set('categories', $categories);

        $this->set('title_for_layout', 'Kategorien');
    }
}
