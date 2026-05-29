<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

$headerPromo = $headerPromo ?? null;
$identity = $identity ?? null;
if ($headerPromo === null) {
    return;
}

$title = trim((string)($headerPromo->title ?? ''));
$lead = trim((string)($headerPromo->lead_text ?? ''));
$text = trim((string)($headerPromo->text ?? ''));
$primaryLabel = trim((string)($headerPromo->primary_label ?? ''));
$primaryHref = trim((string)($headerPromo->primary_href ?? ''));
$secondaryLabel = trim((string)($headerPromo->secondary_label ?? ''));
$secondaryHref = trim((string)($headerPromo->secondary_href ?? ''));

if ($title === '' && $lead === '' && $text === '' && $primaryLabel === '' && $secondaryLabel === '') {
    return;
}
?>
<div class="header-promo">
    <?php if ($identity === null) : ?>
        <div class="header-promo-inner">
            <?php if ($title !== '') : ?>
                <div class="header-promo-title"><?php echo h($title); ?></div>
            <?php endif; ?>
            <?php if ($lead !== '') : ?>
                <div class="header-promo-lead"><?php echo h($lead); ?></div>
            <?php endif; ?>
            <?php if ($text !== '') : ?>
                <div class="header-promo-text"><?php echo $text; ?></div>
            <?php endif; ?>
            <?php if (($primaryLabel !== '' && $primaryHref !== '') || ($secondaryLabel !== '' && $secondaryHref !== '')) : ?>
                <div class="header-promo-actions">
                    <?php if ($primaryLabel !== '' && $primaryHref !== '') : ?>
                        <a class="btn btn-success primary" href="<?php echo h($primaryHref); ?>"><?php echo h($primaryLabel); ?></a>
                    <?php endif; ?>
                    <?php if ($secondaryLabel !== '' && $secondaryHref !== '') : ?>
                        <a class="btn btn-outline-light secondary" href="<?php echo h($secondaryHref); ?>"><?php echo h($secondaryLabel); ?></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
</div>
