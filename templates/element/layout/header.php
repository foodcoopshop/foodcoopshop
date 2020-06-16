<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\Utility\Inflector;

// to always get an up-to-date cart if "browser back" or "duplicate tab" is used
// check if the software is run from unit tests or not
// when run from the unit tests, setting the headers conflicts with setting the headers in vendor/phpunit/phpunit/src/Util/Printer.php
if (! defined('PHPUNIT_COMPOSER_INSTALL') && ! defined('__PHPUNIT_PHAR__')) {
    header('Cache-Control: no-store, private, no-cache, must-revalidate'); // HTTP/1.1
    header('Cache-Control: pre-check=0, post-check=0, max-age=0, max-stale = 0', false); // HTTP/1.1
    header('Pragma: public');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Expires: 0', false);
    header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
    header('Pragma: no-cache');
}
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
    <link rel="mask-icon" href="/safari-pinned-tab.svg">

    <?php echo $this->element('jsNamespace'); ?>

    <?php
        echo $this->element('renderCss', ['configs' => ['frontend']]);
        echo $this->Html->css(['/node_modules/swiper/css/swiper.min']);
        if ($isMobile) {
            echo $this->Html->css(['/node_modules/slidebars/dist/slidebars', 'mobile-global', 'mobile-frontend', 'mobile-self-service', 'mobile-frontend-custom']);
        }
        echo $this->element('customFrontendColorThemeCss');
        echo $this->element('layout/customHeader');
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
