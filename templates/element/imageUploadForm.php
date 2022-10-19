<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<form data-object-id="<?php echo $id; ?>" id="mini-upload-form-image-<?php echo $id ?>" class="hide mini-upload-form mini-upload-form-image" method="post" action="<?php echo $action; ?>" enctype="multipart/form-data">
    <p class="heading hide">
        <?php if ($imageExists) { ?>
            <?php echo __('Replace_existing_image'); ?>
        <?php } else { ?>
            <?php echo __('Upload_new_image'); ?>
        <?php } ?>
    </p>
    <div class="drop">
        <?php if ($imageExists) { ?>
            <?php echo '<img class="existingImage loading" src="/img/ajax-loader.gif" data-src="' . $existingImageSrc . '" />'; ?>
        <?php } ?>
        <p class="overlay-info"><?php echo __('Image_upload_legal_hint.'); ?></p>
        <a class="upload-button"><?php echo __('Search_PC'); ?></a>
        <input type="file" name="upload"  accept="<?php echo join(',', Configure::read('app.allowedImageMimeTypes')); ?>" />
    </div>
    <ul><!-- The file uploads will be shown here --></ul>
</form>
