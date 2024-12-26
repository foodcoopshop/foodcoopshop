<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Core\Configure;
use Cviebrock\DiscoursePHP\SSOHelper as SSOHelper;
use App\Services\CatalogService;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class PagesController extends FrontendController
{
    
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'home',
            'detail',
            'privacyPolicy',
            'termsOfUse',
            'listOfAllergens',
        ]);
    }

    public function home()
    {

        $blogPostsTable = $this->getTableLocator()->get('BlogPosts');
        $blogPosts = $blogPostsTable->findBlogPosts(null, true);
        $this->set('blogPosts', $blogPosts);

        $this->set('title_for_layout', __('Welcome'));

        $slidersTable = $this->getTableLocator()->get('Sliders');
        $sliders = $slidersTable->getForHome();
        $this->set('sliders', $sliders);

        $products = [];
        if (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $this->identity !== null) {
            $catalogService = new CatalogService();
            $products = $catalogService->getProducts(Configure::read('app.categoryAllProducts'), true);
            $products = $catalogService->prepareProducts($products);
        }
        $this->set('newProducts', $products);

    }

    public function detail()
    {

        $pageId = (int) $this->getRequest()->getParam('idAndSlug');

        $conditions = [
            'Pages.id_page' => $pageId,
            'Pages.active' => APP_ON
        ];

        $pagesTable = $this->getTableLocator()->get('Pages');
        $page = $pagesTable->find('all',
        conditions: $conditions,
        contain: [
            'Customers'
        ])->first();

        if (empty($page)) {
            throw new RecordNotFoundException('page not found');
        }

        // redirect direct call of page with link
        if ($page->extern_url != '') {
            $this->redirect($page->extern_url);
        }

        $conditionsForChildren = ['Pages.active' => APP_ON];
        if ($this->identity === null) {
            $conditionsForChildren['Pages.is_private'] = APP_OFF;
        }
        $page['children'] = $pagesTable->find('children',
        for: $pageId,
        direct: true,
        parentField: 'id_parent',
        conditions: $conditionsForChildren,
        order: [
            'Pages.position' => 'ASC',
            'Pages.title' => 'ASC'
        ]);

        $correctSlug = StringComponent::slugify($page->title);
        $givenSlug = StringComponent::removeIdFromSlug($this->getRequest()->getParam('pass')[0]);
        if ($correctSlug != $givenSlug) {
            $this->redirect(Configure::read('app.slugHelper')->getPageDetail($pageId, $page->title));
        }

        $this->set('page', $page);
        $this->set('title_for_layout', $page->title);
    }

    public function discourseSso()
    {
        if ($this->identity === null) {
            die('No User');
        }
        if (!$this->identity->active) {
            die('Inactive User');
        }

        $discourse_sso_secret = Configure::read('app.discourseSsoSecret');

        $sso = new SSOHelper();
        $sso->setSecret($discourse_sso_secret);

        $payload = h($this->getRequest()->getQuery('sso'));
        $signature = h($this->getRequest()->getQuery('sig'));

        if (!($sso->validatePayload($payload, $signature))) {
            die('Bad SSO request');
        }

        $userId = $this->identity->getId();
        $userEmail = $this->identity->email;
        $extraParameters = [
            'name' => $this->identity->name,
        ];

        $nonce = $sso->getNonce($payload);
        $return_sso_url = $sso->getReturnSSOURL($payload);

        $query = $sso->getSignInString($nonce, $userId, $userEmail, $extraParameters);
        $query = (strpos($return_sso_url, '?') !== false ? '&' : '?') . $query;

        $this->redirect($return_sso_url . $query);
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
