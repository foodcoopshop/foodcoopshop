# Changelog v2

Das Format basiert auf [keepachangelog.com](http://keepachangelog.com) und verwendet [Semantic Versioning](http://semver.org/).

## v2.7.1 / 2019-12-18 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.7.0...v2.7.1)

### Bugfix
- Bei aktivierter Lieferpause haben Sofort-Bestellung und Selbstbedienungs-Modus nicht funktioniert. [PR#442](https://github.com/foodcoopshop/foodcoopshop/pull/442) / [I#441](https://github.com/foodcoopshop/foodcoopshop/issues/441) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / <a href="https://github.com/mantensteiner"><img src="https://github.com/mantensteiner.png" width="20"></a>

## v2.7.0 / 2019-12-17 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.6.2...v2.7.0)

### Herzlichen Dank an alle beteiligten Personen
* [AndreasEgger](https://github.com/AndreasEgger)
* [hasslerf](https://github.com/hasslerf)
* [mantensteiner](https://github.com/mantensteiner)
* [mrothauer](https://github.com/mrothauer)

### Neue Funktionen
- Der Selbstbedienungs-Modus für Lagerprodukte (als Alternative zur Sofort-Bestellung) ist nun fertig. Details in der [Online-Dokumentation](https://foodcoopshop.github.io/de/selbstbedienungs-modus). [PR#384](https://github.com/foodcoopshop/foodcoopshop/pull/384) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / <a href="https://github.com/AndreasEgger"><img src="https://github.com/AndreasEgger.png" width="20"></a>
- Die Statistik-Funktion wurde um ein Tortendiagramm und eine schöne Jahresübersicht erweitert. [PR#427](https://github.com/foodcoopshop/foodcoopshop/pull/427) / [I#426](https://github.com/foodcoopshop/foodcoopshop/issues/426) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Produkte können jetzt auch gelöscht werden. In der Produkt-Verwaltung die gewünschten Produkte anhaken und dann unten rechts auf "Ausgewählte Produkte löschen" klicken. [PR#422](https://github.com/foodcoopshop/foodcoopshop/pull/422) / [I#310](https://github.com/foodcoopshop/foodcoopshop/issues/310) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Eine Bestellpause für die gesamte Foodcoop (Feiertag) kann nun bequem im Vorhinein in den Einstellungen angegeben werden. Diese Funktion ersetzt die Einstellung "Ist die Bestell-Funktion aktiviert?". Über die Sofort-Bestellung kann nun auch während einer Bestellpause bestellt werden. [PR#419](https://github.com/foodcoopshop/foodcoopshop/pull/419) / [I#80](https://github.com/foodcoopshop/foodcoopshop/issues/80) / [I#418](https://github.com/foodcoopshop/foodcoopshop/issues/418) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Swipe-Funktion des Sliders auf der Startseite ist verbessert, es gibt nun Navigations-Pfeile statt der Punkte. [PR#416](https://github.com/foodcoopshop/foodcoopshop/pull/416) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Hersteller-Einstellung "Optimiert für Sammelbestellungen" ist seit der Einführung der Lieferrhythmen überflüssig und wurde entfernt. [I#434](https://github.com/foodcoopshop/foodcoopshop/issues/434) / [PR#436](https://github.com/foodcoopshop/foodcoopshop/pull/436) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Bugfixes
- Der Bestelllisten-Versand hat nicht funktioniert, wenn die Listen am gleichen Tag aber für unterschiedliche Liefertage generiert wurden. [I#408](https://github.com/foodcoopshop/foodcoopshop/issues/408) / [PR#437](https://github.com/foodcoopshop/foodcoopshop/pull/437) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bestellung abschließen hat im Firefox nicht funktioniert, wenn das Anhaken einer Checkbox vergessen wurde. [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/3e690448f5a1201a01a46aafbab07031f18545f3) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / <a href="https://github.com/hasslerf"><img src="https://github.com/hasslerf.png" width="20"></a>
- Der Produkt-Filter bei den Aktivitäten zeigt nun alle Änderungen von Bestellungen dieses Produktes. Bisher wurden nur Stornierungen angezeigt. Außerdem werden diese Aktivitäten nun auch für Hersteller angezeigt. [I#430](https://github.com/foodcoopshop/foodcoopshop/issues/430) / [PR#431](https://github.com/foodcoopshop/foodcoopshop/pull/431) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Escaping von Sonderzeichen war nicht ganz sauber. [I#424](https://github.com/foodcoopshop/foodcoopshop/issues/424) / [PR#425](https://github.com/foodcoopshop/foodcoopshop/pull/425) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bei bestimmten Datenbank-Feldern ist jetzt null erlaubt. [I#413](https://github.com/foodcoopshop/foodcoopshop/issues/413) / [PR#428](https://github.com/foodcoopshop/foodcoopshop/pull/428) <a href="https://github.com/mantensteiner"><img src="https://github.com/mantensteiner.png" width="20"></a> / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Frontend-Layout-Bugfix: Falls ein eigenens Hintergrundbild verwendet wird, scheint dieses nicht mehr zwischen Warenkorb und Content durch. [PR#416](https://github.com/foodcoopshop/foodcoopshop/pull/416) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Für Entwickler
- FoodCoopShop kann jetzt auch mittels Docker installiert bzw. gehostet werden: [https://github.com/foodcoopshop/foodcoopshop-docker](https://github.com/foodcoopshop/foodcoopshop-docker). [I#376](https://github.com/foodcoopshop/foodcoopshop/issues/376) / [PR#1](foodcoopshop/foodcoopshop-docker/pull/1) <a href="https://github.com/mantensteiner"><img src="https://github.com/mantensteiner.png" width="20"></a>


## v2.6.2 / 2019-10-10 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.6.1...v2.6.2)

### Herzlichen Dank an alle beteiligten Personen
* [mrothauer](https://github.com/mrothauer)

### Bugfixes
- Jeglicher User-Input wird nun mit HtmlPurifier auf Sicherheitsrisiken (XSS) überprüft und ggfs. bereinigt. <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

## v2.6.1 / 2019-09-30 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.6.0...v2.6.1)

### Herzlichen Dank an alle beteiligten Personen
* [mrothauer](https://github.com/mrothauer)
* [tomgrassmann](https://github.com/tomgrassmann)

### Neue Funktionen
- Bequemeres Bestellen: Auf den Hersteller- bzw. Kategorien-Seiten befinden sich nun ein Vor- und Zurück-Button. / [PR#403](https://github.com/foodcoopshop/foodcoopshop/pull/403) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Bugfixes
- Cross Site Scripting-Sicherheitslücke beim Such-Formular geschlossen. [I#405](https://github.com/foodcoopshop/foodcoopshop/issues/405) / [PR#407](https://github.com/foodcoopshop/foodcoopshop/pull/407) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> <a href="https://github.com/tomgrassmann"><img src="https://github.com/tomgrassmann.png" width="20"></a>
- Beim Aktivitäten-Log für Sofort-Bestellungen wird nun in der Spalte "Benutzer" der richtige Benutzer angezeigt. [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/7d1c935094e3cd8992c2282e84525db54af57dfa) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>


## v2.6.0 / 2019-09-17 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.5.4...v2.6.0)

### Herzlichen Dank an alle beteiligten Personen
* [AndreasEgger](https://github.com/AndreasEgger)
* [CH83](https://github.com/CH83)
* [markuskoban](https://github.com/markuskoban)
* [mrothauer](https://github.com/mrothauer)

### Neue Funktionen
- Die Standard-Einstellung des Feldes "Nur für Mitglieder" für neue Seiten, Hersteller und Blog-Artikel wurde auf "ja" geändert. [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/927d5c8466b2be6b79b34820986d976b4e2b5552) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Verbesserungen WYSIWYG-Editor: Nach Copy/Paste bleiben die Formatierungen vorhanden. Kursiv, zentriert und rechtsbündig ist jetzt möglich. <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> 
- Der Lieferrhythmus "Individuelles Datum" heißt jetzt "Sammelbestellung" (das klingt nicht so sperrig).
- Beim Produkt wird z. B. "in 3 Wochen und 2 Tagen" angezeigt, falls später als am kommenden (regulären) Liefertag geliefert wird. [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/1aa540999c4028cdee058d1876318f80ad85df59) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bei Bestelllisten und Rechnungen wird nun im Footer angezeigt, wann das PDF generiert wurde. / [PR#388](https://github.com/foodcoopshop/foodcoopshop/pull/388) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Verbesserter Spamschutz für das Registrierungsformular. [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/cd3990daf2f9cd185de4254d2e22825b01eecdc4) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Update der Datenschutzerklärung und Möglichkeit, darin die ZVR-Zahl anzugeben. [PR#397](https://github.com/foodcoopshop/foodcoopshop/pull/397) / [PR#399](https://github.com/foodcoopshop/foodcoopshop/pull/399) / <a href="https://github.com/CH83"><img src="https://github.com/CH83.png" width="20"></a> / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
 
### Bugfixes
- Das Erstellen eines neuen Produktes funktioniert jetzt auch auf dem Smartphone. [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/871e40e46343cd0f7a6e21ad4d8f3afdeb3e441d) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Der Bestell-Erinnerungs-Cronjob wird nicht mehr erneut aufgerufen, wenn die E-Mails über die Fallback-Configuration versendet werden. [PR#390](https://github.com/foodcoopshop/foodcoopshop/pull/390) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Für Entwickler
- Legacy-Passwort-Hasher wurde entfernt. [PR#380](https://github.com/foodcoopshop/foodcoopshop/pull/380) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Wie bei jedem Release zahlreiche Dependency-Updates und viele kleinere Bugfixes (CakePHP 3.8.x).


## v2.5.4 / 2019-07-30 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.5.3...v2.5.4)

### Bugfix
- Wenn der Lieferrhythmus auf "Individuelles Datum" gestellt war und das Produkt dann zum Lagerprodukt deklariert wurde, kam es manchmal zu Problemen beim Bestellen. [PR#396](https://github.com/foodcoopshop/foodcoopshop/pull/396) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / <a href="https://github.com/markuskoban"><img src="https://github.com/markuskoban.png" width="20"></a>

## v2.5.3 / 2019-07-08 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.5.2...v2.5.3)

### Bugfix
- Ändern der Anzahl bei Bestellabschluss hat falschen Preis ergeben. [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/d4678a11b1856f88e201796df8adb5eb2dd84350) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

## v2.5.2 / 2019-07-02 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.5.1...v2.5.2)

### Bugfix
- Das Netzwerk-Modul hat nicht mehr funktioniert. [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/60ac851f49d74bf5751d810c810db74ee79be3c6) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

## v2.5.1 / 2019-06-28 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.5.0...v2.5.1)

### Bugfix
- Die Summe wurde nicht richtig berechnet, wenn man mehrere Produkte auf einmal in den Warenkorb hinzufügt. [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/e968d77fa8e381172ce0da3febf2352a0c5ad68d) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

## v2.5.0 / 2019-06-17 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.4.1...v2.5.0)

### Herzlichen Dank an alle beteiligten Personen
* [AndreasEgger](https://github.com/AndreasEgger)
* [firerain-tgz](https://github.com/firerain-tgz)
* [mrothauer](https://github.com/mrothauer)
* [Speis-Vorchdorf](https://github.com/Speis-Vorchdorf)
* [TheFox](https://github.com/TheFox)

### Neue Funktionen
- Umsatzstatistik für Hersteller als Balkendiagramm. Für Admins sind auch die Gesamtumsätze aller Hersteller als Grafik sichtbar. [PR#350](https://github.com/foodcoopshop/foodcoopshop/pull/350) / [PR#365](https://github.com/foodcoopshop/foodcoopshop/pull/365) / [I#349](https://github.com/foodcoopshop/foodcoopshop/issues/349) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> 
- Hersteller können unter "Bestellungen / Bestelllisten" die Bestelllisten nun auch selbst Herunterladen, sollte der E-Mail-Versand einmal fehlschlagen (z.B. Mailbox voll). [PR#348](https://github.com/foodcoopshop/foodcoopshop/pull/348) / [I#316](https://github.com/foodcoopshop/foodcoopshop/issues/316) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> 
- Mitglieder können ab sofort ein Profilbild hochladen, welches in der Mitglieder-Liste mittels Mouseover angezeigt wird. [PR#345](https://github.com/foodcoopshop/foodcoopshop/pull/345) / [I#337](https://github.com/foodcoopshop/foodcoopshop/issues/337) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> 
- Bestellte Produkte können jetzt auf ein anderes Mitglied umgebucht werden. [PR#341](https://github.com/foodcoopshop/foodcoopshop/pull/341) / [I#298](https://github.com/foodcoopshop/foodcoopshop/issues/298) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> 
- Für Sofort-Bestellungen wird jetzt ein eigener Warenkorb verwendet. Ab und zu waren bereits Produkte im Warenkorb und man musste diese vor der Sofort-Bestellung zuerst löschen. [PR#344](https://github.com/foodcoopshop/foodcoopshop/pull/344) / [I#160](https://github.com/foodcoopshop/foodcoopshop/issues/160) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> <a href="https://github.com/Speis-Vorchdorf"><img src="https://github.com/Speis-Vorchdorf.png" width="20"></a>
- Übersetzung auf Polnisch / Translation into Polish. [PR#354](https://github.com/foodcoopshop/foodcoopshop/pull/354) <a href="https://github.com/firerain-tgz"><img src="https://github.com/firerain-tgz.png" width="20"></a> 

### Neue Beta-Funktionen
- Selbstbedienungs-Modus für Lagerprodukte - Details in der [Online-Dokumentation](https://foodcoopshop.github.io/de/selbstbedienungs-modus). [PR#355](https://github.com/foodcoopshop/foodcoopshop/pull/355) / [PR#359](https://github.com/foodcoopshop/foodcoopshop/pull/359) / [PR#361](https://github.com/foodcoopshop/foodcoopshop/pull/361) / [PR#366](https://github.com/foodcoopshop/foodcoopshop/pull/366) / [I#338](https://github.com/foodcoopshop/foodcoopshop/issues/338) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / <a href="https://github.com/AndreasEgger"><img src="https://github.com/AndreasEgger.png" width="20"></a>

### Für Entwickler
- Integration des statischen Quellcode-Analyse-Tools [PHPStan](https://github.com/phpstan/phpstan) in Travis-CI. [PR#363](https://github.com/foodcoopshop/foodcoopshop/pull/363) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Migrations können nun viel einfacher in die bestehenden Datenbank-Dumps übertragen werden. [PR#361](https://github.com/foodcoopshop/foodcoopshop/pull/361) / [I#246](https://github.com/foodcoopshop/foodcoopshop/issues/246) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Wie bei jedem Release zahlreiche Dependency-Updates und viele kleinere Bugfixes (PHPUnit 8).


## v2.4.1 / 2019-03-27 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.4.0...v2.4.1)

### Bugfix
- Der individuelle Lieferrhythmus hat unter speziellen Umständen nicht richtig funktioniert. [PR#343](https://github.com/foodcoopshop/foodcoopshop/pull/343) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

## v2.4.0 / 2019-03-19 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.3.0...v2.4.0)

### Herzlichen Dank an alle beteiligten Personen 
* [AndreasEgger](https://github.com/AndreasEgger)
* [mrothauer](https://github.com/mrothauer)
* [paroga](https://github.com/paroga)
* [SigiK](https://github.com/SigiK)
* [Speis-Vorchdorf](https://github.com/Speis-Vorchdorf)

### Neue Funktionen
- Einloggen ins Forum der Österreichischen Foodcoops (https://forum.foodcoops.at) ist jetzt über das Einloggen im FoodCoopShop möglich (Single-Sign-On). Die Funktion ist natürlich auch für andere Discourse-Foren verwendbar. [PR#306](https://github.com/foodcoopshop/foodcoopshop/pull/306) / [I#164](https://github.com/foodcoopshop/foodcoopshop/issues/164) <a href="https://github.com/paroga"><img src="https://github.com/paroga.png" width="20"></a> 
- Der Lieferrhythmus kann jetzt auch für mehrere Produkte gleichzeitig geändert werden. Dazu die Produkte links mit den Häkchen auswählen und unten auf den Button klicken. [PR#304](https://github.com/foodcoopshop/foodcoopshop/pull/304) / [I#284](https://github.com/foodcoopshop/foodcoopshop/issues/284) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> 
- Das Aktivieren einer Lieferpause ist nur mehr möglich, wenn für den gewünschten Liefertag noch keine Bestellungen vorliegen. Diese können jedoch storniert werden. [PR#303](https://github.com/foodcoopshop/foodcoopshop/pull/303) / [I#297](https://github.com/foodcoopshop/foodcoopshop/issues/297) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> 
- Möglichkeit, bei Rechnungen Lagerprodukte explizit nicht anzuführen. Außerdem ist die Übersicht für den Finanzverantwortlichen nun unter "Aktivitäten" zu finden und es ist möglich, die Rechnung herunterzuladen. / [PR#291](https://github.com/foodcoopshop/foodcoopshop/pull/291) / [PR#294](https://github.com/foodcoopshop/foodcoopshop/pull/294) / [I#289](https://github.com/foodcoopshop/foodcoopshop/issues/289) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / <a href="https://github.com/AndreasEgger"><img src="https://github.com/AndreasEgger.png" width="20"></a>
- Bei Sofort-Bestellungen können nun ausschließlich Lagerprodukte angezeigt werden. Bei "normalen" Bestellungen kann eingestellt werden, dass Lagerprodukte zwar angezeigt aber nicht bestellt werden können.  / [PR#325](https://github.com/foodcoopshop/foodcoopshop/pull/325) / [I#322](https://github.com/foodcoopshop/foodcoopshop/issues/322) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / <a href="https://github.com/AndreasEgger"><img src="https://github.com/AndreasEgger.png" width="20"></a>
- Im Admin-Bereich werden die E-Mail-Adressen von Herstellern und Mitgliedern nach Klick auf den Button automatisch in die Zwischenablage kopiert. / [PR#287](https://github.com/foodcoopshop/foodcoopshop/pull/287)  / [I#254](https://github.com/foodcoopshop/foodcoopshop/issues/254) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Auf der Startseite wird jetzt eine Map aller Initiativen, die mit FoodCoopShop arbeiten, angezeigt. In den Einstellungen kann man das deaktivieren. [PR#320](https://github.com/foodcoopshop/foodcoopshop/pull/320) / [I#319](https://github.com/foodcoopshop/foodcoopshop/issues/319) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / <a href="https://github.com/AndreasEgger"><img src="https://github.com/AndreasEgger.png" width="20"></a>
- Hersteller können für Produkte mit wöchentlichen oder monatlichen Lieferrhythmen nun einen individuellen Wochentag als **Bestellschluss (z.B. Montag Mitternacht)** angeben. Die Bestelllisten werden automatisch am Tag darauf versendet. Bei einem individuellen Datum als Liefertag kann ein fixes Datum für den automatischen Bestelllisten-Versand angegeben werden. [Dokumentation ist aktualisiert - Punkte 5a und 5b](https://foodcoopshop.github.io/de/bestellabwicklung) [PR#331](https://github.com/foodcoopshop/foodcoopshop/pull/331) / [I#323](https://github.com/foodcoopshop/foodcoopshop/issues/319) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / <a href="https://github.com/AndreasEgger"><img src="https://github.com/AndreasEgger.png" width="20"></a>
- Update auf die Version 5 der Icon-Library Fontawesome und Vereinheitlichung der Icons. / [PR#305](https://github.com/foodcoopshop/foodcoopshop/pull/305)  / [I#204](https://github.com/foodcoopshop/foodcoopshop/issues/204) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die E-Mail-Empfänger der Registrierungs-Benachrichtigungen können nun bequem über den Admin-Bereich (Einstellungen) geändert werden. / [PR#332](https://github.com/foodcoopshop/foodcoopshop/pull/332) / [I#312](https://github.com/foodcoopshop/foodcoopshop/issues/312) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Bugfixes / Performance-Verbesserungen
- Bei manchen MySQL-Versionen bzw. -Konfigurationen hat das Hinzufügen eines neuen Herstellers nicht funktioniert. [PR#301](https://github.com/foodcoopshop/foodcoopshop/pull/301) / [I#288](https://github.com/foodcoopshop/foodcoopshop/issues/288) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> <a href="https://github.com/SigiK"><img src="https://github.com/SigiK.png" width="20"></a>
- Produkte, die vor dem "Bestellbar-bis"-Datum in den Warenkorb gelegt wurden, konnten nach Ablauf dieses Datums auch bestellt werden. / [PR#292](https://github.com/foodcoopshop/foodcoopshop/pull/292) / [I#290](https://github.com/foodcoopshop/foodcoopshop/issues/290) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> <a href="https://github.com/Speis-Vorchdorf"><img src="https://github.com/Speis-Vorchdorf.png" width="20"></a>
- Der Punkt "Bestellungen" im Admin-Bereich benötigt jetzt viel weniger Arbeitsspeicher und lädt schneller. / [PR#309](https://github.com/foodcoopshop/foodcoopshop/pull/309) / [I#308](https://github.com/foodcoopshop/foodcoopshop/issues/308) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Größe des Releases beträgt nur noch ca. 28M (statt ca. 47M) / [PR#318](https://github.com/foodcoopshop/foodcoopshop/pull/318) / [I#317](https://github.com/foodcoopshop/foodcoopshop/issues/317) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Umsatzsteuer wurde bei Verwendung der Funktionen "Anzahl anpassen" bzw. "Gewicht anpassen" teilweise falsch berechnet. Der Bruttobetrag war aber immer korrekt. / [PR#335](https://github.com/foodcoopshop/foodcoopshop/pull/335) / [I#334](https://github.com/foodcoopshop/foodcoopshop/issues/334) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Für Entwickler
- Der in die Jahre gekommene SimpleBrowser wurde mit dem in CakePHP integrierten HttpClient ersetzt. Die Tests laufen viel schneller. / [I#314](https://github.com/foodcoopshop/foodcoopshop/issues/314) / [PR#315](https://github.com/foodcoopshop/foodcoopshop/pull/315) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Wie bei jedem Release zahlreiche Dependency-Updates und viele kleinere Bugfixes: CakePHP 3.7 [PR#295](https://github.com/foodcoopshop/foodcoopshop/pull/295)

## v2.3.0 / 2018-12-02 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.2.1...v2.3.0)

### Herzlichen Dank an alle beteiligten Personen
* [mrothauer](https://github.com/mrothauer)

### Neue Funktionen
- Möglichkeit zur farblichen Individualisierung des Frontends (app.customFrontendColorTheme). / [PR#277](https://github.com/foodcoopshop/foodcoopshop/pull/277) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Hersteller können in ihrem Profil ab sofort eigene AGB hochladen, ansonsten gelten wie bisher die Standard-AGB. [PR#282](https://github.com/foodcoopshop/foodcoopshop/pull/282) / [I#89](https://github.com/foodcoopshop/foodcoopshop/issues/89) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Falls in einer Rechnung mehr als einen Steuersatz vorkommt, wird jetzt eine eigene Tabelle mit den Summen je Steuersatz angezeigt. / [PR#283](https://github.com/foodcoopshop/foodcoopshop/pull/283) / [I#104](https://github.com/foodcoopshop/foodcoopshop/issues/104) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Verwaltung und Steuerung der Cronjobs passiert jetzt in der Datenbank. Und sollten Cronjobs mal fehlschlagen, werden sie ab sofort automatisch nachgeholt. [Dokumentation auf Englisch](https://foodcoopshop.github.io/en/cronjobs) / [PR#275](https://github.com/foodcoopshop/foodcoopshop/pull/275) / [I#36](https://github.com/foodcoopshop/foodcoopshop/issues/36) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Das Netzwerk-Modul wurde aufgepeppt: Bilder, Preis nach Gewicht, Lieferrhythmen und die erweiterte Lagerstandsverwaltung können jetzt auch synchronisiert werden. Außerdem wurde es auf Englisch übersetzt. / [PR#274](https://github.com/foodcoopshop/foodcoopshop/pull/274) / [I#190](https://github.com/foodcoopshop/foodcoopshop/issues/190) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Einfachere Passwort-Vergessen-Funktion. / [PR#272](https://github.com/foodcoopshop/foodcoopshop/pull/272) / [I#271](https://github.com/foodcoopshop/foodcoopshop/issues/271) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Bugfixes
- Vereinzelt hat auf den Rechnungen der Nettopreis um ein paar Cent nicht gestimmt (Rundungsfehler). Der Gesamtbetrag war aber immer korrekt. / [PR#278](https://github.com/foodcoopshop/foodcoopshop/pull/278) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Für Entwickler
- Wie bei jedem Release zahlreiche Dependency-Updates und viele kleinere Bugfixes
- Update auf Bootstrap v4.1.3 und Bootstrap Select v1.13.2 / [PR#276](https://github.com/foodcoopshop/foodcoopshop/pull/276) / [I#217](https://github.com/foodcoopshop/foodcoopshop/issues/217) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Verwenden des Default-Passwort-Hashings von CakePHP3. / [PR#269](https://github.com/foodcoopshop/foodcoopshop/pull/269) / [I#268](https://github.com/foodcoopshop/foodcoopshop/issues/268) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>


## v2.2.1 / 2018-09-26 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.2.0...v2.2.1)

### Herzlichen Dank an alle beteiligten Personen
* [mrothauer](https://github.com/mrothauer)

### Bugfixes
- Zahlreiche kleinere Bugfixes


## v2.2.0 / 2018-09-21 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.1.2...v2.2.0)

### Herzlichen Dank an alle beteiligten Personen
* [firerain-tgz](https://github.com/firerain-tgz)
* [hasslerf](https://github.com/hasslerf)
* [mrothauer](https://github.com/mrothauer)
* [robbinjanssen](https://github.com/robbinjanssen)
* [tantelisl](https://github.com/tantelisl)
* [wintersim](https://github.com/wintersim)

### Neue Funktionen
- Endlich gibt es neben dem wöchentlichen auch 2-wöchige und monatliche Lieferrhythmen! Außerdem sind Sammelbestellungen, Sofort-Bestellungen und die Lieferpause wesentlich einfacher zu bedienen. [Bitte unbedingt das hier lesen!](https://foodcoopshop.github.io/de/bestellabwicklung) / [PR#262](https://github.com/foodcoopshop/foodcoopshop/pull/262) / [I#83](https://github.com/foodcoopshop/foodcoopshop/issues/83) / [PR#251](https://github.com/foodcoopshop/foodcoopshop/pull/251) / [I#92](https://github.com/foodcoopshop/foodcoopshop/issues/92) / [I#211](https://github.com/foodcoopshop/foodcoopshop/issues/211) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Erweiterte Lagerstandverwaltung: Hersteller und deren Ansprechpersonen werden jetzt bei geringem Lagerstand automatisch benachrichtigt. [Mehr dazu hier (ganz unten)](https://foodcoopshop.github.io/de/produkte). / [PR#261](https://github.com/foodcoopshop/foodcoopshop/pull/261) / [I#70](https://github.com/foodcoopshop/foodcoopshop/issues/70) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bei Blogartikel von Herstellern ohne Bild wird jetzt das Hersteller Profilbild angezeigt / [PR#255](https://github.com/foodcoopshop/foodcoopshop/pull/255) / <a href="https://github.com/wintersim"><img src="https://github.com/wintersim.png" width="20"></a> / <a href="https://github.com/tantelisl"><img src="https://github.com/tantelisl.png" width="20"></a>
- Diverse Anpassungen für das Stundenabrechnungs-Modul. / [PR#265](https://github.com/foodcoopshop/foodcoopshop/pull/265) / [I#264](https://github.com/foodcoopshop/foodcoopshop/issues/264) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Zahlreiche kleinere Optimierungen und Verbesserungen sowie Sicherheitsupdates

### Für Entwickler
- Tabellen für die Produkt-Verwaltung sind jetzt stark vereinfacht. / [PR#247](https://github.com/foodcoopshop/foodcoopshop/pull/247) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Timeouts bei Travis-CI gefixt. / [PR#256](https://github.com/foodcoopshop/foodcoopshop/pull/256) / <a href="https://github.com/robbinjanssen"><img src="https://github.com/robbinjanssen.png" width="20"></a>


## v2.1.2 2018-07-12 / [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.1.1...v2.1.2)

### Bugfixes
- Link zur Blog-Detail-Seite unter "Aktuelles" hat nicht funktioniert. / [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/9dd71ba0ee482ec9ba4bb05d842ac1a6ce585c10) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

## v2.1.1 2018-07-09 / [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.1.0...v2.1.1)

### Bugfixes
- Eingabe von Kommazahlen hat bei Pfand- und Guthaben-Eintragungen im Edge-Browser nicht funktioniert. / [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/a0898ba33408a478189be52af2eea7558329b199) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>


## v2.1.0 2018-07-06 / [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.0.2...v2.1.0)

### Herzlichen Dank an alle beteiligten Personen
* [EvaSee](https://github.com/EvaSee)
* [mrothauer](https://github.com/mrothauer)
* [SaibotP](https://github.com/SaibotP)
* [tantelisl](https://github.com/tantelisl)
* [vmvbruck](https://github.com/vmvbruck)

### Neue Funktionen
- Für Produkte, bei denen das Gewicht zum Zeitpunkt der Bestellung noch nicht feststeht, kann der **Preis jetzt auch nach Gewicht** eingeben werden. (z. B. 25 € / kg oder 10 € / 100 g). Bei der Eingabe des tatsächlichen Gewichts wird der Preis dann automatisch berechnet. / [PR#223](https://github.com/foodcoopshop/foodcoopshop/pull/223) / [I#14](https://github.com/foodcoopshop/foodcoopshop/issues/14) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Hersteller können ihre Produkte auch in Stunden anbieten, abgerechnet wird über das neue [Stundenabrechnungs-Modul](https://foodcoopshop.github.io/de/stundenabrechnungs-modul). Beta-Version! / [PR#213](https://github.com/foodcoopshop/foodcoopshop/pull/213) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / <a href="https://github.com/SaibotP"><img src="https://github.com/SaibotP.png" width="20"></a>
- Mitglieder können **vergangene Bestellungen in ihren Warenkorb laden**. In der Bestellerinnerungs-Email befindet sich ein Link, mit dem die letzte Bestellung automatisch in den Warenkorb geladen werden kann. / [PR#215](https://github.com/foodcoopshop/foodcoopshop/pull/215) / [I#74](https://github.com/foodcoopshop/foodcoopshop/issues/74) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / <a href="https://github.com/tantelisl"><img src="https://github.com/tantelisl.png" width="20"></a>
- Mitglieder können jetzt in ihrem Profil ihr eigenes Mitgliedskonto löschen, Superadmins können das auch für andere Mitglieder tun. [PR#226](https://github.com/foodcoopshop/foodcoopshop/pull/226) / [I#29](https://github.com/foodcoopshop/foodcoopshop/issues/29) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Produktpreise können jetzt auch für Nicht-Mitglieder angezeigt werden. / [PR#231](https://github.com/foodcoopshop/foodcoopshop/pull/231) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#210](https://github.com/foodcoopshop/foodcoopshop/issues/210) / <a href="https://github.com/vmvbruck"><img src="https://github.com/vmvbruck.png" width="20"></a>
- Anpassungen für die DSGVO bzw. Rechtliches: Allergenliste im Footer; Schriftart nicht mehr vom Google-Server laden. [PR#227](https://github.com/foodcoopshop/foodcoopshop/pull/227) / [I#225](https://github.com/foodcoopshop/foodcoopshop/issues/225) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Für Superadmins gibt es eine unter *Homepage-Verwaltung / Finanzberichte* eine neue Übersicht **Guthaben- und Pfand-Saldo** um einen Überblick über das Guthaben-System und Pfand-Zahlungen zu behalten.
- Verbesserungen Produkt-Admin: Bild und Beschreibung werden jetzt als Mouseover angezeigt. / [PR#229](https://github.com/foodcoopshop/foodcoopshop/pull/229) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#145](https://github.com/foodcoopshop/foodcoopshop/issues/145) / <a href="https://github.com/EvaSee"><img src="https://github.com/EvaSee.png" width="20"></a> / Nach dem Deaktivieren wird nicht mehr automatisch runtergescrollt. / [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/f190414f5be1cfbba86fbf26100e08f9aff0dda2) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#146](https://github.com/foodcoopshop/foodcoopshop/issues/146) / <a href="https://github.com/EvaSee"><img src="https://github.com/EvaSee.png" width="20"></a>
- Software-Hilfe komplett ausgelagert in die [Online-Dokumentation](https://foodcoopshop.github.io), Hilfe-Button hervorgehoben. [PR#234](https://github.com/foodcoopshop/foodcoopshop/pull/234) / [I#9](https://github.com/foodcoopshop/foodcoopshop/issues/9) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- FoodCoopShop ist jetzt auch auf Englisch verfügbar. [PR#234](https://github.com/foodcoopshop/foodcoopshop/pull/234) / [PR#235](https://github.com/foodcoopshop/foodcoopshop/pull/235) / [PR#238](https://github.com/foodcoopshop/foodcoopshop/pull/238) / [I#9](https://github.com/foodcoopshop/foodcoopshop/issues/9) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Für Entwickler
- [elFinder](https://studio-42.github.io/elFinder/) ist jetzt Datei-Upload-Plugin für den WYSIWYG-Editor. [PR#239](https://github.com/foodcoopshop/foodcoopshop/pull/239) / [I#228](https://github.com/foodcoopshop/foodcoopshop/issues/228) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Netzwerk-Modul ist jetzt Teil vom Core. / [PR#237](https://github.com/foodcoopshop/foodcoopshop/pull/237) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- JQuery-Update v3.3.1 mit JQuery Migrate 1.4.1 / [PR#230](https://github.com/foodcoopshop/foodcoopshop/pull/230) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Vendor-Updates: CakePHP-Update v3.6, Featherlight v1.7.13, OwlCarousel v2.3.4 / [PR#218](https://github.com/foodcoopshop/foodcoopshop/pull/218) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Update auf PHPUnit 7. / [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/885c1365fd0ed12f2589f92f2fcdca82993c3558) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- JQuery UI Tooltip wurde durch Tooltipster ersetzt. / [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/ed331eae8fbb8959bb7e3981a1c8895199a3075c) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- JsMin wurde durch JShrink ersetzt. / [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/5013ee524d594a5ca4850dbf2e745c1d573e3b76) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

## v2.0.2 2018-04-18 / [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.0.1...v2.0.2)

### Bugfixes
- Das Ändern der E-Mail-Adresse beim Hersteller hat nicht korrekt funktioniert. / [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/1b72e5dad95287d48efd09882e6aa57d4d52b6d9) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Beim Erstellen eines neuen Blog-Artikels wurde das Feld "nach vorne reihen" irrtümlich angezeigt. Das Deaktivieren dieser Funktion hat zu einem Fehler geführt. / [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/22c550947df41bfc56fea049db4343e70d511a57) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

## v2.0.1 2018-04-03 / [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.0.0...v2.0.1)

### Bugfixes
- Die Sommerzeit wurde nicht korrekt berücksichtigt. / [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/245e5e1d7a7173a24a5f83cae90359563dfb3f01) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

## v2.0.0 2018-03-29 / [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v1.5.0...v2.0.0)

### Herzlichen Dank an alle beteiligten Personen
* [EvaSee](https://github.com/EvaSee)
* [mrothauer](https://github.com/mrothauer)
* [SaibotP](https://github.com/SaibotP)

### Neue Funktionen
- Superadmins können ab sofort das Profil von anderen Mitgliedern bearbeiten. / [PR#208](https://github.com/foodcoopshop/foodcoopshop/pull/208) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Produkte, deren Preis € 0,00 beträgt, werden jetzt unter *Bestellte Produkte* rot hinterlegt angezeigt. Somit soll nicht mehr aufs Ändern des Preises vergessen werden. / [PR#201](https://github.com/foodcoopshop/foodcoopshop/pull/201) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Das Hauptmenü kann jetzt mit drei Hierarchie-Ebenen umgehen. / [PR#208](https://github.com/foodcoopshop/foodcoopshop/pull/208) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bei den Produkten kann über eine neue Checkbox angegeben werden, ob das Produkt korrekt deklariert wurde (Lebensmittelkennzeichnung). / [PR#197](https://github.com/foodcoopshop/foodcoopshop/pull/197) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#196](https://github.com/foodcoopshop/foodcoopshop/issues/196) / <a href="https://github.com/SaibotP"><img src="https://github.com/SaibotP.png" width="20"></a>
- Mehr Filter bei den Produkten im Admin-Bereich: *Kategorie*, *Anzahl 0?* und *Preis 0?* / [PR#185](https://github.com/foodcoopshop/foodcoopshop/pull/185) / [PR#192](https://github.com/foodcoopshop/foodcoopshop/pull/190) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#166](https://github.com/foodcoopshop/foodcoopshop/issues/166) / <a href="https://github.com/SaibotP"><img src="https://github.com/SaibotP.png" width="20"></a>
- In der Hersteller-Liste im Admin-Bereich wird jetzt auch die Summe der offenen Bestellungen angezeigt. / [PR#193](https://github.com/foodcoopshop/foodcoopshop/pull/193) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#187](https://github.com/foodcoopshop/foodcoopshop/issues/187) / <a href="https://github.com/SaibotP"><img src="https://github.com/SaibotP.png" width="20"></a>
- Im Admin-Bereich muss nicht mehr auf *Filtern* geklickt werden, das geht jetzt automatisch. / [PR#184](https://github.com/foodcoopshop/foodcoopshop/pull/184) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#180](https://github.com/foodcoopshop/foodcoopshop/issues/180) / <a href="https://github.com/SaibotP"><img src="https://github.com/SaibotP.png" width="20"></a>
- **Gruppierung nach Produkt** ist jetzt für bestellte Produkte möglich. Hersteller und Mitglieder können so ihre Bestellungen noch übersichtlicher anzeigen bzw. auswerten. Hersteller können ab sofort auch auch das **Datum ihrer Bestellungen** frei wählen und so alte Bestellungen anzeigen. / [PR#179](https://github.com/foodcoopshop/foodcoopshop/pull/179) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#175](https://github.com/foodcoopshop/foodcoopshop/issues/175) / <a href="https://github.com/EvaSee"><img src="https://github.com/EvaSee.png" width="20"></a>

### Bugfixes
- Beim Hersteller kann jetzt die Homepage auch gespeichert werden, wenn sie mit https beginnt.  / [PR#208](https://github.com/foodcoopshop/foodcoopshop/pull/208) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Der variable Mitgliedsbeitrag wurde bei Rechnungen über 1.000 € falsch berechnet. / [PR#195](https://github.com/foodcoopshop/foodcoopshop/pull/195) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Für Entwickler
- Major-Release-Update von CakePHP, ab jetzt wird die Version 3 verwendet. / [PR#208](https://github.com/foodcoopshop/foodcoopshop/pull/208) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Npm wird jetzt anstatt Bower als Dependency Manager verwendet. / [PR#199](https://github.com/foodcoopshop/foodcoopshop/pull/199) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Viele nicht benutzte Datenbank-Felder wurden entfernt und die Anzahl der Tabellen von 40 auf 31 reduziert. / [PR#189](https://github.com/foodcoopshop/foodcoopshop/pull/189) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

[Zum Changelog von FoodCoopShop v1.x](CHANGELOG-v1.md)