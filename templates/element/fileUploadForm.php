<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.3.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>
<form data-object-id="<?php echo $id; ?>" id="mini-upload-form-file-<?php echo $id ?>" class="hide mini-upload-form mini-upload-form-file" method="post" action="<?php echo $action; ?>" enctype="multipart/form-data">
    <input type="hidden" name="_csrfToken" autocomplete="off" value="<?php echo $this->request->getAttribute('csrfToken'); ?>">
    <p class="heading hide">
        <?php if ($fileUploadExists) { ?>
            <?php echo __('Replace_existing_file'); ?>
        <?php } else { ?>
            <?php echo __('Upload_new_file'); ?>
        <?php } ?>
    </p>
    <div class="drop">
        <?php if ($fileUploadExists) { ?>
            <?php echo '<a class="existingFile" target="_blank" href="' . $existingFileUploadSrc . '" />'.$fileName.'</a>'; ?>
        <?php } ?>
        <br /><br />
        <a class="upload-button"><?php echo __('Search_PC'); ?></a>
        <input type="file" name="upload"  accept="application/pdf" />
    </div>
    <ul><!-- The file uploads will be shown here --></ul>
</form>
