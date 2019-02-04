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

namespace App\Shell;

use Cake\Filesystem\File;
use Cake\Filesystem\Folder;

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
        
        $this->fontawesomePath = $this->vendorDir . DS . '@fortawesome' . DS . 'fontawesome-free' . DS;
        $this->jqueryBackstretchPath = $this->vendorDir . DS . 'jquery-backstretch' . DS;
        $this->jqueryUiPath = $this->vendorDir . DS . 'components-jqueryui' . DS;
        $this->owlCarouselPath = $this->vendorDir . DS . 'owl.carousel' . DS;
        $this->tooltipsterPath = $this->vendorDir . DS . 'tooltipster' . DS;
        
        $this->cleanOverheadFromDependencies();
        $this->copyAdaptedElfinderFiles();
        $this->copyJqueryUiImages();
        $this->copyFontawesomeFonts();
    }
    
    private function cleanOverheadFromDependencies()
    {
        
        $folder = new Folder();
        
        $folder->delete($this->jqueryBackstretchPath . DS . 'examples');
        $folder->delete($this->jqueryBackstretchPath . DS . 'test');
        
        $folder->delete($this->fontawesomePath . 'js');
                
        $file = new File($this->fontawesomePath . 'css' . DS . 'all.min.css');
        $file->delete();
        $file = new File($this->fontawesomePath . 'css' . DS . 'fontawesome.css');
        $file->delete();
        $file = new File($this->fontawesomePath . 'css' . DS . 'fontawesome.min.css');
        $file->delete();
        $file = new File($this->fontawesomePath . 'css' . DS . 'v4-shims.css');
        $file->delete();
        $file = new File($this->fontawesomePath . 'css' . DS . 'v4-shims.min.css');
        $file->delete();
        
        $activeThemeFolder = 'smoothness';
        $folder = new Folder($this->jqueryUiPath . 'themes' . DS . $activeThemeFolder);
        $folder->copy($this->jqueryUiPath . 'theme-backup');
        $folder->delete($this->jqueryUiPath . 'themes');
        $folder = new Folder($this->jqueryUiPath . 'theme-backup');
        $folder->move($this->jqueryUiPath . 'themes' . DS . $activeThemeFolder);
        
        $folder->delete($this->owlCarouselPath . 'docs');
        $folder->delete($this->owlCarouselPath . 'docs_src');
        $folder->delete($this->owlCarouselPath . 'src');
        $folder->delete($this->owlCarouselPath . 'test');
        
        $folder->delete($this->tooltipsterPath . 'demo');
        $folder->delete($this->tooltipsterPath . 'doc');
        
    }

    private function copyFontawesomeFonts()
    {
        $folder = new Folder($this->fontawesomePath . 'webfonts' . DS);
        $folder->copy(WWW_ROOT . 'webfonts');
        $this->out('Fontawesome fonts copied.');
    }

    /**
     * if asset compress is on (debug=0=)
     * images linked in css files have to be located in WEBROOT/cache
     */
    private function copyJqueryUiImages()
    {
        $folder = new Folder($this->jqueryUiPath . 'themes' . DS . 'smoothness' . DS . 'images' . DS);
        $folder->copy(WWW_ROOT . 'cache' . DS . 'images');
        $this->out('JQueryUI images copied.');
    }

    private function copyAdaptedElfinderFiles()
    {
        $elfinderConfigDir = ROOT . DS . 'config' . DS . 'elfinder' . DS;

        $adaptedFiles = [
            $elfinderConfigDir . 'elfinder.html',
            $elfinderConfigDir . 'php' . DS . 'connector.minimal.php'
        ];

        foreach ($adaptedFiles as $file) {
            copy($file, preg_replace('/config/', 'webroot' . DS . 'js', $file, 1));
            $this->out('Elfinder config file ' . $file . ' copied successfully.');
        }
    }
}
