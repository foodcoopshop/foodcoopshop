<?php
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

    echo $this->element('localizedJavascript');
    echo $this->element('renderJs', ['configs' => ['frontend']]);

    if ($isMobile) {
        echo '<div class="is-mobile-detector"></div>';
        echo $this->Html->script(['/node_modules/slidebars/dist/slidebars.min']);

        // add script BEFORE all scripts that are loaded in views (block)
        echo $this->MyHtml->scriptBlock(
            $this->Html->wrapJavascriptBlock($mobileInitFunction),
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