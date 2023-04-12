<?php
declare(strict_types=1);

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
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
]);
?>

<h1><?php echo $title_for_layout; ?>

<?php if (is_array($blogPosts)); //app.isBlogFeatureEnabled false ?>

<?php if (isset($manufacturer->name)) {
    echo ' ' . __('of_{0}', [$manufacturer->name]);
} ?>
<span><?php echo $blogPosts->count(); ?> <?php echo __('found'); ?></span></h1>

<?php

echo $this->element('blogPosts', [
    'blogPosts' => $blogPosts,
    'useCarousel' => false,
]);

?>
