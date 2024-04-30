<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 4.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\Csv\Writer;

use League\Csv\Writer;
use App\Services\Csv\Writer\CsvWriterServiceInterface;

abstract class BaseCsvWriterService implements CsvWriterServiceInterface
{

    public $writer;

	public $filename = 'export.csv';

	public function setFilename($filename) {
		$this->filename = $filename;
	}

	final public function render() {

		$this->writer = Writer::createFromFileObject(new \SplTempFileObject());

		$this->writer->setDelimiter(';');
		$this->writer->setOutputBOM(Writer::BOM_UTF8);

		$header = $this->getHeader();
		if (!empty($header)) {
			$this->writer->insertOne($header);
		}

		$records = $this->getRecords();

		if (!empty($records)) {
			$this->writer->insertAll($records);
		}
	}

	public function forceDownload($response) {
		$response = $response->withStringBody($this->writer->toString());
		$response = $response->withDownload($this->filename);

		return $response;
	}

}

?>