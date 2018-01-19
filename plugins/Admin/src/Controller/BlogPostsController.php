<?php

use Admin\Controller\AdminAppController;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * BlogPostsController
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

class BlogPostsController extends AdminAppController
{

    public function isAuthorized($user)
    {
        switch ($this->action) {
            case 'edit':
                if ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin()) {
                    return true;
                }
                // manufacturer owner check
                if ($this->AppAuth->isManufacturer()) {
                    $blogPost = $this->BlogPost->find('first', array(
                        'conditions' => array(
                            'BlogPosts.id_blog_post' => $this->params['pass'][0]
                        )
                    ));
                    if ($blogPost['BlogPosts']['id_manufacturer'] != $this->AppAuth->getManufacturerId()) {
                        return false;
                    }
                    return true;
                }
                break;
            default:
                return $this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin() || $this->AppAuth->isManufacturer();
        }
    }

    public function add()
    {
        $this->edit();
        $this->set('title_for_layout', 'Blog-Artikel erstellen');
        $this->render('edit');
    }

    public function edit($blogPostId = null)
    {
        $this->setFormReferer();
        $this->Manufacturer = TableRegistry::get('Manufacturers');
        $this->set('manufacturersForDropdown', $this->Manufacturer->getForDropdown());

        $_SESSION['KCFINDER'] = array(
            'uploadURL' => Configure::read('AppConfig.cakeServerName') . "/files/kcfinder/blog_posts",
            'uploadDir' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/blog_posts"
        );

        if ($blogPostId > 0) {
            $unsavedBlogPost = $this->BlogPost->find('first', array(
                'conditions' => array(
                    'BlogPosts.id_blog_post' => $blogPostId
                )
            ));
            // default value
            $unsavedBlogPost['BlogPosts']['update_modified_field'] = APP_ON;
        } else {
            // default values for new blog posts
            $unsavedBlogPost = array(
                'BlogPosts' => array(
                    'active' => APP_ON,
                    'is_featured' => APP_ON,
                    'update_modified_field' => APP_ON
                )
            );
        }
        $this->set('title_for_layout', 'Blog-Artikel bearbeiten');

        if (empty($this->request->data)) {
            $this->request->data = $unsavedBlogPost;
        } else {
            // validate data - do not use $this->BlogPost->saveAll()
            $this->BlogPost->id = $blogPostId;
            $this->BlogPost->set($this->request->data['BlogPosts']);

            // quick and dirty solution for stripping html tags, use html purifier here
            foreach ($this->request->data['BlogPosts'] as $key => &$data) {
                if ($key != 'content') {
                    $data = strip_tags(trim($data));
                }
            }

            $errors = array();
            if (! $this->BlogPost->validates()) {
                $errors = array_merge($errors, $this->BlogPost->validationErrors);
            }

            if (empty($errors)) {
                $this->request->data['BlogPosts']['id_customer'] = $this->AppAuth->getUserId();

                // field "modified" is updated by cake, set to false to avoid it
                if (isset($this->request->data['BlogPosts']['update_modified_field']) && ! $this->request->data['BlogPosts']['update_modified_field']) {
                    $this->request->data['BlogPosts']['modified'] = false;
                }

                $this->ActionLog = TableRegistry::get('ActionLogs');

                if (is_null($blogPostId) && $this->AppAuth->isManufacturer()) {
                    $this->request->data['BlogPosts']['id_manufacturer'] = $this->AppAuth->getManufacturerId();
                }

                $this->BlogPost->save($this->request->data['BlogPosts'], array(
                    'validate' => false
                ));
                if (is_null($blogPostId)) {
                    $messageSuffix = 'erstellt.';
                    $actionLogType = 'blog_post_added';
                } else {
                    $messageSuffix = 'geändert.';
                    $actionLogType = 'blog_post_changed';
                }

                if ($this->request->data['BlogPosts']['tmp_image'] != '') {
                    $this->saveUploadedImage($this->BlogPost->id, $this->request->data['BlogPosts']['tmp_image'], Configure::read('AppConfig.htmlHelper')->getBlogPostThumbsPath(), Configure::read('AppConfig.blogPostImageSizes'));
                }

                if ($this->request->data['BlogPosts']['delete_image']) {
                    $this->deleteUploadedImage($this->BlogPost->id, Configure::read('AppConfig.htmlHelper')->getBlogPostThumbsPath(), Configure::read('AppConfig.blogPostImageSizes'));
                }

                if (isset($this->request->data['BlogPosts']['delete_blog_post']) && $this->request->data['BlogPosts']['delete_blog_post']) {
                    $this->BlogPost->saveField('active', APP_DEL, false);
                    $message = 'Der Blog-Artikel "' . $this->request->data['BlogPosts']['title'] . '" wurde erfolgreich gelöscht.';
                    $this->ActionLog->customSave('blog_post_deleted', $this->AppAuth->getUserId(), $this->BlogPost->id, 'blog_posts', $message);
                    $this->Flash->success('Der Blog-Artikel wurde erfolgreich gelöscht.');
                } else {
                    $message = 'Der Blog-Artikel "' . $this->request->data['BlogPosts']['title'] . '" wurde ' . $messageSuffix;
                    $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $this->BlogPost->id, 'blog_posts', $message);
                    $this->Flash->success('Der Blog-Artikel wurde erfolgreich gespeichert.');
                }

                $this->request->session()->write('highlightedRowId', $this->BlogPost->id);
                $this->redirect($this->data['referer']);
            } else {
                $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            }
        }
    }

    public function index()
    {
        $conditions = array();

        $customerId = '';
        if (! empty($this->params['named']['customerId'])) {
            $customerId = $this->params['named']['customerId'];
            $conditions = array(
                'BlogPosts.id_customer' => $customerId
            );
        }
        $this->set('customerId', $customerId);

        $manufacturerId = '';
        if (! empty($this->params['named']['manufacturerId'])) {
            $manufacturerId = $this->params['named']['manufacturerId'];
        }
        $this->set('manufacturerId', $manufacturerId);

        if ($this->AppAuth->isManufacturer()) {
            $manufacturerId = $this->AppAuth->getManufacturerId();
        }
        if ($manufacturerId != '') {
            $conditions = array(
                'BlogPosts.id_manufacturer' => $manufacturerId
            );
        }

        $conditions[] = 'BlogPosts.active > ' . APP_DEL;

        $this->Paginator->settings = array_merge(array(
            'conditions' => $conditions,
            'order' => array(
                'BlogPosts.modified' => 'DESC'
            )
        ), $this->Paginator->settings);
        $blogPosts = $this->Paginator->paginate('BlogPosts');

        foreach ($blogPosts as &$blogPost) {
            $manufacturerRecord = $this->BlogPost->Customer->getManufacturerRecord($blogPost);
            $blogPost['Customers']['Manufacturers'] = @$manufacturerRecord['Manufacturers'];
        }

        $this->set('blogPosts', $blogPosts);

        $this->set('title_for_layout', 'Blog-Artikel');

        $this->Customer = TableRegistry::get('Customers');
        $this->set('customersForDropdown', $this->Customer->getForDropdown());

        $this->Manufacturer = TableRegistry::get('Manufacturers');
        $this->set('manufacturersForDropdown', $this->Manufacturer->getForDropdown());
    }
}
