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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="initial-scale=1.0">
		<meta name="format-detection" content="telephone=no">
		<title><?php echo Configure::read('app.db_config_FCS_APP_NAME'); ?></title>
	</head>
	
	<table width="742" cellpadding="0" border="0" cellspacing="0" style="color:#000;font-family:Arial;">
    	<tbody>
    		<tr>
    			<td align="center" valign="middle" style="padding-bottom: 20px;">
    				<a href="<?php echo Configure::read('app.cakeServerName'); ?>">
    					<img src="<?php echo Configure::read('app.cakeServerName').'/files/images/logo.jpg'; ?>" width="150" />
    				</a>
    			</td>
    		</tr>
    		<tr>
    			<td style="border-bottom: 1px solid #d6d4d4;"></td>
    		</tr>
    		<tr>
    			<td style="padding-bottom: 20px;"></td>
    		</tr>
    		<tr>
    			<td><?php echo $this->fetch('content'); ?></td>
    		</tr>
    		<tr>
    			<td style="padding-top:20px;font-size:12px;">
    				Diese E-Mail wurde automatisch erstellt.<br /><br />
    				--<br />
    				<?php
        				echo Configure::read('app.db_config_FCS_APP_ADDRESS').'<br />';
        				echo '<a href="mailto:'.Configure::read('app.db_config_FCS_APP_EMAIL').'">'.Configure::read('app.db_config_FCS_APP_EMAIL').'</a><br />';
        				echo '<a href="'.Configure::read('app.cakeServerName').'">'.str_replace('http://', '', Configure::read('app.cakeServerName')).'</a>';
    				?>
    				<?php if (isset($appAuth) && $appAuth->loggedIn()) { ?>
    					<br /><br />Eingeloggt:
    					   <?php
    					       if ($appAuth->isManufacturer()) {
            					   echo $appAuth->getManufacturerName();
    				            } else {
    				                echo $appAuth->getUsername();
    				            }
    				       ?>
    				<?php } else { ?>
    				    <br />
    				<?php  } ?>
    				<?php if (Configure::read('app.db_config_FCS_SHOW_FOODCOOPSHOP_BACKLINK')) { ?>
    					<br />&copy; <?php echo date('Y'); ?> www.foodcoopshop.com
    				<?php } ?>
    			</td>
    		</tr>
    	</tbody>
	</table>
	
</html>
