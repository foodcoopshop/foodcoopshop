<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Cake\Core\Configure;
use App\Model\Entity\Block;
use App\Model\Entity\Page;
use Cake\Event\EventInterface;
use App\Services\CatalogService;
use App\Controller\Component\StringComponent;
use Cviebrock\DiscoursePHP\SSOHelper as SSOHelper;
use Cake\Datasource\Exception\RecordNotFoundException;

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
    
    public function beforeFilter(EventInterface $event): void
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

    public function home(): void
    {

        $headerPromosTable = $this->getTableLocator()->get('HeaderPromos');
        $headerPromo = $headerPromosTable->find('all', conditions: [
            'HeaderPromos.page_id' => Page::PAGE_ID_HOME,
        ])->first();
        $this->set('headerPromo', $headerPromo);

        $blogPostsTable = $this->getTableLocator()->get('BlogPosts');
        $blogPosts = $blogPostsTable->findBlogPosts(null, true);
        $this->set('blogPosts', $blogPosts);

        $this->set('title_for_layout', __('Welcome'));

        $homeBlocks = [];
        if (Configure::read('appDb.FCS_HOME_TEXT') != '') {
            $homeBlocks[] = (object)[
                'content' => Configure::read('appDb.FCS_HOME_TEXT'),
                'image_position' => Block::IMAGE_POSITION_LEFT,
            ];
        }

        if ($this->identity === null) {
            $blocksTable = $this->getTableLocator()->get('Blocks');
            $blocks = $blocksTable->getForHome()->all()->toArray();
            if (!empty($blocks)) {
                $homeBlocks = array_merge($homeBlocks, $blocks);
            }
        }
        $this->set('homeBlocks', $homeBlocks);

        $products = [];
        if (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $this->identity !== null) {
            $catalogService = new CatalogService();
            $products = $catalogService->getProducts(Configure::read('app.categoryAllProducts'), true);
            $products = $catalogService->prepareProducts($products);
        }
        $this->set('newProducts', $products);

    }

    public function detail(): ?Response
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
            'Customers',
            'HeaderPromos',
        ])->first();

        if (empty($page)) {
            throw new RecordNotFoundException('page not found');
        }

        // redirect direct call of page with link
        if ($page->extern_url != '') {
            return $this->redirect($page->extern_url);
        }

        $conditionsForChildren = [$pagesTable->aliasField('active') => APP_ON];
        if ($this->identity === null) {
            $conditionsForChildren[$pagesTable->aliasField('is_private')] = APP_OFF;
        }
        $page->children = $pagesTable->find('children',
            for: $pageId,
            direct: true,
            parentField: 'id_parent',
            conditions: $conditionsForChildren,
            order: [
                $pagesTable->aliasField('position') => 'ASC',
                $pagesTable->aliasField('title') => 'ASC'
            ]);

        $correctSlug = StringComponent::slugify($page->title);
        $givenSlug = StringComponent::removeIdFromSlug($this->getRequest()->getParam('pass')[0]);
        if ($correctSlug != $givenSlug) {
            return $this->redirect(Configure::read('app.slugHelper')->getPageDetail($pageId, $page->title));
        }

        $headerPromo = $page->header_promo;
        if ($headerPromo === null) {
            $headerPromosTable = $this->getTableLocator()->get('HeaderPromos');
            $headerPromo = $headerPromosTable->find('all', conditions: [
                'HeaderPromos.page_id' => $page->id_page,
            ])->first();
        }

        $this->set('page', $page);
        $this->set('headerPromo', $headerPromo);
        $this->set('title_for_layout', $page->title);

        return null;
    }

    public function discourseSso(): Response
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

        return $this->redirect($return_sso_url . $query);
    }

    public function termsOfUse(): void
    {
        $this->set('title_for_layout', __('Terms_of_use'));
    }

    public function privacyPolicy(): void
    {
        $this->set('title_for_layout', __('Privacy_policy'));
    }

    public function listOfAllergens(): void
    {
        $this->set('title_for_layout', __('List_of_allergens'));
    }
}
