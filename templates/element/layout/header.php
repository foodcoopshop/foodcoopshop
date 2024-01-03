<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
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
    <meta name="theme-color" content="<?php echo Configure::read('app.customThemeMainColor'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrfToken" content="<?php echo $this->request->getAttribute('csrfToken'); ?>">

    <title><?php echo $title_for_layout; ?> - <?php echo Configure::read('appDb.FCS_APP_NAME'); ?></title>

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="/site.webmanifest" crossorigin="use-credentials">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">

    <?php echo $this->element('jsNamespace'); ?>

    <?php
        $renderConfig = 'frontend';
        if ($isMobile) {
            $renderConfig = 'frontend_mobile';
        }
        echo $this->element('renderCss', ['configs' => [$renderConfig]]);
        if ($isMobile) {
            echo $this->Html->css(['mobile-frontend-custom']);
        }
        if (Configure::read('appDb.FCS_CUSTOMER_CAN_SELECT_PICKUP_DAY')) {
            echo $this->Html->css(['customer-can-select-pickup-day']);
        }
        echo $this->element('customCssVars');
        echo $this->Html->css(['custom']);
        echo $this->element('layout/customHeader');
    ?>

</head>

<?php
    $bodyClasses = [
        Inflector::tableize($this->name),
        Inflector::singularize(Inflector::tableize($this->request->getParam('action')))
    ];

    if ($identity !== null && $identity->isSuperadmin()) {
        $bodyClasses[] = 'superadmin';
    }
?>
<body class="<?php echo implode(' ', $bodyClasses); ?>">
