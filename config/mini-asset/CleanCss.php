<?php
/**
 * MiniAsset
 * Copyright (c) Mark Story (http://mark-story.com)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Mark Story (http://mark-story.com)
 * @since     0.0.1
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace MiniAsset\Filter;

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
     * - `node_path` The path to the node_modules directory where cleancss is installed.
     */
    protected array $_settings = array(
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
    public function output(string $target, string $content): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'CLEANCSS');
        file_put_contents($tmpFile, $content);
        $cmd = $this->_settings['node'] . ' ' .
            $this->_settings['cleancss'] . $this->_settings['options'] . ' ' .
            escapeshellarg($tmpFile);
        $env = array('NODE_PATH' => $this->_settings['node_path']);
        $result = $this->_runCmd($cmd, '', $env);
        unlink($tmpFile);
        return $result;
    }
}