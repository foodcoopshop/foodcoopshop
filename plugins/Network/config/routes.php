<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop Network Plugin 2.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $builder) {
    $builder->plugin('Network',
        ['path' => '/'],
        function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->connect('/api/{action}', ['plugin' => 'Network', 'controller' => 'Api']);
            $builder->connect('/network/{controller}/{action}/*', ['plugin' => 'Network']);
        }
    );
};