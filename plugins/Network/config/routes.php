<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop Network Plugin 2.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
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