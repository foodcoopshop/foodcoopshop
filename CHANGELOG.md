<h1 align="center">
  <a href="https://www.foodcoopshop.com"><img src="https://raw.githubusercontent.com/foodcoopshop/foodcoopshop/develop/webroot/files/images/logo.png" alt="FoodCoopShop"></a>
</h1>

# Changelog v4.x und v3.x

Das Format basiert auf [keepachangelog.com](http://keepachangelog.com) und verwendet [Semantic Versioning](http://semver.org/).

## Unreleased

### Neue Funktionen / Verbesserungen
- Bei Lagerprodukten mit Preis nach Gewicht kann der Lagerstand jetzt auch über das Gewicht berechnet werden, und nicht mehr ausschließlich über die Anzahl. Das ist vor allem für Lagerprodukte gedacht, die bei jeder Entnahme aus einem Großgebinde abgewogen werden (z.B. Nudeln, Reis, Äpfel usw.). [I#336](https://github.com/foodcoopshop/foodcoopshop/issues/336) / [PR#1036](https://github.com/foodcoopshop/foodcoopshop/pull/1036) / [PR#1029](https://github.com/foodcoopshop/foodcoopshop/pull/1029) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Unter "Bestellungen / Gruppiert nach Produkt" befindet sich jetzt eine neue Spalte mit dem summierten Gewicht. [PR#1021](https://github.com/foodcoopshop/foodcoopshop/pull/1021) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Lagerprodukte (Inventurliste), Mitglieder und Hersteller können nun als CSV-Datei exportiert werden. [I#321](https://github.com/foodcoopshop/foodcoopshop/issues/321) / [I#1010](https://github.com/foodcoopshop/foodcoopshop/issues/1010) / [PR#1022](https://github.com/foodcoopshop/foodcoopshop/pull/1022) / [PR#1025](https://github.com/foodcoopshop/foodcoopshop/pull/1025) / [PR#1028](https://github.com/foodcoopshop/foodcoopshop/pull/1028) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Neue Funktionen / Verbesserungen für Selbstbedienungs-Modus
- Beim Scannen von Barcodes mit Gewichtsinformation wird jetzt Produkt und Gewicht automatisch in den Warenkorb gelegt. [I#776](https://github.com/foodcoopshop/foodcoopshop/issues/776) / [PR#1019](https://github.com/foodcoopshop/foodcoopshop/pull/1019) / [PR#1029](https://github.com/foodcoopshop/foodcoopshop/pull/1029) <a href="https://github.com/pabneukistl"><img src="https://github.com/pabneukistl.png" width="20"></a>
- Verbessertes, breiteres Layout für SB-Modus. [I#1037](https://github.com/foodcoopshop/foodcoopshop/issues/1037) / [PR#1039](https://github.com/foodcoopshop/foodcoopshop/pull/1039) <a href="https://github.com/pabneukistl"><img src="https://github.com/pabneukistl.png" width="20"></a>
- Vereinfachter Login für den SB-Modus: Man kann sich jetzt mit einem Klick als vorkonfigurierter SB-Kunde einloggen (`app.selfServiceLoginCustomers`). [I#1031](https://github.com/foodcoopshop/foodcoopshop/issues/1031) / [PR#1035](https://github.com/foodcoopshop/foodcoopshop/pull/1035) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>


## v4.0

### Neue Funktionen / Verbesserungen
- Bei Ändern des Produkt-Preises können jetzt optional automatisch alle Preise von offenen (dh. nicht verrechneten) Bestellungen entsprechend angepasst werden. Das ist unter anderen praktisch bei Sammelbestellungen. [I#1006](https://github.com/foodcoopshop/foodcoopshop/issues/1006) / [I#281](https://github.com/foodcoopshop/foodcoopshop/issues/281) / [PR#1007](https://github.com/foodcoopshop/foodcoopshop/pull/1007) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Beim Ändern des Abholtages von bestellten Produkten gibt es jetzt die Option, dass die Produkte auf zukünftigen Bestelllisten erneut auftauchen. Das war bisher nicht so und hat immer wieder für Verwirrung gesorgt. [I#994](https://github.com/foodcoopshop/foodcoopshop/issues/994) / [PR#995](https://github.com/foodcoopshop/foodcoopshop/pull/995) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bei Herstellern mit vielen eingetragenen Lieferpausen war das linke Menü ziemlich überladen. Es werden jetzt nur die zwei bzw. drei nächsten Lieferpausen angezeigt. [I#952](https://github.com/foodcoopshop/foodcoopshop/issues/952) / [PR#955](https://github.com/foodcoopshop/foodcoopshop/pull/955) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Im Produkt-Admin und bei den Bestellungen herrscht jetzt mehr Übersichtlichkeit: Die zahlreichen Buttons unterhalb der Tabelle wurden innerhalb eines neuen Buttons "Aktionen" zusammengefasst. Dieser befindet sich rechts oben oberhalb der Tabelle. [I#957](https://github.com/foodcoopshop/foodcoopshop/issues/957) / [PR#958](https://github.com/foodcoopshop/foodcoopshop/pull/958) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bei den Bestellungen kann jetzt auch nach Bestell-Typ gefiltert werden (Vorbestellung / Sofort-Bestellung). Dazu im "Aktionen...-Menü" den Punkt "Nach Bestell-Typ filtern" auswählen. Der Bestell-Typ wird jetzt auch im Tooltip beim Bestellstatus angezeigt (links neben dem Stornier-Button). [I#957](https://github.com/foodcoopshop/foodcoopshop/issues/957) / [PR#958](https://github.com/foodcoopshop/foodcoopshop/pull/958) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- In den Einstellungen kann jetzt ein freier Text für die Homepage eingetragen werden. [I#959](https://github.com/foodcoopshop/foodcoopshop/issues/959) / [PR#960](https://github.com/foodcoopshop/foodcoopshop/pull/960) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Für Dorfläden: Wenn man als Einkaufspreis-User eingeloggt ist, wird jetzt im Tooltip beim Preis immer auch der Verkaufspreis angezeigt. [I#965](https://github.com/foodcoopshop/foodcoopshop/issues/965) / [PR#966](https://github.com/foodcoopshop/foodcoopshop/pull/966) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Wenn ein Mitglied eine offene Bestellung hat, die vor mehr als 6 Tagen getätigt wurde (z.B. Vorbestellung Fleisch), bekommt es jetzt auch eine Bestellerinnerung. [I#976](https://github.com/foodcoopshop/foodcoopshop/issues/976) / [PR#977](https://github.com/foodcoopshop/foodcoopshop/pull/977) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bild-Upload: WEBP-Format wird jetzt unterstützt. [PR#993](https://github.com/foodcoopshop/foodcoopshop/pull/993) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Beschränkung der Produkte auf maximal 100 pro Seite, dadurch bessere Übersicht und schnellere Ladezeit von z.B. der "Alle Produkte"-Seite. [PR#999](https://github.com/foodcoopshop/foodcoopshop/pull/999) / [PR#1001](https://github.com/foodcoopshop/foodcoopshop/pull/1001) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die [Software-Dokumentation](https://foodcoopshop.github.io/) wurde komplett neu strukturiert, upgedated und wird jetzt mit einem neuen Tool [Docusaurus](https://docusaurus.io/) generiert. [PR#9](https://github.com/foodcoopshop/foodcoopshop.github.io/pull/9) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Austausch von Komponenten / Software-Updates / Bugfixes
- Update auf CakePHP 5. [I#918](https://github.com/foodcoopshop/foodcoopshop/issues/918) / [PR#985](https://github.com/foodcoopshop/foodcoopshop/pull/985) / [I#917](https://github.com/foodcoopshop/foodcoopshop/issues/917) / [PR#987](https://github.com/foodcoopshop/foodcoopshop/pull/987) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Hello-Cash-API: Umstellung der Authentifizierung auf Token. [I#968](https://github.com/foodcoopshop/foodcoopshop/issues/968) / [PR#969](https://github.com/foodcoopshop/foodcoopshop/pull/969); Umstellung der Beleg-Erstellung aufgrund einer API-Änderung. [I#1002](https://github.com/foodcoopshop/foodcoopshop/issues/1002) / [PR#1003](https://github.com/foodcoopshop/foodcoopshop/pull/1003) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- [Neue Cookie-Banner-Library](https://github.com/Alex-D/Cookies-EU-banner). [I#962](https://github.com/foodcoopshop/foodcoopshop/issues/962) / [PR#963](https://github.com/foodcoopshop/foodcoopshop/pull/963) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- [Neuer WYSIWYG-Editor](https://xdsoft.net/jodit/). [I#858](https://github.com/foodcoopshop/foodcoopshop/issues/858) / [PR#967](https://github.com/foodcoopshop/foodcoopshop/pull/967) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bugfix für Dorfläden: Bei Steuersätzen mit Dezimalstellen (Deutschland) wurde der Steuersatz fehlerhafterweise ohne Kommastellen angezeigt. Berechnet wurde aber korrekt. [I#996](https://github.com/foodcoopshop/foodcoopshop/issues/996) / [PR#998](https://github.com/foodcoopshop/foodcoopshop/pull/998) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Steigerung der Code-Qualität
- Codecov wird jetzt als [Code-Coverage-Tool](https://codecov.io/gh/foodcoopshop/foodcoopshop) verwendet, der Report wird mit PCOV generiert. [I#641](https://github.com/foodcoopshop/foodcoopshop/issues/641) / [PR#964](https://github.com/foodcoopshop/foodcoopshop/pull/964) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Statische Code-Analyse mittels PHPStan: Verbesserung der Code-Qualität auf Level 1. [PR#971](https://github.com/foodcoopshop/foodcoopshop/pull/971) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Neue Beta-Funktionen
- Neue Produkt-Import-Funktion mittels CSV-Upload, [hier gehts zur Doku](https://foodcoopshop.github.io/de/produkt-import.html). [I#953](https://github.com/foodcoopshop/foodcoopshop/issues/953) / [PR#982](https://github.com/foodcoopshop/foodcoopshop/pull/982) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>


Datum: 29.03.2024 / [Mehr Details zum Release](https://github.com/orgs/foodcoopshop/projects/2) / [Alle Änderungen anzeigen](https://github.com/foodcoopshop/foodcoopshop/compare/v3.6.2...v4.0.0)


## v3.6.2

### Bug fix
- Bugfix für das Netzwerk-Modul

Datum: 27.11.2023 / [Alle Änderungen anzeigen](https://github.com/foodcoopshop/foodcoopshop/compare/v3.6.1...v3.6.2)

## v3.6.1

### Security fix
- Security-Fix für das Netzwerk-Modul [PR#972](https://github.com/foodcoopshop/foodcoopshop/pull/972)

Datum: 02.11.2023 / [Alle Änderungen anzeigen](https://github.com/foodcoopshop/foodcoopshop/compare/v3.6.0...v3.6.1)

## v3.6.0

### Neue Datenschutz-Funktionen
- Personenbezogene Mitglieder-Daten (Vorname, Nachname, E-Mail) können nun für bestimmte Hersteller systemweit **anonymisiert** werden. Im Sinne des Datenschutzes ist das für neu angelegte Hersteller auch die Standard-Einstellung. [I#767](https://github.com/foodcoopshop/foodcoopshop/issues/767) / [I#929](https://github.com/foodcoopshop/foodcoopshop/issues/929) / [PR#930](https://github.com/foodcoopshop/foodcoopshop/pull/930) / [PR#932](https://github.com/foodcoopshop/foodcoopshop/pull/932) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- E-Mail-Adressen, die über den Editor eingegeben wurden (z.B. auf Seiten oder in Blog-Artikeln), werden jetzt automatisch verlinkt und spamgeschützt angezeigt. [I#933](https://github.com/foodcoopshop/foodcoopshop/issues/933) / [PR#943](https://github.com/foodcoopshop/foodcoopshop/pull/934) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Neue Funktionen / Verbesserungen
- Ab sofort kann auch ein **dunkles Design / Dark Mode** verwendet werden. Das schont die Augen und spart bei OLED-Bildschirmen auch Strom. Einfach auf den Mond neben dem Anmelde-Link klicken. [I#873](https://github.com/foodcoopshop/foodcoopshop/issues/873) / [PR#913](https://github.com/foodcoopshop/foodcoopshop/pull/913) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Beim Anpassen des Gewichts für Produkte, die mehrmals bestellt wurden, ist jetzt ein **eingebauter Taschenrechner** hilfreich. Man kann z.B. "192+167" eintippen und das Ergebnis wird automatisch übernommen. Der Taschenrechner ist auch im Selbstbedienungs-Modus integriert. [PR#923](https://github.com/foodcoopshop/foodcoopshop/pull/923) / [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/449aedc29269cd1d74322c3f2239a4953d6500a5) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Superadmins können Tag und Uhrzeit der **Cronjobs** (z.B. die automatische Bestell-Erinnerung, Rechnungsversand) jetzt selber im Admin-Bereich (Homepage-Verwaltung / Einstellungen / neuer Tab "Cronjobs") ändern. [I#860](https://github.com/foodcoopshop/foodcoopshop/issues/860) / [PR#74](https://github.com/foodcoopshop/foodcoopshop/pull/874) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Überschriften aller Tabellen im Admin-Bereich bleiben jetzt beim Scrollen sichtbar (nicht in iOS). [PR#888](https://github.com/foodcoopshop/foodcoopshop/pull/888) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Im Produkt-Admin kann jetzt der Status (aktiviert, deaktiviert) von mehreren markierten Produkten auf einmal geändert werden. [I#895](https://github.com/foodcoopshop/foodcoopshop/issues/895) / [PR#897](https://github.com/foodcoopshop/foodcoopshop/pull/897) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Hersteller können ihre Bestellungen über [eine neue API](https://foodcoopshop.github.io/de/netzwerk-modul.html#6-api-zum-abrufen-von-bestellungen) abrufen und sie so im eigenen System weiterverarbeiten. [I#894](https://github.com/foodcoopshop/foodcoopshop/issues/894) / [PR#899](https://github.com/foodcoopshop/foodcoopshop/pull/899) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bei der Umsatzstatistik kann jetzt auch nach "letzte 12 bzw. 24 Monate" gefiltert werden. [I#904](https://github.com/foodcoopshop/foodcoopshop/issues/904) / [PR#908](https://github.com/foodcoopshop/foodcoopshop/pull/908) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bei allen Produkten kann jetzt die Anzahl der bestellten Einheiten für den nächsten Abholtag angezeigt werden. Das hilft, wenn bestimmte Gebindegrößen erreicht werden sollen. [I#909](https://github.com/foodcoopshop/foodcoopshop/issues/909) / [PR#910](https://github.com/foodcoopshop/foodcoopshop/pull/910) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Beim Umbuchen auf ein anderes Mitglied kann jetzt über eine Checkbox ausgewählt werden, ob die betroffenen Mitglieder per Mail benachrichtigt werden sollen. [I#920](https://github.com/foodcoopshop/foodcoopshop/issues/920) / [PR#921](https://github.com/foodcoopshop/foodcoopshop/pull/921) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bei einer herstellerbasierten Lieferpause sind Lagerprodukte jetzt weiterhin vorbestellbar. [PR#924](https://github.com/foodcoopshop/foodcoopshop/pull/924) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Alle Initiativen, die die Funktion "Rechnung an Kunden" aktiviert haben, werden jetzt mit einer E-Mail über einen Bestell-Kommentar benachrichtigt. [PR#926](https://github.com/foodcoopshop/foodcoopshop/pull/926) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Konfiguration "Freitag Bestellschluss / Samstag Bestelllisten-Versand / Donnerstag Abholtag" ist jetzt möglich. [I#866](https://github.com/foodcoopshop/foodcoopshop/issues/866) / [PR#867](https://github.com/foodcoopshop/foodcoopshop/pull/867) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### For developers
- New 🐳 [Docker Dev Environment](https://foodcoopshop.github.io/en/docker-dev-environment.html) and [Gitpod-Integration](https://gitpod.io/#https://github.com/foodcoopshop/foodcoopshop).  [I#871](https://github.com/foodcoopshop/foodcoopshop/issues/871) / [PR#876](https://github.com/foodcoopshop/foodcoopshop/pull/876) / [PR#879](https://github.com/foodcoopshop/foodcoopshop/pull/879) / [PR#881](https://github.com/foodcoopshop/foodcoopshop/pull/881) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Enable strict typing in every php file to improve code quality. [I#872](https://github.com/foodcoopshop/foodcoopshop/issues/872) / [PR#893](https://github.com/foodcoopshop/foodcoopshop/pull/893) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Replace CakePHP's deprecated classes: File, Folder, Shell. [I#902](https://github.com/foodcoopshop/foodcoopshop/issues/902) [I#906](https://github.com/foodcoopshop/foodcoopshop/issues/906) / [PR#905](https://github.com/foodcoopshop/foodcoopshop/pull/905) / [PR#907](https://github.com/foodcoopshop/foodcoopshop/pull/907) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Software ist jetzt kompatibel mit PHP 8.2. [I#915](https://github.com/foodcoopshop/foodcoopshop/issues/915) / [PR#916](https://github.com/foodcoopshop/foodcoopshop/pull/916) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Bugfixes
- Sofern Rechnungen an Kunden generiert wurden (Dorfladen) UND die Steuer für Pfand nicht auf 20% gesetzt war, wurde die Steuer von geliefertem Pfand trotzdem immer mit 20% berechnet. [I#940](https://github.com/foodcoopshop/foodcoopshop/issues/940) / [PR#941](https://github.com/foodcoopshop/foodcoopshop/pull/941) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bei Sofort-Bestellungen ist es ab sofort nicht mehr möglich, das erneute Laden von langsamen Seiten durch wiederholtes Klicken zu erzwingen. Diese Mehrfach-Requests haben nämlich den eingeloggten User und den User, für den bestellt wird, durcheinandergewirbelt. [I#945](https://github.com/foodcoopshop/foodcoopshop/issues/945) / [PR#946](https://github.com/foodcoopshop/foodcoopshop/pull/946) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

Datum: 12.04.2023 / [Mehr Details zum Release](https://github.com/orgs/foodcoopshop/projects/3) / [Alle Änderungen anzeigen](https://github.com/foodcoopshop/foodcoopshop/compare/v3.5.1...v3.6.0)

# v3.5.1

### Herzlichen Dank an alle beteiligten Personen
* <img src="https://github.com/mrothauer.png" width="20"> [mrothauer](https://github.com/mrothauer)
* <img src="https://github.com/pabneukistl.png" width="20"> [pabneukistl](https://github.com/pabneukistl)
* <img src="https://github.com/toblinga.png" width="20"> [toblinga](https://github.com/toblinga)

### Bugfixes
- Das mysteriöse Verschwinden von Produkt-Bildern ist gelöst. Ob deine Installation vom Bug betroffen ist, kannst du über diese Route feststellen: /admin/products/detectMissingProductImages. [I#824](https://github.com/foodcoopshop/foodcoopshop/issues/824)
- Die Umsatzsteuer wurde beim Abschließen des Warenkorb immer mit 0,00 € ausgewiesen. [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/cb876b9ae7d384c576f7bf60649af94260564414)
- Bei einer Barcode-Suche im Selbstbedienungs-Modus wurde immer nur die erste Variante direkt in den Warenkorb gelegt. [I#939](https://github.com/foodcoopshop/foodcoopshop/issues/939)
- In seltenen Fällen wurden bei der Barcode-Suche falsche Produkte angezeigt. [I#938](https://github.com/foodcoopshop/foodcoopshop/issues/938)
- Bei einer Sofort-Bestellung wurde bei Dorfläden unter bestimmten Umständen Verkaufspreis und Einkaufspreis in der Anzeige verwechselt. Bestell- und Rechnungsdaten waren korrekt, es war "lediglich" ein Anzeigeproblem. [I#937](https://github.com/foodcoopshop/foodcoopshop/issues/937)
- Anpassungen der Hello-Cash-API-Requests.

Datum: 28.02.2023 / [Alle Änderungen anzeigen](https://github.com/foodcoopshop/foodcoopshop/compare/v3.5.0...v3.5.1)

# v3.5.0

### Herzlichen Dank an alle beteiligten Personen
* <img src="https://github.com/MaxGitHubAccount.png" width="20"> [MaxGitHubAccount](https://github.com/MaxGitHubAccount)
* <img src="https://github.com/mrothauer.png" width="20"> [mrothauer](https://github.com/mrothauer)

### Änderung der Open-Source-Lizenz
- Ab v3.5 wird der FoodCoopShop unter der GNU Affero General Public License v3.0 (AGPL) veröffentlicht. [I#837](https://github.com/foodcoopshop/foodcoopshop/issues/837) / [PR#845](https://github.com/foodcoopshop/foodcoopshop/pull/845)

### Neue Funktionen / Verbesserungen
- Das User-Menü rechts oben ist jetzt aufgeräumter: Die Unterpunkte erscheinen bei Mouseover, bei Mitgliedern ist der verwirrende Button "Admin-Bereich" entfernt und die Admin-Menüstruktur ist bereits im Frontend abgebildet. [PR#836](https://github.com/foodcoopshop/foodcoopshop/pull/863) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Mitglieder und Hersteller können jetzt über ein eigenes Formular Feedback verfassen, welches dann öffentlich angezeigt wird. [Zur Online-Doku](https://foodcoopshop.github.io/de/user-feedback.html). [I#342](https://github.com/foodcoopshop/foodcoopshop/issues/342) / [PR#861](https://github.com/foodcoopshop/foodcoopshop/pull/861) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Verbesserte Darstellung der Blog-Artikel: Der Menüpunkt "Aktuelles" wandert in den Footer, heißt jetzt "Blog-Archiv"und zeigt nur noch jene Blog-Artikel an, die nicht auf der Startseite angezeigt werden. Im Blog-Archiv-Slider über den Produkten werden nur noch die Blog-Artikel von der Startseite angezeigt. [I#790](https://github.com/foodcoopshop/foodcoopshop/issues/790) / [PR#795](https://github.com/foodcoopshop/foodcoopshop/pull/795) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Software beinhaltet jetzt eine Newsletter-Funktion. [Zur Online-Doku](https://foodcoopshop.github.io/de/mitglieder.html#newsletter-funktion). [I#818](https://github.com/foodcoopshop/foodcoopshop/issues/818) / [PR#823](https://github.com/foodcoopshop/foodcoopshop/pull/823) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Nach Klick auf "Warenkorb anzeigen" wird automatisch zum Button "Zahlungspflichtig bestellen" gescrollt. Dieser konnte - vor allem wenn viele Produkte im Warenkorb sind - leicht übersehen werden. [PR#796](https://github.com/foodcoopshop/foodcoopshop/pull/796) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Schnellere Ladezeit des Produkt-Kataloges, speziell für Dorfladen-Online-Installationen. [I#763](https://github.com/foodcoopshop/foodcoopshop/issues/763) / [PR#813](https://github.com/foodcoopshop/foodcoopshop/pull/813) / [PR#815](https://github.com/foodcoopshop/foodcoopshop/pull/815) / [PR#822](https://github.com/foodcoopshop/foodcoopshop/pull/822) / [I#816](https://github.com/foodcoopshop/foodcoopshop/issues/816) / [PR#835](https://github.com/foodcoopshop/foodcoopshop/pull/835) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Der CSV-Upload für die Guthaben-Aufladungen unterstützt jetzt auch die Sparkasse und die GLS-Bank. [C#1](https://github.com/foodcoopshop/foodcoopshop/commit/a3fc47e23d489efb545f57a33f43fecbebf65ed2) [C#2](https://github.com/foodcoopshop/foodcoopshop/commit/513021e6a7acc3e5e38a61489d45ecc813ce8a1d) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Da der E-Mail-Versand (z.B. Verschicken der Bestellbestätigung) immer wieder komplizierte Probleme verursacht, werden ab sofort alle E-Mails in einer Queue gesammelt und über einen Hintergrund-Prozess (Worker) versendet. [I#842](https://github.com/foodcoopshop/foodcoopshop/issues/842) / [PR#843](https://github.com/foodcoopshop/foodcoopshop/pull/843)
- Bei der Produkt-Suche werden jetzt zuerst alle Produkte angezeigt, bei denen der Suchbegriff im Produktnamen vorkommt. Und dann jene mit dem Suchbegriff in der kurzen Beschreibung. [PR#852](https://github.com/foodcoopshop/foodcoopshop/pull/852)
- Bei monatlichen Cronjobs kann jetzt mit dem Wert "0" auch der Monatsletzte als Ausführtag angegeben werden. [I#854](https://github.com/foodcoopshop/foodcoopshop/issues/854) / [PR#859](https://github.com/foodcoopshop/foodcoopshop/pull/859)
- Die Umsatzsteuer in der Bestellbestätigung kann nun mittels `app.showTaxInOrderConfirmationEmail => false` ausgeblendet werden. [PR#869](https://github.com/foodcoopshop/foodcoopshop/pull/869) <a href="https://github.com/MaxGitHubAccount"><img src="https://github.com/MaxGitHubAccount.png" width="20"></a>

### Neue Funktionen für den [Einzelhandels-Modus](https://foodcoopshop.github.io/de/dorfladen-online.html)
- Kunden können sich jetzt auch als Firma (mit Firmennamen und optionaler Ansprechperson) registrieren. [I#819](https://github.com/foodcoopshop/foodcoopshop/issues/819) / [PR#821](https://github.com/foodcoopshop/foodcoopshop/pull/821) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Für Kunden-Rechnungen kann nun ein Präfix (max. 6 Zeichen) angegeben werden. Achtung: Nicht möglich bei Verwendung der Hello-Cash-API! [I#809](https://github.com/foodcoopshop/foodcoopshop/issues/809) / [PR#810](https://github.com/foodcoopshop/foodcoopshop/pull/810) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Berechnung der Umsatzsteuer auf den Rechnungen kann jetzt so eingestellt werden, dass die Gesamt-Steuer auf Basis der Gesamt-Netto-Erlöse berechnet wird. Das ist für pauschalierte Betriebe sinnvoll, die die Software auch zur Verrechnung verwenden. Achtung: Nicht möglich bei Verwendung der Hello-Cash-API! [I#807](https://github.com/foodcoopshop/foodcoopshop/issues/807) / [PR#812](https://github.com/foodcoopshop/foodcoopshop/pull/812) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Einkaufen zum Nullpreis ist jetzt auch für Dorfläden ohne aktivierter Einkaufspreis-Funktion möglich. So kann Lagerware, die erneut über das System verkauft wird, bequem vorbestellt werden. [I#829](https://github.com/foodcoopshop/foodcoopshop/issues/829) / [PR#830](https://github.com/foodcoopshop/foodcoopshop/pull/830) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die neue Umsatz- und Gewinnstatistik zeigt jetzt ausschließlich Netto-Werte an (Netto-Einkaufpreis, Netto-Gewinn) und zusätzulich den Gewinn-Aufschlag in %. [I#840](https://github.com/foodcoopshop/foodcoopshop/issues/840) / [PR#841](https://github.com/foodcoopshop/foodcoopshop/pull/841) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Bugfixes / Updates / Code cleaning
- Ab und zu wurde man während bzw. nach Abschluss einer Sofort- oder Lagerprodukt-Bestellung ausgeloggt. [I#832](https://github.com/foodcoopshop/foodcoopshop/issues/832) / [PR#831](https://github.com/foodcoopshop/foodcoopshop/pull/831) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Produkt, das von Lieferrhythmus "Sammelbestellung - mit abgelaufenem Bestellschlusss" auf "Lagerprodukt" umgestellt wurde, war nicht bestellbar. [I#774](https://github.com/foodcoopshop/foodcoopshop/issues/774) / [PR#801](https://github.com/foodcoopshop/foodcoopshop/pull/801) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Beim Stornieren wird die Anzahl nicht mehr erhöht, wenn das Produkt die Funktion "Standard-Anzahl pro Lieferrhythmus" verwendet. [I#838](https://github.com/foodcoopshop/foodcoopshop/issues/838) / [PR#839](https://github.com/foodcoopshop/foodcoopshop/pull/839) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Manchmal wurden über den Editor Base64-codierte Bilder eingefügt und gespeichert (z.B. Copy/Paste aus E-Mail-Client) und diese haben dann die Datenbank aufgebläht. Das ist jetzt systemweit unterbunden. [I#804](https://github.com/foodcoopshop/foodcoopshop/issues/804) / [PR#805](https://github.com/foodcoopshop/foodcoopshop/pull/805) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Support für MySQL 8.0 [I#803](https://github.com/foodcoopshop/foodcoopshop/issues/803) / [PR#806](https://github.com/foodcoopshop/foodcoopshop/pull/806) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bootstrap v5 und Bootstrap Select v1.14 Updates. [I#679](https://github.com/foodcoopshop/foodcoopshop/issues/679) / [PR#828](https://github.com/foodcoopshop/foodcoopshop/pull/828) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Überflüssige Einstellung app.isDepositPaymentCashless wurde entfernt. [I#827](https://github.com/foodcoopshop/foodcoopshop/issues/827) / [PR#834](https://github.com/foodcoopshop/foodcoopshop/pull/834) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Das Stundenabrechnungs-Modul wurde schon lange nicht mehr verwendet und deswegen entfernt. [I#848](https://github.com/foodcoopshop/foodcoopshop/issues/848) / [PR#849](https://github.com/foodcoopshop/foodcoopshop/pull/849) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Software ist jetzt kompatibel mit PHP 8.1. [I#750](https://github.com/foodcoopshop/foodcoopshop/issues/750) / [PR#851](https://github.com/foodcoopshop/foodcoopshop/pull/851) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Doppelte Aufrufe von lang andauerenden Cronjobs sind jetzt nicht mehr möglich. Das kam sehr selten vor, aber eben doch. [PR#853](https://github.com/foodcoopshop/foodcoopshop/pull/853) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Neuer CSS-Compressor: CssMin wurde durch CleanCss ersetzt. [PR#856](https://github.com/foodcoopshop/foodcoopshop/pull/856) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Fontawesome v6 Update. [PR#855](https://github.com/foodcoopshop/foodcoopshop/pull/855) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

Datum: 12.09.2022 / [Mehr Details zum Release](https://github.com/orgs/foodcoopshop/projects/4) / [Alle Änderungen anzeigen](https://github.com/foodcoopshop/foodcoopshop/compare/v3.4.2...v3.5.0)

[Zum Changelog von FoodCoopShop v3.0-v3.4](devtools/CHANGELOG-v3.md)
