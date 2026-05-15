<?php
declare(strict_types=1);

use Cake\Core\Configure;

$customCss = Configure::read('appDb.FCS_CUSTOM_CSS');
if ($customCss != '') {
    echo '<style id="fcs-custom-css">' . str_ireplace('</style', '<\/style', $customCss) . '</style>';
}