<?php

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;

/**
 * PagesController
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
class PagesController extends FrontendController
{

    public function beforeFilter(Event $event)
    {

        parent::beforeFilter($event);
        switch ($this->getRequest()->getParam('action')) {
            case 'detail':
                $pageId = (int) $this->getRequest()->getParam('pass')[0];
                $this->Page = TableRegistry::getTableLocator()->get('Pages');
                $page = $this->Page->find('all', [
                    'conditions' => [
                        'Pages.id_page' => $pageId,
                        'Pages.active' => APP_ON
                    ]
                ])->first();
                if (!empty($page) && !$this->AppAuth->user() && $page->is_private) {
                    $this->AppAuth->deny($this->getRequest()->getParam('action'));
                }
                break;
        }
    }

    public function home()
    {

        /**
         * START: security keys check
         */
        $securityErrors = 0;
        if (Configure::read('app.cookieKey') == '') {
            echo '<p>Please copy this <b>app.cookieKey</b> to your custom_config.php: '.StringComponent::createRandomString(58).'</p>';
            $securityErrors++;
        }
        if (Security::getSalt() == '') {
            echo '<p>Please copy this <b>Security => salt</b> to your custom_config.php: '.hash('sha256', Security::randomBytes(64)).'</p>';
            $securityErrors++;
        }
		if (Configure::read('app.cakeServerName') == '') {
			echo '<p>Please copy <b>http://' . $_SERVER['HTTP_HOST'] . '</b> to custom_config.php</p>';
			$securityErrors++;
		}
        if ($securityErrors > 0) {
            die('<p><b>Security errors: '.$securityErrors.'</b></p>');
        }

        /**
         * END: security keys check
         */

        $this->BlogPost = TableRegistry::getTableLocator()->get('BlogPosts');
        $blogPosts = $this->BlogPost->findFeatured($this->AppAuth);
        $this->set('blogPosts', $blogPosts);

        $this->set('title_for_layout', __('Welcome'));

        $this->Slider = TableRegistry::getTableLocator()->get('Sliders');
        $sliders = $this->Slider->getForHome();
        $this->set('sliders', $sliders);

    }

    public function detail()
    {
        $pageId = (int) $this->getRequest()->getParam('pass')[0];

        $conditions = [
            'Pages.id_page' => $pageId,
            'Pages.active' => APP_ON
        ];

        $this->Page = TableRegistry::getTableLocator()->get('Pages');
        $page = $this->Page->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Customers'
            ]
        ])->first();

        if (empty($page)) {
            throw new RecordNotFoundException('page not found');
        }

        // redirect direct call of page with link
        if ($page->extern_url != '') {
            $this->redirect($page->extern_url);
        }

        $conditionsForChildren = ['Pages.active' => APP_ON];
        if (!$this->AppAuth->user()) {
            $conditionsForChildren = ['Pages.is_private' => APP_OFF];
        }
        $page['children'] = $this->Page->find('children', [
            'for' => $pageId,
            'parentField' => 'id_parent',
            'conditions' => $conditionsForChildren,
            'order' => [
                'Pages.position' => 'ASC',
                'Pages.title' => 'ASC'
                ]
            ]);

        $correctSlug = Configure::read('app.slugHelper')->getPageDetail($page->id_page, $page->title);
        if ($correctSlug != Configure::read('app.slugHelper')->getPageDetail($pageId, StringComponent::removeIdFromSlug($this->getRequest()->getParam('pass')[0]))) {
            $this->redirect($correctSlug);
        }

        $this->set('page', $page);
        $this->set('title_for_layout', $page->title);
    }

    public function termsOfUse()
    {
        $this->set('title_for_layout', __('Terms_of_use'));
    }

    public function privacyPolicy()
    {
        $this->set('title_for_layout', __('Privacy_policy'));
    }

    public function listOfAllergens()
    {
        $this->set('title_for_layout', __('List_of_allergens'));
    }
}
