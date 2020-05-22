<?php
@session_start();
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
if (empty($_SESSION['Auth'])) {
    exit(0);
}

error_reporting(0); // Set E_ALL for debuging

// // To Enable(true) handling of PostScript files by ImageMagick
// // It is disabled by default as a countermeasure
// // of Ghostscript multiple -dSAFER sandbox bypass vulnerabilities
// // see https://www.kb.cert.org/vuls/id/332928
// define('ELFINDER_IMAGEMAGICK_PS', true);
// ===============================================

// // load composer autoload before load elFinder autoload If you need composer
// // You need to run the composer command in the php directory.
//require './vendor/autoload.php';

// // elFinder autoload
require './autoload.php';
// ===============================================

// // Enable FTP connector netmount
elFinder::$netDrivers['ftp'] = 'FTP';
// ===============================================

// // Required for Dropbox network mount
// // Installation by composer
// // `composer require kunalvarma05/dropbox-php-sdk` on php directory
// // Enable network mount
// elFinder::$netDrivers['dropbox2'] = 'Dropbox2';
// // Dropbox2 Netmount driver need next two settings. You can get at https://www.dropbox.com/developers/apps
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=dropbox2&host=1"
// define('ELFINDER_DROPBOX_APPKEY',    '');
// define('ELFINDER_DROPBOX_APPSECRET', '');
// ===============================================

// // Required for Google Drive network mount
// // Installation by composer
// // `composer require google/apiclient:^2.0` on php directory
// // Enable network mount
// elFinder::$netDrivers['googledrive'] = 'GoogleDrive';
// // GoogleDrive Netmount driver need next two settings. You can get at https://console.developers.google.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=googledrive&host=1"
// define('ELFINDER_GOOGLEDRIVE_CLIENTID',     '');
// define('ELFINDER_GOOGLEDRIVE_CLIENTSECRET', '');
// // Required case of without composer
// define('ELFINDER_GOOGLEDRIVE_GOOGLEAPICLIENT', '/path/to/google-api-php-client/vendor/autoload.php');
// ===============================================

// // Required for Google Drive network mount with Flysystem
// // Installation by composer
// // `composer require nao-pon/flysystem-google-drive:~1.1 nao-pon/elfinder-flysystem-driver-ext` on php directory
// // Enable network mount
// elFinder::$netDrivers['googledrive'] = 'FlysystemGoogleDriveNetmount';
// // GoogleDrive Netmount driver need next two settings. You can get at https://console.developers.google.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=googledrive&host=1"
// define('ELFINDER_GOOGLEDRIVE_CLIENTID',     '');
// define('ELFINDER_GOOGLEDRIVE_CLIENTSECRET', '');
// // And "php/.tmp" directory must exist and be writable by PHP.
// ===============================================

// // Required for One Drive network mount
// //  * cURL PHP extension required
// //  * HTTP server PATH_INFO supports required
// // Enable network mount
// elFinder::$netDrivers['onedrive'] = 'OneDrive';
// // GoogleDrive Netmount driver need next two settings. You can get at https://dev.onedrive.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL/netmount/onedrive/1"
// define('ELFINDER_ONEDRIVE_CLIENTID',     '');
// define('ELFINDER_ONEDRIVE_CLIENTSECRET', '');
// ===============================================

// // Required for Box network mount
// //  * cURL PHP extension required
// // Enable network mount
// elFinder::$netDrivers['box'] = 'Box';
// // Box Netmount driver need next two settings. You can get at https://developer.box.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL"
// define('ELFINDER_BOX_CLIENTID',     '');
// define('ELFINDER_BOX_CLIENTSECRET', '');
// ===============================================


// // Zoho Office Editor APIKey
// // https://www.zoho.com/docs/help/office-apis.html
// define('ELFINDER_ZOHO_OFFICE_APIKEY', '');
// ===============================================

// // Online converter (online-convert.com) APIKey
// // https://apiv2.online-convert.com/docs/getting_started/api_key.html
// define('ELFINDER_ONLINE_CONVERT_APIKEY', '');
// ===============================================

// // Zip Archive editor
// // Installation by composer
// // `composer require nao-pon/elfinder-flysystem-ziparchive-netmount` on php directory
// define('ELFINDER_DISABLE_ZIPEDITOR', false); // set `true` to disable zip editor
// ===============================================

/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from '.' (dot)
 *
 * @param  string    $attr    attribute name (read|write|locked|hidden)
 * @param  string    $path    absolute file path
 * @param  string    $data    value of volume option `accessControlData`
 * @param  object    $volume  elFinder volume driver object
 * @param  bool|null $isDir   path is directory (true: directory, false: file, null: unknown)
 * @param  string    $relpath file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume, $isDir, $relpath) {
    $basename = basename($path);
    return $basename[0] === '.'                  // if file/folder begins with '.' (dot)
    && strlen($relpath) !== 1           // but with out volume root
    ? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
    :  null;                                 // else elFinder decide it itself
}


// Documentation for connector options:
// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
$allowed = array(
    'image',
    'text/plain',
    'application/pdf',
    'application/msword',
    'application/vnd.ms-excel',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation'
);
$uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/files/kcfinder';
if (isset($_SESSION['ELFINDER']['uploadPath'])) {
    $uploadPath = $_SESSION['ELFINDER']['uploadPath'];
}
$uploadUrl = '/files/kcfinder/';
if (isset($_SESSION['ELFINDER']['uploadUrl'])) {
    $uploadUrl = $_SESSION['ELFINDER']['uploadUrl'];
}
mkdir($uploadPath, 0777, true);

$opts = array(
    'bind' => array(
        'upload.presave' => array(
            'Plugin.AutoResize.onUpLoadPreSave'
        )
    ),
    'plugin' => array(
        'AutoResize' => array(
            'enable' => true,
            'maxWidth'  => 717,
            'quality' => 95
        )
    ),
    // 'debug' => true,
    'roots' => array(
        // Items volume
        array(
            'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
            'path'          => $uploadPath, // path to files (REQUIRED)
            'URL'           => $uploadUrl, // URL to files (REQUIRED)
//            'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
            'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
            'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
            'uploadAllow'   => $allowed,// Mimetype `image` and `text/plain` allowed to upload
            'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
            'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
        ),
    )
);

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

