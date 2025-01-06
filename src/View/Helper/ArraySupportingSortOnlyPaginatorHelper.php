<?php
declare(strict_types=1);

/**
 * Copy of CakePHP 4.x branch's PaginatorHelper
 * CakePHP 5.x branch's PaginatorHelper can't handle arrays, only PaginatedInterfaces
 *
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

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\View\Helper;

use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;
use Cake\View\View;
use function Cake\Core\h;
use function Cake\I18n\__;

/**
 * Pagination Helper class for easy generation of pagination links.
 *
 * PaginationHelper encloses all methods needed when working with pagination.
 *
 * @property \Cake\View\Helper\UrlHelper $Url
 * @property \Cake\View\Helper\NumberHelper $Number
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Cake\View\Helper\FormHelper $Form
 * @link https://book.cakephp.org/4/en/views/helpers/paginator.html
 */
class ArraySupportingSortOnlyPaginatorHelper extends Helper
{
    use StringTemplateTrait;

    /**
     * List of helpers used by this helper
     *
     * @var array
     */
    protected array $helpers = ['Url', 'Number', 'Html', 'Form'];

    /**
     * Default config for this class
     *
     * Options: Holds the default options for pagination links
     *
     * The values that may be specified are:
     *
     * - `url` Url of the action. See Router::url()
     * - `url['?']['sort']` the key that the recordset is sorted.
     * - `url['?']['direction']` Direction of the sorting (default: 'asc').
     * - `url['?']['page']` Page number to use in links.
     * - `model` The name of the model.
     * - `escape` Defines if the title field for the link should be escaped (default: true).
     * - `routePlaceholders` An array specifying which paging params should be
     *   passed as route placeholders instead of query string parameters. The array
     *   can have values `'sort'`, `'direction'`, `'page'`.
     *
     * Templates: the templates used by this class
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'options' => [],
        'templates' => [
            'nextActive' => '<li class="next"><a rel="next" href="{{url}}">{{text}}</a></li>',
            'nextDisabled' => '<li class="next disabled"><a href="" onclick="return false;">{{text}}</a></li>',
            'prevActive' => '<li class="prev"><a rel="prev" href="{{url}}">{{text}}</a></li>',
            'prevDisabled' => '<li class="prev disabled"><a href="" onclick="return false;">{{text}}</a></li>',
            'counterRange' => '{{start}} - {{end}} of {{count}}',
            'counterPages' => '{{page}} of {{pages}}',
            'first' => '<li class="first"><a href="{{url}}">{{text}}</a></li>',
            'last' => '<li class="last"><a href="{{url}}">{{text}}</a></li>',
            'number' => '<li><a href="{{url}}">{{text}}</a></li>',
            'current' => '<li class="active"><a href="">{{text}}</a></li>',
            'ellipsis' => '<li class="ellipsis">&hellip;</li>',
            'sort' => '<a href="{{url}}">{{text}}</a>',
            'sortAsc' => '<a class="asc" href="{{url}}">{{text}}</a>',
            'sortDesc' => '<a class="desc" href="{{url}}">{{text}}</a>',
            'sortAscLocked' => '<a class="asc locked" href="{{url}}">{{text}}</a>',
            'sortDescLocked' => '<a class="desc locked" href="{{url}}">{{text}}</a>',
        ],
    ];

    /**
     * Default model of the paged sets
     *
     * @var string|null
     */
    protected $_defaultModel;

    /**
     * Constructor. Overridden to merge passed args with URL options.
     *
     * @param \Cake\View\View $view The View this helper is being attached to.
     * @param array<string, mixed> $config Configuration settings for the helper.
     */
    public function __construct(View $view, array $config = [])
    {
        parent::__construct($view, $config);

        $query = $this->_View->getRequest()->getQueryParams();
        unset($query['page'], $query['limit'], $query['sort'], $query['direction']);
        $this->setConfig(
            'options.url',
            array_merge($this->_View->getRequest()->getParam('pass', []), ['?' => $query])
        );
    }

    /**
     * Gets the current paging parameters from the resultset for the given model
     *
     * @param string|null $model Optional model name. Uses the default if none is specified.
     * @return array The array of paging parameters for the paginated resultset.
     */
    public function params(?string $model = null): array
    {
        $request = $this->_View->getRequest();

        if (empty($model)) {
            $model = (string)$this->defaultModel();
        }

        $params = $request->getAttribute('paging');

        return empty($params[$model]) ? [] : $params[$model];
    }

    /**
     * Convenience access to any of the paginator params.
     *
     * @param string $key Key of the paginator params array to retrieve.
     * @param string|null $model Optional model name. Uses the default if none is specified.
     * @return mixed Content of the requested param.
     */
    public function param(string $key, ?string $model = null)
    {
        $params = $this->params($model);

        return $params[$key] ?? null;
    }

    /**
     * Sets default options for all pagination links
     *
     * @param array<string, mixed> $options Default options for pagination links.
     *   See PaginatorHelper::$options for list of keys.
     * @return void
     */
    public function options(array $options = []): void
    {
        $request = $this->_View->getRequest();

        if (!empty($options['paging'])) {
            $request = $request->withAttribute(
                'paging',
                $options['paging'] + $request->getAttribute('paging', [])
            );
            unset($options['paging']);
        }

        $model = (string)$this->defaultModel();
        if (!empty($options[$model])) {
            $params = $request->getAttribute('paging', []);
            $params[$model] = $options[$model] + Hash::get($params, $model, []);
            $request = $request->withAttribute('paging', $params);
            unset($options[$model]);
        }

        $this->_View->setRequest($request);

        $this->_config['options'] = array_filter($options + $this->_config['options']);
        if (empty($this->_config['options']['url'])) {
            $this->_config['options']['url'] = [];
        }
        if (!empty($this->_config['options']['model'])) {
            $this->defaultModel($this->_config['options']['model']);
        }
    }

    /**
     * Gets the current key by which the recordset is sorted
     *
     * @param string|null $model Optional model name. Uses the default if none is specified.
     * @param array<string, mixed> $options Options for pagination links.
     * @return string|null The name of the key by which the recordset is being sorted, or
     *  null if the results are not currently sorted.
     * @link https://book.cakephp.org/4/en/views/helpers/paginator.html#creating-sort-links
     */
    public function sortKey(?string $model = null, array $options = []): ?string
    {
        if (empty($options)) {
            $options = $this->params($model);
        }
        if (!empty($options['sort'])) {
            return $options['sort'];
        }

        return null;
    }

    /**
     * Gets the current direction the recordset is sorted
     *
     * @param string|null $model Optional model name. Uses the default if none is specified.
     * @param array<string, mixed> $options Options for pagination links.
     * @return string The direction by which the recordset is being sorted, or
     *  null if the results are not currently sorted.
     * @link https://book.cakephp.org/4/en/views/helpers/paginator.html#creating-sort-links
     */
    public function sortDir(?string $model = null, array $options = []): string
    {
        $dir = null;

        if (empty($options)) {
            $options = $this->params($model);
        }

        if (!empty($options['direction'])) {
            $dir = strtolower($options['direction']);
        }

        if ($dir === 'desc') {
            return 'desc';
        }

        return 'asc';
    }

    /**
     * Generates a sorting link. Sets named parameters for the sort and direction. Handles
     * direction switching automatically.
     *
     * ### Options:
     *
     * - `escape` Whether you want the contents html entity encoded, defaults to true.
     * - `model` The model to use, defaults to PaginatorHelper::defaultModel().
     * - `direction` The default direction to use when this link isn't active.
     * - `lock` Lock direction. Will only use the default direction then, defaults to false.
     *
     * @param string $key The name of the key that the recordset should be sorted.
     * @param array<string, mixed>|string|null $title Title for the link. If $title is null $key will be used
     *   for the title and will be generated by inflection. It can also be an array
     *   with keys `asc` and `desc` for specifying separate titles based on the direction.
     * @param array<string, mixed> $options Options for sorting link. See above for list of keys.
     * @return string A link sorting default by 'asc'. If the resultset is sorted 'asc' by the specified
     *  key the returned link will sort by 'desc'.
     * @link https://book.cakephp.org/4/en/views/helpers/paginator.html#creating-sort-links
     */
    public function sort(string $key, $title = null, array $options = []): string
    {
        $options += ['url' => [], 'model' => null, 'escape' => true];
        $url = $options['url'];
        unset($options['url']);

        if (empty($title)) {
            $title = $key;

            if (strpos($title, '.') !== false) {
                $title = str_replace('.', ' ', $title);
            }

            $title = __(Inflector::humanize(preg_replace('/_id$/', '', $title)));
        }

        $defaultDir = isset($options['direction']) ? strtolower($options['direction']) : 'asc';
        unset($options['direction']);

        $locked = $options['lock'] ?? false;
        unset($options['lock']);

        $sortKey = (string)$this->sortKey($options['model']);
        $defaultModel = $this->defaultModel();
        $model = $options['model'] ?: $defaultModel;
        [$table, $field] = explode('.', $key . '.');
        if (!$field) {
            $field = $table;
            $table = $model;
        }
        $isSorted = (
            $sortKey === $table . '.' . $field ||
            $sortKey === $model . '.' . $key ||
            $table . '.' . $field === $model . '.' . $sortKey
        );

        $template = 'sort';
        $dir = $defaultDir;
        if ($isSorted) {
            if ($locked) {
                $template = $dir === 'asc' ? 'sortDescLocked' : 'sortAscLocked';
            } else {
                $dir = $this->sortDir($options['model']) === 'asc' ? 'desc' : 'asc';
                $template = $dir === 'asc' ? 'sortDesc' : 'sortAsc';
            }
        }
        if (is_array($title) && array_key_exists($dir, $title)) {
            $title = $title[$dir];
        }

        $paging = ['sort' => $key, 'direction' => $dir, 'page' => 1];

        $url = $this->generateUrl($paging, $options['model'], $url);

        $currentUrl = $this->_View->getRequest()->getRequestTarget();
        $url = $this->applyFixForChangingDirection($currentUrl, $url, $key);
        if (preg_match('/' . $key . '/', $currentUrl)) {
            $options['class'] = $this->_View->getRequest()->getQueryParams()['direction'] ?? '';
        }
        $options['escape'] = false;
        $result = $this->Html->link($title, $url, $options);
        return $result;
    }

    private function applyFixForChangingDirection($currentUrl, $url, $key): string
    {
        if (!preg_match('/' . $key . '/', $currentUrl)) {
            return $url;
        }

        if (preg_match('/direction=asc/', $currentUrl)) {
            $newUrl = preg_replace('/direction=asc/', 'direction=desc', $url);
        }
        if (preg_match('/direction=desc/', $currentUrl)) {
            $newUrl = preg_replace('/direction=desc/', 'direction=asc', $url);
        }
        return $newUrl ?? $url;
    }

    /**
     * Merges passed URL options with current pagination state to generate a pagination URL.
     *
     * ### Url options:
     *
     * - `escape`: If false, the URL will be returned unescaped, do only use if it is manually
     *    escaped afterwards before being displayed.
     * - `fullBase`: If true, the full base URL will be prepended to the result
     *
     * @param array<string, mixed> $options Pagination options.
     * @param string|null $model Which model to paginate on
     * @param array $url URL.
     * @param array<string, mixed> $urlOptions Array of options
     * @return string By default, returns a full pagination URL string for use
     *   in non-standard contexts (i.e. JavaScript)
     * @link https://book.cakephp.org/4/en/views/helpers/paginator.html#generating-pagination-urls
     */
    public function generateUrl(
        array $options = [],
        ?string $model = null,
        array $url = [],
        array $urlOptions = []
    ): string {
        $urlOptions += [
            'escape' => true,
            'fullBase' => false,
        ];

        return  $this->Url->build($this->generateUrlParams($options, $model, $url), $urlOptions);
    }

    /**
     * Merges passed URL options with current pagination state to generate a pagination URL.
     *
     * @param array<string, mixed> $options Pagination/URL options array
     * @param string|null $model Which model to paginate on
     * @param array $url URL.
     * @return array An array of URL parameters
     */
    public function generateUrlParams(array $options = [], ?string $model = null, array $url = []): array
    {
        $paging = $this->params($model);
        $paging += ['page' => null, 'sort' => null, 'direction' => null, 'limit' => null];

        if (
            !empty($paging['sort'])
            && !empty($options['sort'])
            && strpos($options['sort'], '.') === false
        ) {
            $paging['sort'] = $this->_removeAlias($paging['sort'], $model = null);
        }
        if (
            !empty($paging['sortDefault'])
            && !empty($options['sort'])
            && strpos($options['sort'], '.') === false
        ) {
            $paging['sortDefault'] = $this->_removeAlias($paging['sortDefault'], $model);
        }

        $options += array_intersect_key(
            $paging,
            ['page' => null, 'limit' => null, 'sort' => null, 'direction' => null]
        );

        if (!empty($options['page']) && $options['page'] === 1) {
            $options['page'] = null;
        }

        if (
            isset($paging['sortDefault'], $paging['directionDefault'], $options['sort'], $options['direction'])
            && $options['sort'] === $paging['sortDefault']
            && strtolower($options['direction']) === strtolower($paging['directionDefault'])
        ) {
            $options['sort'] = $options['direction'] = null;
        }
        $baseUrl = $this->_config['options']['url'] ?? [];
        if (!empty($paging['scope'])) {
            $scope = $paging['scope'];
            if (isset($baseUrl['?'][$scope]) && is_array($baseUrl['?'][$scope])) {
                $options += $baseUrl['?'][$scope];
                unset($baseUrl['?'][$scope]);
            }
            $options = [$scope => $options];
        }

        if (!empty($baseUrl)) {
            $url = Hash::merge($url, $baseUrl);
        }

        $url['?'] = $url['?'] ?? [];

        if (!empty($this->_config['options']['routePlaceholders'])) {
            $placeholders = array_flip($this->_config['options']['routePlaceholders']);
            $url += array_intersect_key($options, $placeholders);
            $url['?'] += array_diff_key($options, $placeholders);
        } else {
            $url['?'] += $options;
        }

        $url['?'] = Hash::filter($url['?']);

        return $url;
    }

    /**
     * Remove alias if needed.
     *
     * @param string $field Current field
     * @param string|null $model Current model alias
     * @return string Unaliased field if applicable
     */
    protected function _removeAlias(string $field, ?string $model = null): string
    {
        $currentModel = $model ?: $this->defaultModel();

        if (strpos($field, '.') === false) {
            return $field;
        }

        [$alias, $currentField] = explode('.', $field);

        if ($alias === $currentModel) {
            return $currentField;
        }

        return $field;
    }

    /**
     * Gets or sets the default model of the paged sets
     *
     * @param string|null $model Model name to set
     * @return string|null Model name or null if the pagination isn't initialized.
     */
    public function defaultModel(?string $model = null): ?string
    {
        if ($model !== null) {
            $this->_defaultModel = $model;
        }
        if ($this->_defaultModel) {
            return $this->_defaultModel;
        }

        $params = $this->_View->getRequest()->getAttribute('paging');
        if (!$params) {
            return null;
        }
        [$this->_defaultModel] = array_keys($params);

        return $this->_defaultModel;
    }

}