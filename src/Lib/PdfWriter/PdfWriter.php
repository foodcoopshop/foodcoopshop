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
use Cake\View\ViewBuilder;

abstract class PdfWriter
{
    
    protected $pdfLibrary;
    protected $data;
    
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
    
    public function getData() {
        return $this->data;
    }
    
    private function getContent()
    {
        $this->data['pdf'] = $this->pdfLibrary;
        return (new ViewBuilder())->setLayout('ajax')->build($this->getData())->render($this->getTemplate());
    }
    
    public function writeAsInline($controller)
    {
        $this->pdfLibrary->html = $this->getContent();
        return $this->pdfLibrary->Output($this->getFilename(), 'I');
    }
    
    public function writeAsAttachment($controller)
    {
        $this->pdfLibrary->html = $this->getContent();
        return $this->pdfLibrary->Output(null, 'S');
    }

}