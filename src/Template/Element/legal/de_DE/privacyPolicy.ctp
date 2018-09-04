<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

?>
<h1>Datenschutzerklärung</h1>

<h2>1. Allgemeines</h2>

<p>1.1. Die Webseiten von <b>"<?php echo Configure::read('appDb.FCS_APP_NAME'); ?>"</b>, erreichbar unter der Domain <?php echo Configure::read('app.cakeServerName'); ?>,  werden von "
    <?php
    if (Configure::read('appDb.FCS_PLATFORM_OWNER') != '') {
        echo str_replace('<br />', ', ', Configure::read('appDb.FCS_PLATFORM_OWNER'));
    } else {
        echo Configure::read('appDb.FCS_APP_NAME');
        echo ', '.str_replace('<br />', ', ', $this->MyHtml->getAddressFromAddressConfiguration());
    }
    ?>
" als Diensteanbieter (im Folgenden "Website") betrieben. Medieninhaber und Herausgeber der Website ist der Betreiber.</p>

<p>1.2. Der Schutz und die Sicherheit Ihrer persönlichen Daten ist uns ein wichtiges Anliegen. Ihre Daten werden im Rahmen der gesetzlichen Vorschriften geschützt. Sie als Nutzer stimmen der Datenverarbeitung im Sinne dieser Erklärung zu.</p>

<p>1.3. Auf der Website werden automatisiert keine direkt personenbezogenen Daten ermittelt. Das heißt: Nur durch Ihre aktive Eingabe von (beispielsweise) Namen, Adresse etc. geben Sie der Website diese Daten bekannt.</p> 

<p>1.4. Die Website erstellt keine personenbezogenen Nutzerprofile.</p>

<h2>2. Gespeicherte Daten:</h2>

<p>2.1. Die Website speichert und verarbeitet folgende personenbezogene Daten, wenn Sie unsere Website besuchen:</p>

<p>2.1.1. Serverlogs: Die IP-Adresse (die letzten zwei Bytes sind anonymisiert) des anfragenden Computers, gemeinsam mit dem Datum, der Uhrzeit, der Anfrage, welche Datei angefragt wird (Name und URL), welche Datenmenge an Sie übertragen wird, eine Meldung, ob die Anfrage erfolgreich war, Erkennungsdaten des verwendeten Browsers und des verwendeten Betriebssystems, sowie die Website, von der der Zugriff erfolgte (sollte der Zugriff über einen Link erfolgen). Die Serverlogs werden gespeichert, um die Systemsicherheit prüfen zu können, die Website technisch administrieren, sowie das Angebot optimieren zu können. Diese Daten werden – sollte es einen Hackangriff gegeben haben – an die Strafverfolgungsbehörden weitergeben. Eine darüberhinausgehende Weitergabe an Dritte erfolgt nicht. Die Serverlogs werden für maximal sechs Monate gespeichert.</p> 

<p>2.1.2. Cookies: Cookies sind kleine Textdateien, die die Website auf Ihrem Computer speichert, um diesen wiederzuerkennen. Die in den Cookies enthaltenen Informationen werden verwendet, um festzustellen, ob Sie eingeloggt sind. Bei den meisten Web-Browsern werden Cookies automatisch akzeptiert. Durch Änderung der Einstellungen Ihres Browsers können Sie dies vermeiden. Sie können auf Ihrem PC gespeicherte Cookies jederzeit durch Löschen der temporären Internetdateien entfernen.</p> 

<p>Konkret werden folgende Cookies gespeichert über die jeweilige Session hinaus gespeichert:</p>

<p>remember_me: 6 Tage gültig, wird ausschließlich für die Login-Funktion "angemeldet bleiben" vewendet.</p> 

<p>2.2. Die Website speichert und verwendet darüber hinaus automatisiert keine Daten.</p>

<h2>3. Über die Website eingegebenen Daten</h2>

<p>Sollten Sie im Rahmen der Website Daten eingeben, verarbeiten wir Ihre Daten wie folgt:</p>

<p>3.1. Daten über die Bestellungen: Jene Daten, welche Sie im Rahmen Ihrer Anmeldung sowie im Rahmen der Bestellungen bekanntgegeben haben, werden ausschließlich für die Abwicklung der Bestellung verwendet. Diese Daten werden für die Dauer von sieben Jahren gespeichert.</p>

<h2>4. Widerspruchsrecht</h2>

<p>4.1. Jeder Nutzer der Website hat das Recht, die Speicherung seiner personenbezogenen Daten zu verweigern, der Nutzer und seine Daten werden in diesem Fall gelöscht, sofern keine gesetzliche Verpflichtung zur Speicherung der Daten besteht.</p>

<p>4.2. Mitglieder können ihr Profil eigenständig löschen. Der Link dazu befindet sich unter "Meine Daten".</p>

<h2>5. Datenverwendung</h2>

<p>5.1. Die Website verpflichtet sich zur Absicherung der Daten gegen unberechtigten Zugriff. Die Website speichert und nutzt die vom Nutzer eingegeben und übermittelten Daten ausschließlich im hier genannten Umfang. Mit Löschung des Nutzers werden sämtliche personenbezogenen Daten des Nutzers gelöscht.</p>
