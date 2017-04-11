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
<!DOCTYPE html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta name="theme-color" content="#719f41">
    
    <title><?php echo $title_for_layout; ?> - <?php echo Configure::read('app.db_config_FCS_APP_NAME'); ?></title>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    
    <?php echo $this->element('jsNamespace'); ?>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
    
    <?php echo $this->element('renderCss', array('config' => 'plain')); ?>
    
</head>
<body class="<?php echo Inflector::tableize($this->name); ?> <?php echo Inflector::singularize(Inflector::tableize($this->action)); ?> <?php echo Configure::read('debug') == 2 ? 'dev' : ''; ?>">
    
    <div id="container">
    
            <div id="content">
            <?php echo $this->Session->flash(); ?>
            <div id="inner-content">
                <?php echo $this->fetch('content'); ?>
                <?php
                if ($this->name == 'CakeError' && Configure::read('debug') == 0) {
                    $referer = '/';
                    $refererName = 'Startseite';
                    if (!empty($_SERVER['HTTP_REFERER'])) {
                        $referer = $_SERVER['HTTP_REFERER'];
                        $refererName = 'Seite, auf der du gerade warst.';
                    }
                    ?>
                    <br /><a class="btn btn-success" href="<?php echo $referer; ?>">Hier geht's zur <?php echo $refererName; ?></a>
                <?php                                                                                                                                                                                                                                                                                                                                                                                                                                   } ?>
                <div class="sc"></div>
            </div>
        </div>
        
    </div>
    
    <div class="sc"></div>
    <?php echo $this->element('sql_dump'); ?>
    
<?php

    echo $this->element('renderJs', array('config' => 'frontend'));

if ($this->name != 'CakeError' || Configure::read('debug') == 0) {
    $this->element('addScript', array('script' =>
    Configure::read('app.jsNamespace').".Helper.initAnystretch();"
    ));
}

    echo $this->fetch('script'); // all scripts from layouts

?>

</body>
</html>
