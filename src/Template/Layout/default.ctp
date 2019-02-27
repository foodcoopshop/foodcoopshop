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
use Cake\I18n\I18n;
use Cake\Utility\Inflector;

?>
<?php
// to always get an up-to-date cart if "browser back" or "duplicate tab" is used
header('Cache-Control: no-store, private, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0, max-stale = 0', false); // HTTP/1.1
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Expires: 0', false);
header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="<?php echo strtolower(str_replace('_', '-', I18n::getLocale())); ?>">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta name="theme-color" content="<?php echo Configure::read('app.customFrontendColorTheme'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $title_for_layout; ?> - <?php echo Configure::read('appDb.FCS_APP_NAME'); ?></title>

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="/manifest.json">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    
    <?php echo $this->element('jsNamespace'); ?>
    
    <?php
        echo $this->element('renderCss', ['configs' => ['frontend']]);
        if ($isMobile) {
            echo $this->Html->css(['/node_modules/slidebars/dist/slidebars', 'mobile-global', 'mobile-frontend', 'mobile-frontend-custom']);
        }
        echo $this->element('customFrontendColorThemeCss');
    ?>
    
</head>

<?php
    $bodyClasses = [
        Inflector::tableize($this->name),
        Inflector::singularize(Inflector::tableize($this->request->getParam('action')))
    ];
    if ($appAuth->isSuperadmin()) {
        $bodyClasses[] = 'superadmin';
    }
?>

<body class="<?php echo implode(' ', $bodyClasses); ?>">

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
                <?php echo $this->element('cart'); ?>
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
    echo $this->element('localizedJavascript');
    echo $this->element('renderJs', ['configs' => ['frontend']]);

    if (Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED')) {
        echo $this->Html->scriptBlock(
            $this->Html->wrapJavascriptBlock(
                Configure::read('app.jsNamespace').".TimebasedCurrency.setShortcode('".Configure::read('appDb.FCS_TIMEBASED_CURRENCY_SHORTCODE')."');"
            ),
            ['inline' => true]
        );
    }

    if ($isMobile) {
        echo '<div class="is-mobile-detector"></div>';
        echo $this->Html->script(['/node_modules/slidebars/dist/slidebars']);

        // add script BEFORE all scripts that are loaded in views (block)
        echo $this->MyHtml->scriptBlock(
            $this->Html->wrapJavascriptBlock(
                Configure::read('app.jsNamespace').".Mobile.initMenusFrontend();"
            ),
            ['inline' => true]
        );
    }

    $scripts = $this->fetch('script');
    if ($scripts != '') {
        echo $this->Html->wrapJavascriptBlock($scripts);
    }

?>

</body>
</html>
