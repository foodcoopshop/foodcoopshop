<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<h1>Allgemeine Geschäftsbedingungen</h1>

<h2>Betreiber der Plattform</h2>

<p><?php echo $this->Html->getPlatformOwnerForLegalTexts(); ?></p>

<h2>1. Geltung</h2>

<p>Für alle gegenwärtigen und zukünftigen Leistungen, die der Betreiber im Rahmen ihrer Internet-Dienstleistung unter der Domain <https://pabneukistl.at> für seine Nutzer erbringt (im Folgenden gemeinsam kurz: die Leistung), gelten ausschließlich die nachfolgenden Bedingungen.</p>

<p>1.2. Geschäftsbedingungen des Nutzers kommen nicht zur Anwendung.</p>

<h2>2. Leistungen und Entgelte</h2>

<p>2.1. Der Betreiber stellt dem Nutzer eine Plattform unentgeltlich zur Verfügung, auf der Waren und Dienstleistungen Dritter präsentiert werden. Diese dargebotenen Waren und Dienstleistungen sind eine unverbindliche Aufforderung an den Nutzer, ein verbindliches Anbot für die angebotenen Waren und Dienstleistungen zu legen. Ein verbindliches Angebot durch den Nutzer kann nur gelegt werden, wenn dieser sich auf der Plattform registriert hat und zustimmt, dass seine personenbezogenen Daten für die Durchführung von Bestellungen verwendet werden. Durch eine getätigte Bestellung legt der Nutzer ein solches verbindliches Anbot an den Betreiber. Ein Vertrag zwischen dem Nutzer und dem Betreiber kommt dann zustande, wenn der Drittanbieter mit der Leistungserbringung an den Betreiber begonnen hat oder die Waren zur Abholung bereitgelegt hat.</b></p>

<p>2.2. Der Vertrag über die Waren und Dienstleistungen kommt zwischen dem Nutzer und dem Betreiber zustande.</p>

<p>2.3. Die auf der Website angegebenen Preise verstehen sich inklusive der gesetzlichen Steuer, jedoch exklusive der Verpackungs- und Versandkosten. Allfällige weitere Kosten (etwa Pfand) sind gesondert ausgewiesen.</p>

<p>2.4. Vor Abgabe der Vertragserklärung werden die Gesamtkosten dargestellt.

<h2>3. Schadenersatz und Gewährleistung</h2>

<p>3.1. Die Nutzung der Plattform ist für die Nutzer kostenlos. Eine Haftung ist daher ausgeschlossen.</p>

<p>3.2. Für Schäden infolge schuldhafter Vertragsverletzung haftet der Betreiber bei eigenem Verschulden oder dem eines Erfüllungsgehilfen nur für Vorsatz oder grobe Fahrlässigkeit. Dies gilt nicht für Schäden an der Person.</p>

<h2>4. Rücktrittsrecht</h2>

<p>4.1. Der Nutzer schließt den Vertrag mit dem Betreiber. Der Nutzer erhält Informationen über das Rücktrittsrecht <a href="<?php echo Configure::read('app.cakeServerName'); ?>/Informationen-ueber-Ruecktrittsrecht.pdf" target="_blank">hier</a>. Grundsätzlich ist das Rücktrittsrecht für die Lieferung von Lebensmittel ausgeschlossen.</p>

<p>4.2. Der Betreiber wird von den alternativen Streitbeilegungsstellen "Online-Streitbeilegung" (https://webgate.ec.europa.eu/odr) sowie "Internetombudsmann" (www.ombudsmann.at) erfasst. Der Nutzer hat auf den genannten Plattformen die Möglichkeit, außergerichtliche Streitbeilegung durch eine unparteiische Schlichtungsstelle in Anspruch zu nehmen.</p>

<p>4.3. Die E-Mail-Adresse des Betreibers ergibt sich aus dessen Impressum.</p>

<h2>5. Schlussbestimmungen</h2>

<p>5.1. Erfüllungsort für alle Leistungen aus diesem Vertrag ist <?php echo $this->Html->getAddressFromAddressConfiguration(); ?>.</p>

<p>5.2. Für Rechtsstreitigkeiten aus diesem Vertrag gilt ausschließlich österreichisches Recht. Die Anwendung des UN-Kaufrechts, der Verweisungsnormen des IPRG und der VO (EG) Nr. 593/2008 des Europäischen Parlaments und des Rates vom 17. Juni 2008 über das auf vertragliche Schuldverhältnisse anzuwendende Recht (Rom I-Verordnung) ist ausgeschlossen.</p>

<p>5.3. Änderungen oder Ergänzungen dieser AGB bedürfen zu ihrer Wirksamkeit der Schriftform.</p>

<?php if ($this->Html->paymentIsCashless()) { ?>
<h2>6. Guthabenkonto</h2>

<p>6.1. Sämtliche Leistungen werden entweder von einem Guthabenkonto abgebucht oder bar verrechnet. Das Guthabenkonto wird vom Betreiber verwaltet, der Nutzer kann jederzeit auf das Guthabenkonto Beträge einbezahlen.
<?php if (Configure::read('appDb.FCS_MINIMAL_CREDIT_BALANCE') < 0) { ?>
    Auch bei einem negativen Kontostand sind weitere Bestellungen möglich.
<?php } ?>
</p>

<p>6.2. Der Nutzer hat jederzeit das Recht, die Auszahlung des Guthabenkontos zu verlangen, der Betreiber wird die Auszahlung innerhalb eines Monats auf das vom Nutzer bekanntgegebene Konto mittels Überweisung durchführen.</p>

<?php } ?>