<?php
declare(strict_types=1);

namespace App\Mailer;

use Cake\Mailer\Mailer;
use Cake\Core\Configure;
use App\Services\OutputFilter\OutputFilterService;
use Cake\Mailer\Message;
use Cake\ORM\TableRegistry;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppMailer extends Mailer
{

    public $afterRunParams = [];

    public $customerAnonymizationForManufacturers = true;

    public function __construct($addBccBackupAddress = true)
    {
        parent::__construct(null);

        if ($addBccBackupAddress && Configure::read('appDb.FCS_BACKUP_EMAIL_ADDRESS_BCC') != '') {
            $bccRecipients = [];
            $bccs = explode(',', Configure::read('appDb.FCS_BACKUP_EMAIL_ADDRESS_BCC'));
            foreach ($bccs as $bcc) {
                $bccRecipients[] = $bcc;
            }
            $this->addBcc($bccRecipients);
        }
    }

    private function getAnonymizedCustomersAsStringReplacementArray(): array
    {

        $outputStringReplacements = [];

        if (!$this->customerAnonymizationForManufacturers) {
            return $outputStringReplacements;
        }

        foreach($this->getTo() as $email) {
            
            $addressManufacturersTable = TableRegistry::getTableLocator()->get('AddressManufacturers');
            $addressManufacturer = $addressManufacturersTable->find('all',
                conditions: [
                    'AddressManufacturers.email' => $email,
                    'AddressManufacturers.id_manufacturer > 0',
                ],
                contain: [
                    'Manufacturers',
                ],
            )->first();
            
            if (!empty($addressManufacturer) && $addressManufacturer->manufacturer->anonymize_customers) {
                $customersTable = TableRegistry::getTableLocator()->get('Customers');
                $customersTable->dropManufacturersInNextFind();
                $customers = $customersTable->find('all',
                    contain: [
                        'AddressCustomers', // to make exclude happen using dropManufacturersInNextFind
                    ],
                );
                foreach($customers as $customer) {
                    // eg. greeting is ALWAYS firstname - lastname (not respecting app.customerMainNamePart)
                    $replaceArrays = [
                        $customer->firstname . ' ' . $customer->lastname,
                        $customer->lastname . ' ' . $customer->firstname,
                    ];
                    foreach($replaceArrays as $customerName) {
                        $outputStringReplacements[$customerName] = Configure::read('app.htmlHelper')->anonymizeCustomerName($customerName, $customer->id_customer);
                    }
                }
            }

        }

        return $outputStringReplacements;
    }

    public function addToQueue(): void
    {

        $this->render();

        $outputStringReplacements = $this->getAnonymizedCustomersAsStringReplacementArray();
        if (!is_null(Configure::read('app.outputStringReplacements'))) {
            $outputStringReplacements = array_merge($outputStringReplacements, Configure::read('app.outputStringReplacements'));
        }

        if (!empty($outputStringReplacements)) {
            $replacedSubject = OutputFilterService::replace(
                /** @phpstan-ignore-next-line */
                $this->getOriginalSubject(),
                $outputStringReplacements,
            );
            $this->setSubject($replacedSubject);
            $replacedBody = OutputFilterService::replace($this->getMessage()->getBodyHtml(), $outputStringReplacements);
            $this->getMessage()->setBodyHtml($replacedBody);
        }

        // due to queue_jobs.text field datatype "mediumtext" the limit of emails is 16MB (including attachments)
        $queuedJobsTable = TableRegistry::getTableLocator()->get('Queue.QueuedJobs');
        $queuedJobsTable->createJob('AppEmail', [
            'class' => Message::class,
            'settings' => $this->getMessage()->__serialize(),
            'serialized' => true,
            'afterRunParams' => $this->afterRunParams,
        ]);

    }

}
