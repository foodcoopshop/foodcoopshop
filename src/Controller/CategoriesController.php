<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Component\StringComponent;
use App\Controller\Traits\PaginatedProductsTrait;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Core\Configure;
use App\Services\CatalogService;
use Cake\Event\EventInterface;

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
class CategoriesController extends FrontendController
{

    use PaginatedProductsTrait;

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'newProducts',
            'randomProducts',
            'search',
            'detail',
        ]);
    }

    public function newProducts(): void
    {
        $this->redirectIfPageIsSetTo1();
        $page = (int) $this->getRequest()->getQuery('page', 1);

        $blogPostsTable = $this->getTableLocator()->get('BlogPosts');
        $blogPosts = $blogPostsTable->findBlogPosts(null, true);
        $this->set('blogPosts', $blogPosts);

        $catalogService = new CatalogService();
        $products = $catalogService->getProducts(Configure::read('app.categoryAllProducts'), true);
        $totalProductCount = $catalogService->getProducts(Configure::read('app.categoryAllProducts'), true, countMode: true, page: $page);
        $pagesCount = $catalogService->getPagesCount($totalProductCount);
        $products = $catalogService->prepareProducts($products);
        $this->set('products', $products);

        $this->throw404IfNoProductsOnPaginatedPageFound($products, $page);
        
        $this->set('totalProductCount', $totalProductCount);
        $this->set('products', $products);
        $this->set('pagesCount', $pagesCount);
        $this->set('page', $page);

        $this->set('title_for_layout', __('New_products'));

        $this->render('detail');
    }

    public function randomProducts(): void
    {
        $this->redirectIfPageIsSetTo1();
        $page = (int) $this->getRequest()->getQuery('page', 1);

        $blogPostsTable = $this->getTableLocator()->get('BlogPosts');
        $blogPosts = $blogPostsTable->findBlogPosts(null, true);
        $this->set('blogPosts', $blogPosts);

        $catalogService = new CatalogService();
        $products = $catalogService->getProducts(Configure::read('app.categoryAllProducts'), filterByNewProducts: false, randomize: true);
        shuffle($products); // shuffle again due to caching
        $totalProductCount = $catalogService->getProducts(Configure::read('app.categoryAllProducts'), false, countMode: true, page: $page);
        $pagesCount = $catalogService->getPagesCount($totalProductCount);
        $products = $catalogService->prepareProducts($products);
        $this->set('products', $products);

        $this->throw404IfNoProductsOnPaginatedPageFound($products, $page);
        
        $this->set('totalProductCount', $totalProductCount);
        $this->set('products', $products);
        $this->set('pagesCount', $pagesCount);
        $this->set('page', $page);

        $this->set('title_for_layout', __('Random_products'));

        $this->render('detail');
    }
    
    public function search(): void
    {
        $this->redirectIfPageIsSetTo1();
        $page = (int) $this->getRequest()->getQuery('page', 1);
        $keyword = h(trim($this->getRequest()->getQuery('keyword', '')));

        if ($keyword == '') {
            throw new RecordNotFoundException('no keyword');
        }

        $this->set('keyword', $keyword);

        $blogPostsTable = $this->getTableLocator()->get('BlogPosts');
        $blogPosts = $blogPostsTable->findBlogPosts(null, true);
        $this->set('blogPosts', $blogPosts);

        $catalogService = new CatalogService();
        $products = $catalogService->getProducts(Configure::read('app.categoryAllProducts'), false, $keyword, page: $page);
        $totalProductCount = $catalogService->getProducts(Configure::read('app.categoryAllProducts'), false, $keyword, countMode: true);
        $pagesCount = $catalogService->getPagesCount($totalProductCount);
        $products = $catalogService->prepareProducts($products);

        $this->throw404IfNoProductsOnPaginatedPageFound($products, $page);

        $this->set('products', $products);
        $this->set('totalProductCount', $totalProductCount);
        $this->set('products', $products);
        $this->set('pagesCount', $pagesCount);
        $this->set('page', $page);

        $this->set('title_for_layout', __('Search') . ' "' . $keyword . '"');

        $this->render('detail');
    }

    public function detail(): void
    {
        $this->redirectIfPageIsSetTo1();
        $page = (int) $this->getRequest()->getQuery('page', 1);
        $categoryId = (int) $this->getRequest()->getParam('idAndSlug');

        $categoriesTable = $this->getTableLocator()->get('Categories');
        $category = $categoriesTable->find('all', conditions: [
            'Categories.id_category' => $categoryId,
            'Categories.active' => APP_ON,
        ])->first();

        if (empty($category)) {
            throw new RecordNotFoundException('category not found');
        }

        $correctSlug = StringComponent::slugify($category->name);
        $givenSlug = StringComponent::removeIdFromSlug($this->getRequest()->getParam('idAndSlug'));
        if ($correctSlug != $givenSlug) {
            $this->redirect(Configure::read('app.slugHelper')->getCategoryDetail($categoryId, $category->name));
        }

        $blogPostsTable = $this->getTableLocator()->get('BlogPosts');
        $blogPosts = $blogPostsTable->findBlogPosts(null, true);
        $this->set('blogPosts', $blogPosts);

        $catalogService = new CatalogService();
        $products = $catalogService->getProducts(
            categoryId: $categoryId,
            page: $page,
        );
        $products = $catalogService->prepareProducts($products);
        $totalProductCount = $catalogService->getProducts($categoryId, false, '', 0, true);
        $pagesCount = $catalogService->getPagesCount($totalProductCount);

        $this->throw404IfNoProductsOnPaginatedPageFound($products, $page);

        $this->set('totalProductCount', $totalProductCount);
        $this->set('products', $products);
        $this->set('pagesCount', $pagesCount);
        $this->set('page', $page);

        $this->set('category', $category);

        $this->set('title_for_layout', $category->name);
    }
}
