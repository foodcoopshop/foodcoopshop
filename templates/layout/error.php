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

?>
<!DOCTYPE html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta name="theme-color" content="<?php echo Configure::read('app.customFrontendColorTheme'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Error - <?php echo Configure::read('appDb.FCS_APP_NAME'); ?></title>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />

    <?php echo $this->element('jsNamespace'); ?>

    <?php
        // do not use AssetCompressPlugin here (not loaded for errors!)
        echo $this->Html->css([
            'reset',
            '/node_modules/bootstrap/dist/css/bootstrap.css',
            'global',
            'fonts',
            'frontend',
            'error',
            'custom',
        ]);
        if ($isMobile) {
            echo $this->Html->css(['mobile-error']);
        }
    ?>

</head>
<body>

    <div id="container">

            <div id="content">
            <?php echo $this->Flash->render(); ?>

            <div id="inner-content">
                <?php

                echo $this->fetch('content');

                $referer = '/';
                $refererName = __('homepage');
                if (!empty($_SERVER['HTTP_REFERER'])) {
                    $referer = $_SERVER['HTTP_REFERER'];
                    $refererName = __('page_you_have_just_been_to');
                }

                ?>
                <br /><a class="btn btn-success" href="<?php echo $referer; ?>"><?php echo __('Click_here_to_open_the') . ' ' . $refererName; ?>.</a>
                <div class="sc"></div>
            </div>
        </div>

    </div>

    <div class="sc"></div>

<?php

    echo $this->element('localizedJavascript');
    // do not use AssetCompressPlugin here (not loaded for errors!)
    echo $this->Html->script([
        '/node_modules/jquery/dist/jquery.min.js',
        '/node_modules/jquery-backstretch/jquery.backstretch.js',
        'helper.js',
    ]);

    $this->element('addScript', ['script' =>
        Configure::read('app.jsNamespace').".Helper.initAnystretch();"
    ]);

    $scripts = $this->fetch('script');
    if ($scripts != '') {
        echo $this->MyHtml->wrapJavascriptBlock($scripts);
    }

?>

</body>
</html>
