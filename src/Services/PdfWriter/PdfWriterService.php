<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\PdfWriter;

use Cake\Utility\Inflector;
use Cake\View\ViewBuilder;

abstract class PdfWriterService
{

    protected mixed $pdfLibrary;
    protected array $data = [];
    protected ?string $plugin = null;
    protected string $filename = '';
    public ?string $templateFile = null;

    public function setPdfLibrary($pdfLibrary): static
    {
        $this->pdfLibrary = $pdfLibrary;
        return $this;
    }

    public function setData($data): static
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename($filename): static
    {
        $this->filename = $filename;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    private function setContent(): void
    {
        $this->data['pdf'] = $this->pdfLibrary;
        $viewBuilder = new ViewBuilder();
        if ($this->plugin) {
            $viewBuilder->setPlugin($this->plugin);
        }
        if (is_null($this->templateFile)) {
            $reflect = new \ReflectionClass($this);
            $this->templateFile = Inflector::underscore(str_replace('PdfWriter', '', $reflect->getShortName()));
            $this->templateFile = DS . 'pdf' . DS . $this->templateFile;
        }
        $viewBuilder->setLayout('ajax')->setVars($this->getData())->setTemplate($this->templateFile)->build()->render();
    }

    public function writeInline(): string
    {
        $this->setContent();
        return $this->pdfLibrary->Output($this->getFilename(), 'I');
    }

    public function writeAttachment(): string
    {
        $this->setContent();
        return $this->pdfLibrary->Output('', 'S');
    }

    public function writeFile(): string
    {
        $this->setContent();

        // pdf saved on server
        if (file_exists($this->getFilename())) {
            unlink($this->getFilename());
        }

        $path = dirname($this->getFilename());
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        return $this->pdfLibrary->Output($this->getFilename(), 'F');
    }

    public function writeHtml(): string
    {
        $this->setContent();
        return $this->pdfLibrary->getHtml();
    }

}