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
<!DOCTYPE html>
<html lang="<?php echo strtolower(str_replace('_', '-', I18n::getLocale())); ?>">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta name="theme-color" content="#719f41">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0">
    
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="/manifest.json">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">

    <title><?php echo $title_for_layout; ?> - <?php echo Configure::read('appDb.FCS_APP_NAME'); ?></title>

    <?php echo $this->element('jsNamespace'); ?>
    
    <?php
    $cssConfigs = ['admin'];
    if ($this->plugin != 'Admin') {
        $cssConfigs[] = $this->plugin.'.all';
    }
    echo $this->element('renderCss', ['configs' => $cssConfigs]);
    if ($isMobile) {
        echo $this->Html->css(['/node_modules/slidebars/dist/slidebars', 'mobile-global', 'Admin.mobile']);
    }
    ?>
    
</head>
<body class="<?php echo Inflector::tableize($this->name); ?> <?php echo Inflector::singularize(Inflector::tableize($this->request->getParam('action'))); ?>">

    <div id="container">
        
        <?php echo $this->element('Admin.menu'); ?>
        
        <div id="content">
            <?php
                echo $this->Flash->render();
                echo $this->Flash->render('auth');
                echo $this->fetch('content');
            ?>
        </div>
    </div>
    
    <?php echo $this->element('scrollToTopButton'); ?>
    
    <div class="sc"></div>
    
<?php
$jsConfigs = ['admin'];
if ($this->plugin != 'Admin') {
    $jsConfigs[] = $this->plugin.'.all';
}
echo $this->element('localizedJavascript');
echo $this->element('renderJs', ['configs' => $jsConfigs]);

if ($isMobile) {
    echo '<div class="is-mobile-detector"></div>';
    echo $this->Html->script(['/node_modules/slidebars/dist/slidebars']);

    // add script BEFORE all scripts that are loaded in views (block)
    echo $this->MyHtml->scriptBlock(
        $this->Html->wrapJavascriptBlock(
            Configure::read('app.jsNamespace').".Mobile.initMenusAdmin();",
            ['block']
        )
    );
}

if ($this->plugin == 'Admin') {
    echo $this->Html->script('/node_modules/bootstrap-select/dist/js/i18n/defaults-'.I18n::getLocale().'.js');
    echo $this->Html->script('/node_modules/ckeditor/ckeditor');
    echo $this->Html->script('/node_modules/ckeditor/adapters/jquery');
}

$scripts = $this->fetch('script');
if ($scripts != '') {
    echo $this->Html->wrapJavascriptBlock($scripts);
}

?>

</body>
</html>
