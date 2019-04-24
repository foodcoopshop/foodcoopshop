<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

echo $this->element('layout/header');

?>

<div id="container">

    <div id="header">
        <div class="logo-wrapper">
            <a href="<?php echo $this->Slug->getHome(); ?>" title="<?php echo __('Home'); ?>">
                <img class="logo" src="/files/images/logo.jpg" />
            </a>
        </div>
        <?php if (Configure::read('appDb.FCS_SHOW_PRODUCTS_FOR_GUESTS') || $appAuth->user()) { ?>
            <?php
            	$this->element('addScript', ['script' =>
                    Configure::read('app.jsNamespace').".Helper.initSearchForm();"
                ]);
            ?>
            <form id="product-search" action="/<?php echo __('route_search');?>">
                <input placeholder="<?php echo __('Search'); ?>" name="keyword" type="text" required="required" <?php echo isset($keyword) ? 'value="'.$keyword.'"' : ''; ?> />
                <button type="submit" class="btn btn-success"><i class="fas fa-search"></i></button>
            </form>
        <?php } ?>
        <?php echo $this->element('userMenu'); ?>
        <?php echo $this->element('mainMenu'); ?>
    </div>
    
    <div id="content">
        <?php
            echo $this->Flash->render();
            echo $this->Flash->render('auth');
        ?>
        <?php echo $this->element('slider', ['slides' => !empty($slides) ? $slides : []]); ?>         
        <?php echo $this->element('sidebar'); ?>
        <div id="inner-content">
            <?php echo $this->fetch('content'); ?>
            <div class="sc"></div>
        </div>
    </div>
    
    <div id="right">
        <div class="inner-right">
            <?php echo $this->element('cart', ['showLinkToSelfService' => true]); ?>
            <?php echo $this->element('infoBox'); ?>
        </div>
    </div>
    
    <div id="footer">
        <div class="inner-footer">
            <?php echo $this->element('footer'); ?>
        </div>
    </div>
    
</div>

<?php echo $this->element('scrollToTopButton'); ?>

<div class="sc"></div>

<?php
echo $this->element('layout/footer');
?>
