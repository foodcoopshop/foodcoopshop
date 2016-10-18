<?php
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
                            'BlogPost.id_smart_blog_post' => $this->params['pass'][0]
                        )
                    ));
                    if ($blogPost['BlogPost']['id_manufacturer'] != $this->AppAuth->getManufacturerId()) {
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
        $this->loadModel('Manufacturer');
        $this->set('manufacturersForDropdown', $this->Manufacturer->getForDropdown());
        
        $_SESSION['KCFINDER'] = array(
            'uploadURL' => Configure::read('app.cakeServerName') . "/files/kcfinder/blog_posts",
            'uploadDir' => $_SERVER['DOCUMENT_ROOT'] . "/files/kcfinder/blog_posts"
        );
        
        if ($blogPostId > 0) {
            $unsavedBlogPost = $this->BlogPost->find('first', array(
                'conditions' => array(
                    'BlogPost.id_smart_blog_post' => $blogPostId
                )
            ));
            // default value
            $unsavedBlogPost['BlogPost']['update_modified_field'] = APP_ON;
        } else {
            // default values for new blog posts
            $unsavedBlogPost = array(
                'BlogPost' => array(
                    'active' => APP_ON,
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
            $this->BlogPost->set($this->request->data['BlogPost']);
            
            // quick and dirty solution for stripping html tags, use html purifier here
            foreach ($this->request->data['BlogPost'] as &$data) {
                $data = strip_tags($data);
            }
            foreach ($this->request->data['BlogPostLang'] as $key => &$data) {
                if ($key != 'content') {
                    $data = strip_tags($data);
                }
            }
            
            $errors = array();
            
            $this->BlogPost->BlogPostLang->set($this->request->data['BlogPostLang']);
            if (! $this->BlogPost->BlogPostLang->validates()) {
                $errors = array_merge($errors, $this->BlogPost->BlogPostLang->validationErrors);
            }
            
            if (empty($errors)) {
                
                $this->request->data['BlogPost']['id_customer'] = $this->AppAuth->getUserId();
                
                // field "modified" is updated by cake, set to false to avoid it
                if (isset($this->request->data['BlogPost']['update_modified_field']) && ! $this->request->data['BlogPost']['update_modified_field']) {
                    $this->request->data['BlogPost']['modified'] = false;
                }
                
                $this->loadModel('CakeActionLog');
                
                if (is_null($blogPostId) && $this->AppAuth->isManufacturer()) {
                    $this->request->data['BlogPost']['id_manufacturer'] = $this->AppAuth->getManufacturerId();
                }
                
                $this->BlogPost->save($this->request->data['BlogPost'], array(
                    'validate' => false
                ));
                if (is_null($blogPostId)) {
                    $this->request->data['BlogPostLang']['id_smart_blog_post'] = $this->BlogPost->id;
                    $this->request->data['BlogPostLang']['id_lang'] = Configure::read('app.langId');
                    $this->request->data['BlogPostShop']['id_smart_blog_post'] = $this->BlogPost->id;
                    $this->request->data['BlogPostShop']['id_shop'] = Configure::read('app.shopId');
                    $this->BlogPost->BlogPostShop->save($this->request->data, array(
                        'validate' => false
                    ));
                    $messageSuffix = 'erstellt.';
                    $actionLogType = 'blog_post_added';
                } else {
                    $this->BlogPost->BlogPostLang->id = $blogPostId;
                    $messageSuffix = 'geändert.';
                    $actionLogType = 'blog_post_changed';
                }
                
                $this->BlogPost->BlogPostLang->save($this->request->data, array(
                    'validate' => false
                ));
                
                if ($this->request->data['BlogPost']['tmp_image'] != '') {
                    $this->saveUploadedImage($this->BlogPost->id, $this->request->data['BlogPost']['tmp_image'], Configure::read('htmlHelper')->getBlogPostThumbsPath(), Configure::read('app.blogPostImageSizes'));
                }
                
                if ($this->request->data['BlogPost']['delete_image']) {
                    $this->deleteUploadedImage($this->BlogPost->id, Configure::read('htmlHelper')->getBlogPostThumbsPath(), Configure::read('app.blogPostImageSizes'));
                }
                
                if (isset($this->request->data['BlogPost']['delete_blog_post']) && $this->request->data['BlogPost']['delete_blog_post']) {
                    $this->BlogPost->saveField('active', APP_DEL, false);
                    $message = 'Der Blog-Artikel "' . $this->request->data['BlogPostLang']['meta_title'] . '" wurde erfolgreich gelöscht.';
                    $this->CakeActionLog->customSave('blog_post_deleted', $this->AppAuth->getUserId(), $this->BlogPost->id, 'blog_posts', $message);
                    $this->AppSession->setFlashMessage('Der Blog-Artikel wurde erfolgreich gelöscht.');
                } else {
                    $message = 'Der Blog-Artikel "' . $this->request->data['BlogPostLang']['meta_title'] . '" wurde ' . $messageSuffix;
                    $this->CakeActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $this->BlogPost->id, 'blog_posts', $message);
                    $this->AppSession->setFlashMessage('Der Blog-Artikel wurde erfolgreich gespeichert.');
                }
                
                $this->AppSession->write('highlightedRowId', $this->BlogPost->id);
                $this->redirect($this->data['referer']);
            } else {
                $this->AppSession->setFlashError('Beim Speichern sind Fehler aufgetreten!');
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
                'BlogPost.id_customer' => $customerId
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
                'BlogPost.id_manufacturer' => $manufacturerId
            );
        }
        
        $conditions[] = 'BlogPost.active > ' . APP_DEL;
        
        $this->Paginator->settings = array_merge(array(
            'conditions' => $conditions,
            'order' => array(
                'BlogPost.modified' => 'DESC'
            )
        ), $this->Paginator->settings);
        $blogPosts = $this->Paginator->paginate('BlogPost');
        
        foreach ($blogPosts as &$blogPost) {
            $manufacturerRecord = $this->BlogPost->Customer->getManufacturerRecord($blogPost);
            $blogPost['Customer']['Manufacturer'] = @$manufacturerRecord['Manufacturer'];
        }
        
        $this->set('blogPosts', $blogPosts);
        
        $this->set('title_for_layout', 'Blog-Artikel');
        
        $this->loadModel('Customer');
        $this->set('customersForDropdown', $this->Customer->getForDropdown());
        
        $this->loadModel('Manufacturer');
        $this->set('manufacturersForDropdown', $this->Manufacturer->getForDropdown());
    }
}

?>