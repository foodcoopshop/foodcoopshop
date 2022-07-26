<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.4.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use App\Controller\Component\StringComponent;
?>

<div class="testimonial-quote group">
    <div class="quote-container">
        <blockquote>
            <p><?php echo StringComponent::nl2br2($quote); ?>”</p>
        </blockquote>
        <cite>
            <span><?php echo $metaData; ?></span>
        </cite>
    </div>
</div>

<hr style="margin: 40px auto 60px auto; opacity: .5;">
