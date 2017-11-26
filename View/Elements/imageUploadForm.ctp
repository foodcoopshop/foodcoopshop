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
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>
<form data-object-id="<?php echo $id; ?>" id="mini-upload-form-<?php echo $id ?>" class="hide mini-upload-form" method="post" action="<?php echo $action; ?>" enctype="multipart/form-data">
        <p class="heading">
            <?php if ($imageExists) { ?>
                Bestehendes Bild überschreiben
            <?php } else { ?>
                Neues Bild hochladen
            <?php } ?>
        </p>
    <div class="drop">
        <?php if ($imageExists) { ?>
            <?php echo '<img class="existingImage loading" src="/img/ajax-loader.gif" data-src="' . $existingImageSrc . '" />'; ?>
        <?php } ?>
        <p style="font-size:13px;">Bitte nur selbstgemachte Bilder verwenden<br />(keine aus dem Internet).</p>
        <a class="upload-button">PC durchsuchen</a>
        <input type="file" name="upload"  accept="image/jpeg" />
    </div>
    <ul><!-- The file uploads will be shown here --></ul>
</form>	
