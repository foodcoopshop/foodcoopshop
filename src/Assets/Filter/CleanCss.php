<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.5.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace MiniAsset\Filter;

use Cake\Log\Log;

/**
 * CleanCssFilter.
 *
 * Allows you to filter Css files through CleanCss. You need to install CleanCssCli with composer.
 */
class CleanCss extends AssetFilter
{

    /**
     * Settings for CleanCss
     *
     * - `node` Path to nodejs on your machine
     * - `node_path` The path to the node_modules directory where uglify is installed.
     */
    protected $_settings = array(
        'node' => '/usr/local/bin/node',
        'cleancss' => '/usr/local/bin/clean-css-cli/bin/cleancss',
        'node_path' => '/usr/local/lib/node_modules',
        'options' => '',
    );

    /**
     * Run `cleancss` against the output and compress it.
     *
     * @param  string $target   Name of the file being generated.
     * @param  string $content The uncompressed contents for $filename.
     * @return string Compressed contents.
     */
    public function output($target, $content)
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'CLEANCSS');
        file_put_contents($tmpFile, $content);
        $cmd = $this->_settings['node'] . ' ' . $this->_settings['cleancss'] . $this->_settings['options'] . ' ' . escapeshellarg($tmpFile);
        $env = array('NODE_PATH' => $this->_settings['node_path']);
        $result = $this->_runCmd($cmd, '', $env);
        unlink($tmpFile);
        return $result;
    }

}
