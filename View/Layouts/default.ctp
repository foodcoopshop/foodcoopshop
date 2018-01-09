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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
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
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta name="theme-color" content="#719f41">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $title_for_layout; ?> - <?php echo Configure::read('app.db_config_FCS_APP_NAME'); ?></title>

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="/manifest.json">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    
    <?php echo $this->element('jsNamespace'); ?>
    <link href='//fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
    
    <?php
        echo $this->element('renderCss', array('configs' => array('frontend')));
    if ($isMobile) {
        echo $this->Html->css(array('/node_modules/slidebars/dist/slidebars', 'mobile-global', 'mobile-frontend'));
    }
    ?>
    
</head>

<?php
    $bodyClasses = array(
        Inflector::tableize($this->name),
        Inflector::singularize(Inflector::tableize($this->action))
    );
    if ($appAuth->isSuperadmin()) {
        $bodyClasses[] = 'superadmin';
    }
?>

<body class="<?php echo implode(' ', $bodyClasses); ?>">

    <div id="container">
    
        <div id="header">
            <div class="logo-wrapper">
                <a href="<?php echo $this->Slug->getHome(); ?>" title="Home">
                    <img class="logo" src="/files/images/logo.jpg" />
                </a>
            </div>
            <?php if (Configure::read('app.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS') || $appAuth->loggedIn()) { ?>
                <form id="product-search" action="/suche">
                    <input placeholder="Suche" name="keyword" type="text" required="required" <?php echo isset($keyword) ? 'value="'.$keyword.'"' : ''; ?> />
                    <button type="submit" class="btn btn-success"><i class="fa fa-search"></i></button>
                </form>
            <?php } ?>
            <?php echo $this->element('userMenu'); ?>
            <?php echo $this->element('mainMenu'); ?>
        </div>
        
        <div id="content">
            <?php echo $this->Session->flash(); ?>
            <?php echo $this->element('slider', array('slides' => !empty($slides) ? $slides : array())); ?>         
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
    <?php echo $this->element('sql_dump'); ?>
    
<?php
    echo $this->element('renderJs', array('configs' => array('frontend')));
if ($isMobile) {
    echo '<div class="is-mobile-detector"></div>';
    echo $this->Html->script(array('/node_modules/slidebars/dist/slidebars'));
    // add script BEFORE all scripts that are loaded in views (block)
    echo $this->MyHtml->scriptBlock(Configure::read('app.jsNamespace').".Mobile.initMenusFrontend();", array('block'));
}
    echo $this->fetch('script'); // all scripts from layouts
?>

</body>
</html>
