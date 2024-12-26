<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

?>
<h1>Nutzungsbedingungen für Hersteller</h1>

<h2>Betreiber der Plattform</h2>

<p><?php echo $this->Html->getPlatformOwnerForLegalTexts(); ?></p>

<h2>1. Geltung</h2>

<p>1.1. Für alle gegenwärtigen und zukünftigen Leistungen, die der Betreiber im Rahmen ihrer Internet-Dienstleistung unter der Domain <?php echo Configure::read('App.fullBaseUrl'); ?> für den Hersteller erbringt (im Folgenden gemeinsam kurz: die Leistung), gelten ausschließlich die nachfolgenden Bedingungen.</p>

<p>1.2. Geschäftsbedingungen des Herstellers kommen nicht zur Anwendung.</p>

<h2>2. Leistungen und Entgelte</h2>

<p>2.1. Der Betreiber stellt dem Hersteller eine Plattform zur Verfügung, auf der der Hersteller (nicht exklusiv) Waren und Dienstleistungen zum Verkauf anbietet. Dazu stellt der Betreiber dem Hersteller ein System (Hersteller-Bereich) zur Verfügung, mit dem der Hersteller die jeweiligen Produkte selbst eintragen kann.</p>

<p>2.2. Der Hersteller verpflichtet sich nur solche Waren und Dienstleistungen zum Verkauf anzubieten, welche in Österreich durch den Hersteller verkauft werden dürfen.</p>

<p>2.3. Der Hersteller nimmt zur Kenntnis, dass auf der Plattform mehrere Hersteller gleichartige oder identische Waren und Dienstleistungen anbieten können.</p>

<p>2.4. Die Auszahlung der Kaufpreise erfolgt am 11. des Folgemonats, sofern die Frist für die Ausübung eines allfälligen Rücktrittsrecht bereits abgelaufen ist.</p>

<p>2.5. Der Vertrag über die Waren und Dienstleistungen kommt ausschließlich zwischen dem Nutzer und dem jeweiligen Hersteller zustande, der Betreiber vermittelt lediglich den Vertrag.</p>

<p>2.6. Die auf der Website angegebenen Preise verstehen sich inklusive der gesetzlichen Steuer, jedoch exklusive der Verpackungs- und Versandkosten. Allfällige weitere Kosten (etwa Pfand) sind gesondert ausgewiesen.</p>

<p>2.7. Der Betreiber hat das Recht, Produkte, die der Hersteller zum Verkauf anbietet, ohne Angabe von Gründen von der Plattform zu nehmen. Der Hersteller hat keinen Rechtsanspruch auf die Veröffentlichung von Waren und Dienstleistungen auf der Plattform.</p>

<?php
$manufacturersTable = TableRegistry::getTableLocator()->get('Manufacturers');
$variableMemberFee = $manufacturersTable->getOptionVariableMemberFee($identity->manufacturer->variable_member_fee);
if (Configure::read('appDb.FCS_USE_VARIABLE_MEMBER_FEE') && $variableMemberFee > 0) { ?>
    <p>2.8. Für jede über die Plattform verkaufte Ware oder Dienstleistung steht dem Betreiber eine Provision in Höhe von <?php echo $variableMemberFee; ?>% des Umsatzes zuzüglich einer allfälligen Umsatzsteuer zu. Der Betreiber ist berechtigt, diesen Betrag unmittelbar vor der Auszahlung an den Hersteller einzubehalten. Der Hersteller bekommt die Rechnungen der verkauften Produkte (inklusive der einbehaltenen Beträge) automatisch per E-Mail.</p>
<?php } ?>

<h2>3. Schadenersatz und Gewährleistung</h2>

<p>3.1. Die Haftung des Betreibers ist ausgeschlossen. Für Schäden infolge schuldhafter Vertragsverletzung haftet der Betreiber bei eigenem Verschulden oder dem eines Erfüllungsgehilfen nur für Vorsatz oder grobe Fahrlässigkeit. Dies gilt nicht für Schäden an der Person.</p>

<p>3.2. Der Hersteller verpflichtet sich, die ihn betreffenden Daten vollständig und wahrheitsgemäß auszufüllen und aktuell zu halten.</p>

<p>3.3. Der Betreiber haftet nicht für Rechtstexte, die der Betreiber dem Hersteller zur Verfügung stellt. Die Zurverfügungstellung erfolgt unverbindlich.</p>

<p>3.4. Der Hersteller verpflichtet sich, nur solche Inhalte auf die Plattform zu stellen, für die er die für die Veröffentlichung auf der Plattform <b>notwendigen Rechte</b> erworben hat. Der Hersteller haftet daher für die von ihm bereitgestellten Inhalte und wird den Betreiber schad- und klaglos halten. <b>Dies gilt vor allem für Bilder, die für das Hersteller-Profil oder die Produkte verwendet werden können.</b></p>

<h2>4. Schlussbestimmungen</h2>

<p>4.1. Erfüllungsort für alle Leistungen aus diesem Vertrag ist der Sitz des Betreibers.</p>

<p>4.2. Als Gerichtsstand wird das für den Betreiber sachlich und örtlich zuständige Gericht vereinbart.</p>

<p>4.3. Für Rechtsstreitigkeiten aus diesem Vertrag gilt ausschließlich österreichisches Recht. Die Anwendung des UN-Kaufrechts, der Verweisungsnormen des IPRG und der VO (EG) Nr. 593/2008 des Europäischen Parlaments und des Rates vom 17. Juni 2008 über das auf vertragliche Schuldverhältnisse anzuwendende Recht (Rom I-Verordnung) ist ausgeschlossen.</p>

<p>4.4. Änderungen oder Ergänzungen dieser Nutzungsbedingungen bedürfen zu ihrer Wirksamkeit der Schriftform.</p>
