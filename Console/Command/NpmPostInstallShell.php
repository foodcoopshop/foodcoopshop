<?php

App::uses('Folder', 'Utility');

/**
 * NpmPostInstallShell
 *
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
class NpmPostInstallShell extends AppShell
{

    public $vendorDir;
    /**
     * do not call parent::main because db connection might not be available
     * @see AppShell::main()
     */
    public function main()
    {
        $this->vendorDir = WWW_ROOT . 'node_modules';
        $this->copyAdaptedKcfinderFiles();
        $this->copyJqueryUiImages();
        $this->copyBoostrapFonts();
        $this->copyFontawesomeFonts();
    }

    private function copyFontawesomeFonts()
    {
        $folder = new Folder($this->vendorDir . DS . 'font-awesome' . DS . 'fonts' . DS);
        $folder->copy(WWW_ROOT . 'fonts');
        $this->out('Fontawesome fonts copied.');
    }

    private function copyBoostrapFonts()
    {
        $folder = new Folder($this->vendorDir . DS . 'bootstrap' . DS . 'dist' . DS . 'fonts' . DS);
        $folder->copy(WWW_ROOT . 'fonts');
        $this->out('Boostrap fonts copied.');
    }

    /**
     * if asset compress is on (debug=0=)
     * images linked in css files have to be located in WEBROOT/cache
     */
    private function copyJqueryUiImages()
    {
        $folder = new Folder($this->vendorDir . DS . 'jquery-ui-dist' . DS . 'images' . DS);
        $folder->copy(WWW_ROOT . 'cache' . DS . 'images');
        $this->out('JQueryUI images copied.');
    }

    private function copyAdaptedKcfinderFiles()
    {
        $kcfinderConfigDir = APP . 'Config' . DS . 'kcfinder' . DS;

        $adaptedFiles = array(
            $kcfinderConfigDir . 'conf' . DS . 'config.php',
            $kcfinderConfigDir . 'core' . DS . 'bootstrap.php'
        );

        foreach ($adaptedFiles as $file) {
            copy($file, preg_replace('/Config/', 'webroot' . DS . 'node_modules', $file));
            $this->out('KCFinder config file ' . $file . ' copied successfully.');
        }
    }
}
