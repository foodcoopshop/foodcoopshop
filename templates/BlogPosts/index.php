<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();".
    Configure::read('app.jsNamespace').".ModalImage.init('a.open-with-modal');"
]);
?>

<h1><?php echo $title_for_layout; ?>
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
