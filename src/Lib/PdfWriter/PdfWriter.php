<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Lib\PdfWriter;
use Cake\Filesystem\Folder;
use Cake\Utility\Inflector;
use Cake\View\ViewBuilder;

abstract class PdfWriter
{
    
    protected $pdfLibrary;
    protected $data;
    protected $plugin = null;
    protected $filename = '';
    
    public function setPdfLibrary($pdfLibrary): PdfWriter
    {
        $this->pdfLibrary = $pdfLibrary;
        return $this;
    }
    
    public function setData($data): PdfWriter
    {
        $this->data = $data;
        return $this;
    }
    
    public function getFilename(): string
    {
        return $this->filename;
    }
    
    public function setFilename($filename): PdfWriter
    {
        $this->filename = $filename;
        return $this;
    }
    
    public function getData() {
        return $this->data;
    }
    
    private function getContent()
    {
        $this->data['pdf'] = $this->pdfLibrary;
        $viewBuilder = new ViewBuilder();
        if ($this->plugin) {
            $viewBuilder->setPlugin($this->plugin);
        }
        $reflect = new \ReflectionClass($this);
        $templateFile = Inflector::underscore(str_replace('PdfWriter', '', $reflect->getShortName()));
        $templateFile = DS . 'pdf' . DS . $templateFile;
        return $viewBuilder->setLayout('ajax')->build($this->getData())->render($templateFile);
    }
    
    private function setContent()
    {
        $this->pdfLibrary->html = $this->getContent();
    }
    
    public function writeInline()
    {
        $this->setContent();
        return $this->pdfLibrary->Output($this->getFilename(), 'I');
    }
    
    public function writeAttachment()
    {
        $this->setContent();
        return $this->pdfLibrary->Output(null, 'S');
    }

    /**
     * creates folder structure if not yet existings
     */
    public function writeFile()
    {
        $this->setContent();
        
        // pdf saved on server
        if (file_exists($this->getFilename())) {
            unlink($this->getFilename());
        }
        // assure that folder structure exists
        $dir = new Folder();
        $path = dirname($this->getFilename());
        $dir->create($path);
        $dir->chmod($path, 0755);
        
        return $this->pdfLibrary->Output($this->getFilename(), 'F');
    }
    
    public function writeHtml()
    {
        $this->setContent();
        return $this->pdfLibrary->getHtml();
    }
    
}