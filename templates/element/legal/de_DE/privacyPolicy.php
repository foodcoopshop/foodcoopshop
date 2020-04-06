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
use App\Controller\Component\StringComponent;
use Cake\Core\Configure;

?>

<h1>Datenschutzerklärung</h1>

<h2>1. Verantwortlicher</h2>

<p>
    <?php
    if (Configure::read('appDb.FCS_PLATFORM_OWNER') != '') {
        echo Configure::read('appDb.FCS_PLATFORM_OWNER');
    } else {
        echo Configure::read('appDb.FCS_APP_NAME');
        echo '<br />' . $this->MyHtml->getAddressFromAddressConfiguration();
        echo '<br />' . __('Email').': ' . StringComponent::hideEmail($this->Html->getEmailFromAddressConfiguration());
        if (Configure::read('appDb.FCS_APP_ADDITIONAL_DATA') != '') {
            echo '<br />' . Configure::read('appDb.FCS_APP_ADDITIONAL_DATA');
        }
    }
    ?>
</p>

<h3>Kontaktdaten des Datenschutzbeauftragten</h3>

<p>Es wurde aufgrund der fehlenden Notwendigkeit kein Datenschutzbeauftragter bestellt.<br />
Betroffene können sich jederzeit direkt an den Verantwortlichen wenden.</p>

<h3>Zuständige Aufsichtsbehörde</h3>

<p>Österreichische Datenschutzbehörde<br />
Barichgasse 40-42<br />
A-1030 Wien<br />
Telefon: +43 1 52 152-0<br />
E-Mail: <a href="mailto:dsb@dsb.gv.at">dsb@dsb.gv.at</a><br />
Web: <a href="https://www.dsb.gv.at" target="_blank">https://www.dsb.gv.at</a>
</p>

<h2>2. Allgemeines zur Datenverarbeitung</h2>

<p>Wir legen großen Wert auf den Schutz Ihrer personenbezogenen Daten und haben entsprechend den gesetzlichen Vorgaben für die elektronische Kommunikation und den Datenschutz die vorgeschriebenen organisatorischen, vertraglichen und technischen Maßnahmen getroffen, um sicherzustellen, dass zufällige oder vorsätzliche Manipulationen, Verluste, Zerstörungen oder der Zugriff unberechtigter Personen verhindert werden.</p>

<p>Als Verantwortlicher im Sinne der EU-Datenschutz-Grundverordnung (kurz DSGVO) stellen wir sicher, dass Verarbeitungstätigkeiten Ihrer personenbezogen Daten nur für legitime Zwecke, auf Basis einer Rechtsgrundlage, im nötigen Umfang und für die erforderliche Dauer durchgeführt werden.</p>

<p>Wenn Sie der Meinung sind, dass die Verarbeitung Ihrer Daten gegen Datenschutzrecht verstößt oder Ihre Ansprüche sonst in einer Weise verletzt worden sind, können Sie Kontakt mit uns aufnehmen oder Beschwerde bei der Datenschutzbehörde einlegen.</p>

<h2>3. Datenverarbeitungen</h2>

<h3>3.1 Bereitstellung der Webseite und Erstellung von Logfiles</h3>
<p><b>3.1.1 Beschreibung und Umfang der Datenverarbeitung</b></p>
<p>Bei jedem Aufruf unserer Internetseite erfasst unser System automatisiert Daten und Informationen des aufrufenden Gerätes.</p>
<p>Folgende Daten werden hierbei erhoben:</p>
<ul>
  <li>Informationen über den Browsertyp und die verwendete Version</li>
  <li>Das Betriebssystem des aufrufenden Gerätes</li>
  <li>Die IP-Adresse des aufrufenden Gerätes</li>
  <li>Datum und Uhrzeit des Zugriffs</li>
  <li>Webseiten, von denen das System des Nutzers auf unsere Internetseite gelangt </li>
  <li>Adresse der abgerufenen Seite/Datei </li>
  <li>übertragene Datenmenge</li>
  <li>Webseiten, die vom System des Nutzers über unsere Webseite aufgerufen werden</li>
</ul>
<p>Die Daten werden ebenfalls in den Logfiles unseres Systems gespeichert. Eine Speicherung dieser Daten zusammen mit anderen personenbezogenen Daten des Nutzers findet nicht statt.</p>
<p><b>3.1.2 Rechtsgrundlage für die Datenverarbeitung</b></p>
<p>Rechtsgrundlage für die vorübergehende Speicherung der Daten und der Logfiles ist Art. 6 Abs. 1 lit. f DSGVO.</p>
<p><b>3.1.3 Zweck der Datenverarbeitung</b></p>
<p>Die vorübergehende Speicherung der IP-Adresse durch das System ist notwendig, um eine Auslieferung der Webseite an den Rechner des Nutzers zu ermöglichen. Hierfür muss die IP-Adresse des Nutzers für die Dauer der Sitzung gespeichert bleiben.</p>
<p>Die Speicherung in Logfiles erfolgt, um die Funktionsfähigkeit der Webseite sicherzustellen. Zudem dienen uns die Daten zur Optimierung der Webseite und zur Sicherstellung der Sicherheit unserer informationstechnischen Systeme. Eine personenbezogene Auswertung der Daten zu Marketingzwecken findet in diesem Zusammenhang nicht statt.</p>
<p>In diesen Zwecken liegt auch unser berechtigtes Interesse an der Datenverarbeitung nach Art. 6 Abs. 1 lit. f DSGVO.</p>
<p><b>3.1.4 Dauer der Speicherung</b></p>
<p>Die Daten werden gelöscht, sobald sie für die Erreichung des Zweckes ihrer Erhebung nicht mehr erforderlich sind. Die gespeicherten Logfiles werden nach spätestens 6 Monaten gelöscht. In begründeten Einzelfällen können Auszüge aus Logfiles länger gespeichert werden, soweit und solange dies zur Abwehr von Angriffen oder zur Durchsetzung von Rechtsansprüchen erforderlich ist.</p>
<p><b>3.1.5 Widerspruchs- und Beseitigungsmöglichkeit</b></p>
<p>Die Verarbeitung der Daten zur Bereitstellung der Webseite und die Speicherung der Daten in Logfiles ist für den Betrieb der Internetseite zwingend erforderlich. Es besteht folglich seitens des Nutzers keine Widerspruchsmöglichkeit.</p>

<h3>3.2 E-Mail-Kontakt</h3>
<p><b>3.2.1 Beschreibung und Umfang der Datenverarbeitung</b></p>
<p>Es ist eine Kontaktaufnahme über die auf der Webseite bereitgestellten E-Mail-Adressen möglich. In diesem Fall werden die mit der E-Mail übermittelten personenbezogenen Daten des Nutzers gespeichert.</p>
<p>Es erfolgt in diesem Zusammenhang keine Weitergabe der Daten an Dritte. Die Daten werden ausschließlich für die Verarbeitung der Konversation verwendet.</p>
<p><b>3.2.2 Rechtsgrundlage für die Datenverarbeitung</b></p>
<p>Rechtsgrundlage für die Verarbeitung der Daten, die im Zuge einer Übersendung einer E-Mail übermittelt werden, ist Art. 6 Abs. 1 lit. f DSGVO. Zielt der E-Mail-Kontakt auf den Abschluss eines Vertrages bzw. einer Mitgliedschaft ab, so ist eine zusätzliche Rechtsgrundlage für die Verarbeitung Art. 6 Abs. 1 lit. b DSGVO.</p>
<p><b>3.2.3 Zweck der Datenverarbeitung</b></p>
<p>Die Verarbeitung der personenbezogenen Daten aus der übermittelten E-Mail dient uns allein zur Bearbeitung der Kontaktaufnahme.</p>
<p>In diesem Zweck liegt auch unser berechtigtes Interesse an der Datenverarbeitung nach Art. 6 Abs. 1 lit. f DSGVO.</p>
<p><b>3.2.4 Dauer der Speicherung</b></p>
<p>Die Daten werden gelöscht, sobald sie für die Erreichung des Zweckes ihrer Erhebung nicht mehr erforderlich sind. Für die personenbezogenen Daten, die per E-Mail übersandt wurden, ist dies dann der Fall, wenn die jeweilige Konversation mit dem Nutzer beendet ist. Beendet ist die Konversation dann, wenn sich aus den Umständen entnehmen lässt, dass der betroffene Sachverhalt abschließend geklärt ist.</p>
<p><b>3.2.5 Widerspruchs- und Beseitigungsmöglichkeit</b></p>
<p>Nimmt der Nutzer per E-Mail Kontakt mit uns auf, so kann er der Speicherung seiner personenbezogenen Daten jederzeit widersprechen. In einem solchen Fall kann die Konversation nicht fortgeführt werden.</p>
<p>Alle personenbezogenen Daten, die im Zuge der Kontaktaufnahme gespeichert wurden, werden in diesem Fall gelöscht.</p>

<h3>3.3 Verwendung von Cookies</h3>
<p><b>3.3.1 Beschreibung und Umfang der Datenverarbeitung</b></p>
<p>Bei Cookies handelt es sich um Textdateien, die im Internetbrowser bzw. vom Internetbrowser auf dem Computersystem des Nutzers gespeichert werden. Ruft ein Nutzer eine Webseite auf, so kann ein Cookie auf dem Betriebssystem des Nutzers gespeichert werden. Dieses Cookie enthält eine charakteristische Zeichenfolge, die eine eindeutige Identifizierung des Browsers beim erneuten Aufrufen der Webseite ermöglicht.</p>
<p>Wir setzen eigene Cookies ein, um festzustellen, ob Sie eingeloggt sind (nur bei Mitgliedern).</p>
<p>Die auf diese Weise erhobenen Daten der Nutzer werden durch technische Vorkehrungen pseudonymisiert. Daher ist eine Zuordnung der Daten zum aufrufenden Nutzer nicht mehr möglich. Die Daten werden nicht gemeinsam mit sonstigen personenbezogenen Daten der Nutzer gespeichert.</p>
<p>Beim Login in den internen Bereich unserer Webseite wird vom Nutzer durch das Aktivieren der Checkbox „Angemeldet bleiben und Cookie akzeptieren“ eine Einwilligung zur Verwendung von Cookies zum Zweck der Log-In-Wiedererkennung eingeholt. In diesem Zusammenhang erfolgt auch ein Hinweis auf diese Datenschutzerklärung.</p>
<p><b>3.3.2 Rechtsgrundlage für die Datenverarbeitung</b></p>
<p>Die Rechtsgrundlage für die Verarbeitung personenbezogener Daten unter Verwendung von Cookies ist Art. 6 Abs. 1 lit. a DSGVO.</p>
<p><b>3.3.3 Zweck der Datenverarbeitung</b></p>
<p>Der Zweck der Verwendung von Cookies ist, die Nutzung von Webseiten für die Nutzer zu vereinfachen und sicherer zu gestalten. Einige Funktionen unserer Internetseite können ohne den Einsatz von Cookies nicht angeboten werden. Für diese ist es erforderlich, dass der Browser auch nach einem Seitenwechsel wiedererkannt wird.</p>
<p>Für folgende Anwendungen benötigen wir Cookies:</p>
<p>(1) Log-In-Wiedererkennung für den internen Bereich der Webseite</p>
<p>Die durch Cookies erhobenen Nutzerdaten werden nicht zur Erstellung von Nutzerprofilen verwendet.</p>
<p><b>3.3.4 Dauer der Speicherung, Widerspruchs- und Beseitigungsmöglichkeit</b></p>
<p>Cookies werden auf dem Rechner des Nutzers gespeichert und von diesem an unsere Seite übermittelt. Daher haben Sie als Nutzer auch die volle Kontrolle über die Verwendung von Cookies. Durch eine Änderung der Einstellungen in Ihrem Internetbrowser können Sie die Übertragung von Cookies deaktivieren oder einschränken. Bereits gespeicherte Cookies können jederzeit gelöscht werden. Dies kann auch automatisiert erfolgen. Werden Cookies für unsere Webseite deaktiviert, können möglicherweise nicht mehr alle Funktionen der Webseite vollumfänglich genutzt werden.</p>
<p><b>3.3.5 Liste der Cookies</b></p>
<p>Eigene Cookies:</p>
<ul>
	<li>
    remember_me<br />
    Dauer: 30 Tage<br />
    Zweck: Speichert, ob der Benutzer die Log-In-Wiedererkennungsfunktion "Angemeldet bleiben" verwendet.
    </li>
</ul>

<?php if (Configure::read('appDb.FCS_FOODCOOPS_MAP_ENABLED')) { ?>
<p>Fremde Cookies:</p>
<ul>
	<li>
    umap.openstreetmap.fr: anonymous_owner|211165, csrftoken<br />
    Dauer: 1 Jahr<br />
    Zweck: Zum Anzeigen der Open-Street-Map auf der Startseite
  </li>
</ul>
<?php } ?>

<h3>3.4 Verwaltung der Benutzer (Mitglieder) und der Bestellungen</h3>
<p><b>3.4.1 Beschreibung und Umfang der Datenverarbeitung</b></p>
<p>Zur Erfüllung des Vereinszwecks (Abwicklung von Sammelbestellungen) ist es notwendig, die Vereinsmitglieder als Benutzer in der Software anzulegen, damit diese dann ihre jeweiligen Bestellungen zusammenstellen und absenden können.</p>
<p>Folgende Daten werden hierbei verarbeitet:</p>
<ul>
  <li>Persönliche Daten (Vorname, Nachname)</li>
  <li>Adressdaten (PLZ, Ort, Straße, Hausnummer)</li>
  <li>Kontaktdaten (Telefonnummer, E-Mail-Adresse)</li>
  <li>Mitgliedsdaten (Mitgliedsnummer)</li>
  <li>Bestelldaten (Zeitpunkt der Bestellung, bestellte Artikel inkl. Menge, gewählte Hersteller, etc.)</li>
  <li>Finanzdaten (Rechnungsdaten, Guthaben und Überweisungen zu den Bestellungen, etc.)</li>
  <li>Bildaufzeichnungsdaten (optionales Profilfoto)</li>
  <li>Anwender-/Benutzerdaten (Benutzername, Hashwert des Passworts [nicht für andere auslesbar!], Benutzerrollen, Benutzerrechte, etc.)</li>
</ul>
<p><b>3.4.2 Rechtsgrundlage für die Datenverarbeitung</b></p>
<p>Rechtsgrundlage für die Verarbeitung der Daten im Zuge der Verwaltung der Bestellungen ist Art. 6 Abs. 1 lit. b DSGVO.</p>
<p><b>3.4.3 Zweck der Datenverarbeitung</b></p>
<p>Die Verarbeitung der personenbezogenen Daten dient uns zur Administration der Benutzer (Vereinsmitglieder) und der Hersteller, zur Erfüllung des Vereinszwecks (insbesondere die Abwicklung von Sammelbestellungen) und zur dafür notwendigen Kommunikation.</p>
<p><b>3.4.4 Dauer der Speicherung</b></p>
<p>Die personenbezogene Verarbeitung erfolgt für die Dauer der Mitgliedschaft plus 40 Monate nach Vertragsbeendigung (Beendigung der Mitgliedschaft).</p>
<p>Nach Ablauf dieser Frist wird jedenfalls der Personenbezug gelöscht.</p>
<p>Danach erfolgt eine personenbezogene Datenverarbeitung von Finanzdaten noch bis zum Ende der gesetzlichen Aufbewahrungspflicht (derzeit grundsätzlich 7 Jahre).</p>
<p><b>3.4.5 Widerspruchs- und Beseitigungsmöglichkeit</b></p>
<p>Die Verarbeitung der Daten zur Verwaltung der Mitglieder und der Bestellungen ist für die Vertragserfüllung (Vereinsmitgliedschaft) zwingend erforderlich. Es besteht folglich seitens des Nutzers keine Widerspruchsmöglichkeit außer der Beendigung der Vereinsmitgliedschaft.</p>
<p>Mitglieder können ihr Benutzerprofil der eingesetzten Software eigenständig löschen. Der Link dazu befindet sich nach dem Login unter dem Menüpunkt "Meine Daten".</p>
<p>Daten, die für eine etwaige weiteregehende Vertragserfüllung notwendig sind, werden dabei jedoch nicht gelöscht.</p>
<p>Daten, für welche eine gesetzliche Aufbewahrungsfrist gilt, werden dabei für die Dauer dieser Frist jedoch nicht gelöscht.</p>
