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
        echo $this->Html->script(['/node_modules/slidebars/dist/slidebars.min']);

        // add script BEFORE all scripts that are loaded in views (block)
        echo $this->MyHtml->scriptBlock(
            $this->Html->wrapJavascriptBlock($mobileInitFunction),
            ['inline' => true]
        );
    }

    echo $this->Html->script('/node_modules/bootstrap-select/dist/js/i18n/defaults-'.I18n::getLocale().'.js');

    $scripts = $this->fetch('script');
    if ($scripts != '') {
        echo $this->Html->wrapJavascriptBlock($scripts);
    }

?>

</body>
</html>