<?php
namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
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
        $this->Category = TableRegistry::getTableLocator()->get('Categories');
        $category = $this->Category->newEntity(
            ['active' => APP_ON],
            ['validate' => false]
        );
        $this->set('title_for_layout', __d('admin', 'Add_category'));

        $this->set('disabledSelectCategoryIds', []);

        $this->_processForm($category, false);

        if (empty($this->getRequest()->getData())) {
            $this->render('edit');
        }
    }

    public function edit($categoryId)
    {
        if ($categoryId === null) {
            throw new NotFoundException;
        }

        $this->Category = TableRegistry::getTableLocator()->get('Categories');
        $category = $this->Category->find('all', [
            'conditions' => [
                'Categories.id_category' => $categoryId
            ]
        ])->first();

        if (empty($category)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', __d('admin', 'Edit_category'));

        $categoryChildren = $this->Category->find('all')->find('children', ['for' => $categoryId]);

        $disabledSelectCategoryIds = [(int) $categoryId];
        foreach ($categoryChildren as $categoryChild) {
            $disabledSelectCategoryIds[] = $categoryChild->id_category;
        }
        $this->set('disabledSelectCategoryIds', $disabledSelectCategoryIds);

        $this->_processForm($category, true);
    }

    private function _processForm($category, $isEditMode)
    {
        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);
        $categoriesForSelect = $this->Category->getForSelect($category->id);
        $this->set('categoriesForSelect', $categoriesForSelect);

        if (empty($this->getRequest()->getData())) {
            $this->set('category', $category);
            return;
        }

        $this->loadComponent('Sanitize');
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->trimRecursive($this->getRequest()->getData())));
        $this->setRequest($this->getRequest()->withParsedBody($this->Sanitize->stripTagsRecursive($this->getRequest()->getData(), ['description'])));

        $category = $this->Category->patchEntity($category, $this->getRequest()->getData());
        if ($category->hasErrors()) {
            $this->Flash->error(__d('admin', 'Errors_while_saving!'));
            $this->set('category', $category);
            $this->render('edit');
        } else {
            $category = $this->Category->save($category);

            if (!$isEditMode) {
                $messageSuffix = __d('admin', 'created');
                $actionLogType = 'category_added';
            } else {
                $messageSuffix = __d('admin', 'changed');
                $actionLogType = 'category_changed';
            }

            if (!empty($this->getRequest()->getData('Categories.tmp_image'))) {
                $this->saveUploadedImage($category->id_category, $this->getRequest()->getData('Categories.tmp_image'), Configure::read('app.htmlHelper')->getCategoryThumbsPath(), Configure::read('app.categoryImageSizes'));
            }

            if (!empty($this->getRequest()->getData('Categories.delete_image'))) {
                $this->deleteUploadedImage($category->id_category, Configure::read('app.htmlHelper')->getCategoryThumbsPath(), Configure::read('app.categoryImageSizes'));
            }

            $this->ActionLog = TableRegistry::getTableLocator()->get('ActionLogs');
            if (!empty($this->getRequest()->getData('Categories.delete_category'))) {
                $this->Category->delete($category);
                $actionLogType = 'category_deleted';
                $messageSuffix = __d('admin', 'deleted');
            }
            $message = __d('admin', 'The_category_{0}_has_been_{1}.', ['<b>' . $category->name . '</b>', $messageSuffix]);
            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $category->id_category, 'categories', $message);
            $this->Flash->success($message);

            $this->getRequest()->getSession()->write('highlightedRowId', $category->id_category);
            $this->redirect($this->getRequest()->getData('referer'));
        }

        $this->set('category', $category);
    }

    public function index()
    {
        $conditions = [];
        $this->Category = TableRegistry::getTableLocator()->get('Categories');
        $conditions[] = $this->Category->getExcludeCondition();
        $conditions[] = 'Categories.active > ' . APP_DEL;

        $totalCategoriesCount = $this->Category->find('all', [
            'conditions' => $conditions
        ])->count();
        $this->set('totalCategoriesCount', $totalCategoriesCount);

        $categories = $this->Category->getThreaded($conditions);

        $this->set('categories', $categories);

        $this->set('title_for_layout', __d('admin', 'Categories'));
    }
}
