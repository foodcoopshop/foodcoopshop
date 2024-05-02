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
use Cake\Core\Configure;
use App\Services\OutputFilter\OutputFilterService;

abstract class BaseCsvWriterService implements CsvWriterServiceInterface
{

    public $writer;

	public $filename = 'export.csv';

	private $requestQueryParams = [];

	public function setFilename($filename) {
		if (Configure::check('app.outputStringReplacements')) {
            $filename = OutputFilterService::replace($filename, Configure::read('app.outputStringReplacements'));
        }
		$this->filename = $filename;
	}

	public function setRequestQueryParams($requestQueryParams) {
		$this->requestQueryParams = $requestQueryParams;
	}

	public function getRequestQuery($name, $default = null) {
		return $this->requestQueryParams[$name] ?? $default;
	}

	final public function getRequestQueryParams() {
		return $this->requestQueryParams;
	}

	final public function paginate($query, $params) {
		$results = $query->find('all', 
			order: $params['order'] ?? null,
		);
		return $results;
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

	final public function toString() {
		$result = $this->writer->toString();
		if (Configure::check('app.outputStringReplacements')) {
            $result = OutputFilterService::replace($result, Configure::read('app.outputStringReplacements'));
        }
		return $result;
	}

	public function forceDownload($response) {
		$response = $response->withStringBody($this->toString());
		$response = $response->withDownload($this->filename);

		return $response;
	}

}

?>