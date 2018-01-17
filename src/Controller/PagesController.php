<?php

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Event\Event;

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

    /*
    public function beforeFilter(Event $event)
    {

        parent::beforeFilter($event);
        switch ($this->action) {
            case 'detail':
                $pageId = (int) $this->params['pass'][0];
                $this->Page->recursive = -1;
                $page = $this->Page->find('first', [
                    'conditions' => [
                        'Page.id_page' => $pageId,
                        'Page.active' => APP_ON
                    ]
                ]);
                if (!empty($page) && !$this->AppAuth->user() && $page['Page']['is_private']) {
                    $this->AppAuth->deny($this->action);
                }
                break;
        }
    }
    */

    public function home()
    {
        
        pr('xxx');
        exit;
        
        /**
         * START: security keys check
         */
        $showKeyGeneratorWebsite = 0;
        $securityErrors = 0;
        if (Configure::read('AppConfig.cookieKey') == '') {
            echo '<p>Please copy this <b>app.cookieKey</b> to your config.custom.php: '.StringComponent::createRandomString(58).'</p>';
            $securityErrors++;
        }
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
        if ($showKeyGeneratorWebsite) {
            echo '<p>Security.salt and Security.sipherSeed can be generated on this website: <a target="_blank" href="http://cakephp.thomasv.nl/">http://cakephp.thomasv.nl</a></p>';
        }
        if ($securityErrors > 0) {
            die('<p><b>Security errors: '.$securityErrors.'</b></p>');
        }
        /**
         * END: security keys check
         */

        $this->loadModel('BlogPost');
        $blogPosts = $this->BlogPost->findFeatured($this->AppAuth);
        $this->set('blogPosts', $blogPosts);

        $this->set('title_for_layout', 'Willkommen');

        $this->loadModel('Slider');
        $sliders = $this->Slider->getForHome();
        $this->set('sliders', $sliders);
    }

    public function detail()
    {
        $pageId = (int) $this->params['pass'][0];

        $conditions = [
            'Page.id_page' => $pageId,
            'Page.active' => APP_ON
        ];

        $page = $this->Page->find('first', [
            'conditions' => $conditions
        ]);

        if (empty($page)) {
            throw new MissingActionException('page not found');
        }

        // redirect direct call of page with link
        if ($page['Page']['extern_url'] != '') {
            $this->redirect($page['Page']['extern_url']);
        }

        $children = $this->Page->children(
            $pageId,
            false,
            null,
            [
                'Page.position' => 'ASC',
                'Page.title' => 'ASC'
            ]
        );

        $page['children'] = [];
        foreach ($children as $child) {
            if ($child['Page']['active'] < APP_ON) {
                continue;
            }
            if (!$this->AppAuth->user() && $child['Page']['is_private']) {
                continue;
            }
            $page['children'][] = $child;
        }

        $correctSlug = Configure::read('AppConfig.slugHelper')->getPageDetail($page['Page']['id_page'], $page['Page']['title']);
        if ($correctSlug != Configure::read('AppConfig.slugHelper')->getPageDetail($pageId, StringComponent::removeIdFromSlug($this->params['pass'][0]))) {
            $this->redirect($correctSlug);
        }

        $this->set('page', $page);
        $this->set('title_for_layout', $page['Page']['title']);
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
