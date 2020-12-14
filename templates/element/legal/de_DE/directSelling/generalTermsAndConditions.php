<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
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

<p>1.1. Für alle gegenwärtigen und zukünftigen Leistungen, die der Betreiber im Rahmen seiner Internet-Dienstleistung unter der Domain <?php echo Configure::read('app.cakeServerName'); ?> für seine Nutzer erbringt (im Folgenden gemeinsam kurz: die Leistung), gelten ausschließlich die nachfolgenden Bedingungen.</p>

<p>1.2. Geschäftsbedingungen des Nutzers kommen nicht zur Anwendung.</p>

<h2>2. Leistungen und Entgelte</h2>

<p>2.1. Die vom Hersteller in der Plattform des Betreibers unter der Domain <?php echo Configure::read('app.cakeServerName'); ?> dargebotenen Waren und Leistungen sind eine unverbindliche Aufforderung des Herstellers an den Vertragspartner, ein verbindliches Anbot für die angebotenen Waren und Leistungen zu legen. Durch die Bestellung im Onlineshop legt der Vertragspartner ein solches verbindliches Anbot. <b>Ein Vertrag zwischen dem Vertragspartner und dem Hersteller kommt erst zustande, wenn der Lieferant mit der Leistungserbringung begonnen hat oder die Waren zur Abholung bereitgelegt hat.</b></p>

<p>2.2. Die auf der Website angegebenen Preise verstehen sich inklusive der gesetzlichen Steuer, jedoch exklusive der Verpackungs- und Versandkosten. Allfällige weitere Kosten (etwa Pfand) sind gesondert ausgewiesen.</p>

<p>2.3. Vor Abgabe der Vertragserklärung werden die Gesamtkosten dargestellt.</p>

<p>2.4. Der Warenwert inklusive Pfand

<?php if ($this->MyHtml->paymentIsCashless()) { ?>
wird vom vorhandenen Guthaben abgebucht
<?php } else { ?>
wird bei der Abholung bar bezahlt
<?php } ?>
. Der Lieferant ist berechtigt, vom Vertragspartner verschuldete und ihm erwachsene Schäden geltend zu machen, insbesondere die notwendigen Kosten zweckentsprechender außergerichtlicher Betreibungs- oder Einbringungsmaßnahmen, soweit diese in einem angemessenen Verhältnis zur betriebenen Forderung stehen.</p>

<h2>3. Eigentumsvorbehalt</h2>

<p>3.1. An den vom Hersteller gelieferten Produkten besteht Eigentumsvorbehalt des Herstellers bis zur Bezahlung aller Forderungen aus der Geschäftsverbindung zum Vertragspartner. Der Eigentumsvorbehalt bleibt auch für den Fall der Weiterveräußerung der gelieferten Produkte durch den Vertragspartner an einen Dritten bestehen. Der Vertragspartner tritt schon jetzt jene Forderungen an den Hersteller ab, die dem Vertragspartner aus dieser Weiterveräußerung an einen Dritten erwachsen. Daraus entstehende Gebühren gehen zu Lasten des Vertragspartners.</p>

<h2>4. Schadenersatz und Gewährleistung</h2>

<p>4.1. Für Schäden infolge schuldhafter Vertragsverletzung haftet der Lieferant bei eigenem Verschulden oder dem eines Erfüllungsgehilfen nur für Vorsatz oder grobe Fahrlässigkeit. Dies gilt nicht für Schäden an der Person.</p>

<h2>5. Rücktrittsrecht</h2>

<p>5.1. Informationen über das Rücktrittsrecht erhalten Sie <a href="<?php echo Configure::read('app.cakeServerName'); ?>/Informationen-ueber-Ruecktrittsrecht.pdf" target="_blank">hier</a>.</p>

<p>5.2. Der Lieferant wird von den alternativen Streitbeilegungsstellen "Online-Streitbeilegung" (https://webgate.ec.europa.eu/odr) sowie "Internetombudsmann" (www.ombudsmann.at) erfasst. Wenn der Vertragspartner ein Verbraucher ist, haben diese auf den genannten Plattformen die Möglichkeit, außergerichtliche Streitbeilegung durch eine unparteiische Schlichtungsstelle in Anspruch zu nehmen.</p>

<p>Die E-Mail-Adresse des Hersteller ergibt sich aus dem Impressum.</p>

<h2>6. Schlussbestimmungen</h2>

<p>6.1. Erfüllungsort für alle Leistungen aus diesem Vertrag ist der Standort des Herstellers.</p>

<p>6.2. Für Rechtsstreitigkeiten aus diesem Vertrag gilt ausschließlich österreichisches Recht. Die Anwendung des UN-Kaufrechts, der Verweisungsnormen des IPRG und der VO (EG) Nr. 593/2008 des Europäischen Parlaments und des Rates vom 17. Juni 2008 über das auf vertragliche Schuldverhältnisse anzuwendende Recht (Rom I-Verordnung) ist ausgeschlossen.</p>

<p>6.3. Änderungen oder Ergänzungen dieser AGB bedürfen zu ihrer Wirksamkeit der Schriftform.</p>
