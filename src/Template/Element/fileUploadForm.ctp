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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>
<form data-object-id="<?php echo $id; ?>" id="mini-upload-form-file-<?php echo $id ?>" class="hide mini-upload-form" method="post" action="<?php echo $action; ?>" enctype="multipart/form-data">
        <p class="heading">
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
        <a class="upload-button"><?php echo __('Search_PC'); ?></a>
        <input type="file" name="upload"  accept="application/pdf" />
    </div>
    <ul><!-- The file uploads will be shown here --></ul>
</form> 
