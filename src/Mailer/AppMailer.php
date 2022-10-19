<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Lib\OutputFilter\OutputFilter;
use Cake\Core\Configure;
use Cake\Mailer\Mailer;
use Cake\Datasource\FactoryLocator;

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

    public function addToQueue(): void
    {

        $this->render();

        if (Configure::check('app.outputStringReplacements')) {
            $replacedSubject = OutputFilter::replace($this->getOriginalSubject(), Configure::read('app.outputStringReplacements'));
            $this->setSubject($replacedSubject);
            $replacedBody = OutputFilter::replace($this->getMessage()->getBodyHtml(), Configure::read('app.outputStringReplacements'));
            $this->getMessage()->setBodyHtml($replacedBody);
        }

        // due to queue_jobs.text field datatype "mediumtext" the limit of emails is 16MB (including attachments)
        $queuedJobs = FactoryLocator::get('Table')->get('Queue.QueuedJobs');
        $queuedJobs->createJob('AppEmail', [
            'settings' => $this->getMessage(),
            'afterRunParams' => $this->afterRunParams,
        ]);

    }

}
