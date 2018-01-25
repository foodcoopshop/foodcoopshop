<?php

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

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
        switch ($this->request->action) {
            case 'detail':
                $pageId = (int) $this->request->getParam('pass')[0];
                $this->Page = TableRegistry::get('Pages');
                $page = $this->Page->find('all', [
                    'conditions' => [
                        'Pages.id_page' => $pageId,
                        'Pages.active' => APP_ON
                    ]
                ])->first();
                if (!empty($page) && !$this->AppAuth->user() && $page->is_private) {
                    $this->AppAuth->deny($this->request->action);
                }
                break;
        }
    }

    public function home()
    {
        
        /**
         * START: security keys check
         */
        $showKeyGeneratorWebsite = 0;
        $securityErrors = 0;
        if (Configure::read('AppConfig.cookieKey') == '') {
            echo '<p>Please copy this <b>app.cookieKey</b> to your config.custom.php: '.StringComponent::createRandomString(58).'</p>';
            $securityErrors++;
        }
        /*
        if (Configure::read('Security.salt') == '') {
            echo '<p>Please generate the <b>Security.salt</b> and copy it to your config.custom.php (not to your core.php)</p>';
            $securityErrors++;
            $showKeyGeneratorWebsite = 1;
        }
        if (Configure::read('Security.cipherSeed') == '') {
            echo '<p>Please generate the <b>Security.cipherSeed</b> and copy it to your config.custom.php (not to your core.php)</p>';
            $securityErrors++;
            $showKeyGeneratorWebsite = 1;
        }
        */
        if ($showKeyGeneratorWebsite) {
            echo '<p>Security.salt and Security.sipherSeed can be generated on this website: <a target="_blank" href="http://cakephp.thomasv.nl/">http://cakephp.thomasv.nl</a></p>';
        }
        if ($securityErrors > 0) {
            die('<p><b>Security errors: '.$securityErrors.'</b></p>');
        }
        /**
         * END: security keys check
         */

        $this->BlogPost = TableRegistry::get('BlogPosts');
        $blogPosts = $this->BlogPost->findFeatured($this->AppAuth);
        $this->set('blogPosts', $blogPosts);

        $this->set('title_for_layout', 'Willkommen');

        $this->Slider = TableRegistry::get('Sliders');
        $sliders = $this->Slider->getForHome();
        $this->set('sliders', $sliders);
    }

    public function detail()
    {
        $pageId = (int) $this->request->getParam('pass')[0];

        $conditions = [
            'Pages.id_page' => $pageId,
            'Pages.active' => APP_ON
        ];

        $this->Page = TableRegistry::get('Pages');
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
            ]
        );

        $correctSlug = Configure::read('AppConfig.slugHelper')->getPageDetail($page->id_page, $page->title);
        if ($correctSlug != Configure::read('AppConfig.slugHelper')->getPageDetail($pageId, StringComponent::removeIdFromSlug($this->request->getParam('pass')[0]))) {
            $this->redirect($correctSlug);
        }

        $this->set('page', $page);
        $this->set('title_for_layout', $page->title);
    }

    public function termsOfUse()
    {
        $this->set('title_for_layout', 'Nutzungsbedingungen');
    }

    public function privacyPolicy()
    {
        $this->set('title_for_layout', 'Datenschutzerklärung');
    }
}
