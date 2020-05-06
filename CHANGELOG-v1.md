# Changelog v1

Das Format basiert auf [keepachangelog.com](http://keepachangelog.com) und verwendet [Semantic Versioning](http://semver.org/).

## v1.5.0 2017-12-18 / [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v1.4.0...v1.5.0)

### Herzlichen Dank an alle beteiligten Personen
* [christiankaindl](https://github.com/christiankaindl)
* [EvaSee](https://github.com/EvaSee)
* [k-pd](https://github.com/k-pd)
* [MacPac](https://github.com/MadPac)
* [mrothauer](https://github.com/mrothauer)
* [vmvbruck](https://github.com/vmvbruck)

### Neue Funktionen
- Sofort-Bestellungen und Pfand-Rückgabe sind jetzt auch in der Liste "Bestellte Produkte" erreichbar. Das spart Zeit beim Abholen der Produkte. Bei der Sofort-Bestellung ist das Mitglied vorausgewählt. / [PR#163](https://github.com/foodcoopshop/foodcoopshop/pull/163) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#155](https://github.com/foodcoopshop/foodcoopshop/issues/155) / <a href="https://github.com/MadPac"><img src="https://github.com/MadPac.png" width="20"></a>
- Möglichkeit zum Hochladen von Etiketten-Fotos für die lange Produktbeschreibung (Lebensmittelkennzeichnung). / [PR#170](https://github.com/foodcoopshop/foodcoopshop/pull/170) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- In den Rechnungen scheinen jetzt auch Bestellungen mit dem Status *offen* auf. / [PR#156](https://github.com/foodcoopshop/foodcoopshop/pull/156) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Im Admin-Bereich können jetzt die Produkte aller Hersteller in einer Liste angezeigt und bearbeitet werden. Es wird eine zusätzliche Spalte "Hersteller" angezeigt. / [PR#167](https://github.com/foodcoopshop/foodcoopshop/pull/167) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Eine Änderung von Guthaben-Aufladungen ist nicht mehr möglich, sobald sie bestätigt wurden. / [PR#143](https://github.com/foodcoopshop/foodcoopshop/pull/143) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Falls man uneingeloggt nur für Mitglieder sichtbare Blog-Artikel, Seiten oder Hersteller (bzw. deren Produkte) aufruft, wird jetzt statt der Fehler-Seite das Login-Formular angezeigt. / [PR#154](https://github.com/foodcoopshop/foodcoopshop/pull/154) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Wenn man das Passwort vergessen hat, muss man jetzt zusätzlich auf einen Bestätigungs-Link klicken, bevor das Passwort tatsächlich geändert wird. / [PR#141](https://github.com/foodcoopshop/foodcoopshop/pull/141) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Der Urlaubsmodus heißt jetzt *Lieferpause* und kann somit auch für Lieferpausen außerhalb des Urlaubs verwendet werden. / [PR#159](https://github.com/foodcoopshop/foodcoopshop/pull/159) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#158](https://github.com/foodcoopshop/foodcoopshop/issues/158) / <a href="https://github.com/vmvbruck"><img src="https://github.com/vmvbruck.png" width="20"></a>
- Bei den Herstellern können jetzt auch IBANs aus Deutschland eingetragen werden. / [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/25a5eb17fb2008993a9e6fd914348d84e0dcf093) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> 
- Mehr mögliche Kombinationen für Bestelllisten-Versendetag und Liefertag: *Mittwoch-Freitag* / *Dienstag-Freitag* / *Montag-Dienstag*. / [PR#173](https://github.com/foodcoopshop/foodcoopshop/pull/173) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#151](https://github.com/foodcoopshop/foodcoopshop/issues/151) / <a href="https://github.com/christiankaindl"><img src="https://github.com/christiankaindl.png" width="20"></a>
- Anpassungen für die Einbindung des Netzwerk-Moduls in der Version 1.0. / [PR#129](https://github.com/foodcoopshop/foodcoopshop/pull/129) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Bugfixes
- Wenn der Steuersatz eines Produktes 0% betragen hat und geändert wird, wurde der Preis auf € 0 zurückgesetzt. / [PR#153](https://github.com/foodcoopshop/foodcoopshop/pull/153) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bereits hochgeladene Bilder, die durch neue ersetzt wurden, werden jetzt auch am Frontend sofort angezeigt. / [PR#138](https://github.com/foodcoopshop/foodcoopshop/pull/138) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die automatische Backup-Funktion über die konfigurierbare BCC-Adresse hat nicht korrekt funktioniert. / [PR#136](https://github.com/foodcoopshop/foodcoopshop/pull/136) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Gelöschte Unterseiten wurden auf der übergeordneten Seite als Button angezeigt. / [PR#135](https://github.com/foodcoopshop/foodcoopshop/pull/135) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#131](https://github.com/foodcoopshop/foodcoopshop/issues/131) / <a href="https://github.com/vmvbruck"><img src="https://github.com/vmvbruck.png" width="20"></a>

### Für Entwickler
- MySQL 5.7 wird jetzt wirklich unterstützt, es gab da noch ein paar Probleme. Außerdem verwendet Travis-CI jetzt auch MySQL 5.7. / [PR#161](https://github.com/foodcoopshop/foodcoopshop/pull/161) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Veraltete Dependency zum Erstellen von Thumbnails wurde ersetzt: [image.intervention.io](http://image.intervention.io). / [PR#138](https://github.com/foodcoopshop/foodcoopshop/pull/138) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

## v1.4.0 2017-09-17 / [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v1.3.0...v1.4.0)

### Neue Funktionen
- Kommentar-Feld bei Bestell-Abschluss für Nachricht an Abholdienst. / [PR#100](https://github.com/foodcoopshop/foodcoopshop/pull/100) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Viele Hersteller-Einstellungen können jetzt auch vom Hersteller selbst verändert werden. / [PR#87](https://github.com/foodcoopshop/foodcoopshop/pull/87) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- E-Mail-Benachrichtigung für Hersteller nach Sofortbestellungen / [PR#87](https://github.com/foodcoopshop/foodcoopshop/pull/87) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Herstellern kann eine Ansprechperson zugeordnert werden. Name, E-Mail-Adresse und Telefonnummer sind dann für den Hersteller ersichtlich. [PR#87](https://github.com/foodcoopshop/foodcoopshop/pull/87) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Verbesserter Urlaubsmodus für Hersteller. Es kann jetzt das Datum angegeben werden. / [PR#81](https://github.com/foodcoopshop/foodcoopshop/pull/81) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Bugfixes
- Die Überschrift der Info-Box kann wieder als Überschrift 3 formatiert werden. / [Commit](https://github.com/foodcoopshop/foodcoopshop/compare/18e3adee0c536fd15e7450c7aba289c49b391214...c952166ec81eb6f8ad5c2a84875b534329439f6a) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> 
- Wenn ein Hersteller nur für Mitglieder sichtbar ist, sind jetzt auch zugeordnete Blog-Artikel nur für Mitglieder sichtbar (unabhängig von der Einstellung des Blog-Artikels). / [PR#90](https://github.com/foodcoopshop/foodcoopshop/pull/90) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Artikel heißen ab sofort Produkte. Das war ein Durcheinander. / [PR#128](https://github.com/foodcoopshop/foodcoopshop/pull/128) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- In den automatisierten E-Mails bei Sofort-Bestellungen wird im Footer der Name des tatsächlich eingeloggten Mitglieds angezeigt, und nicht mehr der Name, für den bestellt wird.  

### Für Entwickler
- Minimal-Anforderung für PHP: v5.6 (v5.5 wird nicht mehr unterstützt!)
- Datenbank-Anpassungen für MySQL 5.7 / [PR#109](https://github.com/foodcoopshop/foodcoopshop/pull/109) / <a href="https://github.com/k-pd"><img src="https://github.com/k-pd.png" width="20"></a>
- Unit Tests für Stornierung, Preisänderung und Änderung der Menge von bestellten Produkten / [PR#102](https://github.com/foodcoopshop/foodcoopshop/pull/102) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Travis-CI Anpassungen für Ubuntu Trusty / [PR#110](https://github.com/foodcoopshop/foodcoopshop/pull/110) / <a href="https://github.com/k-pd"><img src="https://github.com/k-pd.png" width="20"></a>
- Das Versenden von E-Mails und Cake-Shell-Skripte können jetzt abgetestet werden / [PR#96](https://github.com/foodcoopshop/foodcoopshop/pull/96) / [PR#118](https://github.com/foodcoopshop/foodcoopshop/pull/118) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Erleichterter Workflow bei Migrations / [PR#82](https://github.com/foodcoopshop/foodcoopshop/pull/82) / <a href="https://github.com/k-pd"><img src="https://github.com/k-pd.png" width="20"></a>

## v1.3.0 2017-06-17 / [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v1.2.1...v1.3.0)

### Added
- Responsive design for admin / [PR#37](https://github.com/foodcoopshop/foodcoopshop/pull/37) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Software documentation moved from the Github-Wiki to [https://foodcoopshop.github.io](https://foodcoopshop.github.io) / [#43](https://github.com/foodcoopshop/foodcoopshop/issues/43) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Home slider with pagination including swiping for mobile devices / [#2](https://github.com/foodcoopshop/foodcoopshop/issues/2) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Improvements for credit uploads / [PR#47](https://github.com/foodcoopshop/foodcoopshop/pull/47) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Travis integration on Github / [PR#58](https://github.com/foodcoopshop/foodcoopshop/pull/58) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Stickler integration on Github / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Easier execution of unit tests / [PR#57](https://github.com/foodcoopshop/foodcoopshop/pull/57) / [PR#45](https://github.com/foodcoopshop/foodcoopshop/pull/45) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Added unit tests for payments / [PR#59](https://github.com/foodcoopshop/foodcoopshop/pull/59) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Lint code with git pre-commit-hook / [PR#56](https://github.com/foodcoopshop/foodcoopshop/pull/56) / <a href="https://github.com/k-pd"><img src="https://github.com/k-pd.png?size=20" width="20"></a>
- Database migrations for automatic database update after installing new release / [PR#34](https://github.com/foodcoopshop/foodcoopshop/pull/34) / <a href="https://github.com/k-pd"><img src="https://github.com/k-pd.png" width="20"></a>
- Created this changelog / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Changed
- Hide product price for logged out users if option "show products for logged out users" is enabled / [PR#53](https://github.com/foodcoopshop/foodcoopshop/pull/53) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Phone icon for manufacturer and customer (easier to understand) [#63](https://github.com/foodcoopshop/foodcoopshop/issues/63) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Fixed
- New price of products was calculated wrong if tax was set offline / [PR#51](https://github.com/foodcoopshop/foodcoopshop/pull/51) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Slider images upload / [PR#49](https://github.com/foodcoopshop/foodcoopshop/pull/49) / <a href="https://github.com/k-pd"><img src="https://github.com/k-pd.png" width="20"></a>

## v1.2.1 2017-03-22 / [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v1.2...v1.2.1)

### Added
- Info-Text für variablen Mitgliedsbeitrag in Bestelllisten, Rechnungen, Bestellbestätigungen und Bestell-PDFs

## v1.2.0 2017-03-09 / [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v1.1.3...v1.2)

### Added
- Neues Frühwarnsystem für Guthaben-Aufladungen: Diese können jetzt von einem Superadmin bestätigt bzw. als "da stimmt was nicht…" markiert werden, auch Kommentare sind möglich. Die Mitglieder sehen den Status ihrer Zahlungen in ihrem Guthaben-System und können über die Status-Änderung (optional) per E-Mail benachrichtigt werden.
- Stornieren von mehreren Produkten auf einmal.
- Sofort-Bestellungen sind nur mehr von Admins und Superadmins durchführbar. War für neue Mitglieder teilweise unklar.
- Der Bestell-Status von Sofort-Bestellungen kann in den Einstellungen verwaltet werden. Standard-Einstellung ist "abgeschlossen".
- Vorschau-Funktion der Registrierungs-E-Mail unter "Einstellungen"

### Changed
- Performance-Optimierungen in der Datenbank
- Beim Datenbank-Backup wird die Größe der Datei angeführt.
- Kleinere Verbesserungen beim Bestell-Vorgang (vor allem in der mobilen Version) und bei der Darstellung der Blog-Artikel.
- Der Betreiber der Webseite kann jetzt separat angegeben werden, sollte der Betreiber nicht die Foodcoop selbst sein (unter "Einstellungen")
- Favicons werden jetzt für viele mobile Devices unterstützt.

## v1.1.3 2017-02-21 / [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v1.1.2...v1.1.3)

### Fixed
- Hersteller-bearbeiten: Customer records wurden mehrfach angelegt

## v1.1.2 2017-02-06 / [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v1.1.1...v1.1.2)

### Fixed
- Hersteller-bearbeiten: wenn gleiche E-Mail-Adresse bereits einem Mitglied zugewiesen war, konnte sich der Hersteller danach nicht mehr einloggen

## v1.1.1 2016-12-30 / [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/1.1.0...v1.1.1)

### Fixed
- Mehrere kleine Bugfixes

## v1.1.0 2016-12-25 / [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/1.0.0...1.1.0)

### Added
- Superadmins können Guthaben-Konten von anderen Mitgliedern ansehen und Zahlungen eintragen bzw. löschen. Es sind ab sofort auch Rückzahlungen eintragbar, falls Mitglieder aus dem Verein austreten und ihnen Geld zurücküberwiesen wird.
- Superadmins können auch für deaktivierte Mitglieder Sofort-Bestellungen tätigen (z.B. Rest-Guthaben als Spende für den Verein)
- Einbindung von Nutzungsbedingungen
- Änderungen Bestellübersicht / Warenkorb
- Enthaltene Umsatzsteuer aller Produkte wird angezeigt (Bestellübersicht)
- Bild durch Klick vergrößerbar (Warenkorb)
- Rücktrittsbedingungen, AGB und detaillierte Bestellübersicht werden beim Bestellen als PDF mitgeschickt.
- Gesamtsumme von Warenwert und Pfand wird angezeigt.
- Hersteller verfügen jetzt über ein eigenes Pfandkonto, mit dem das Pfand am Ende des Jahres sauber abgerechnet werden kann.
- Sofort-Bestellungen sind jetzt automatisch abgeschlossen.
- Neue Einstellung für den Maximalbetrag einer Guthaben-Aufladung.
- Neue Einstellung für die Backup-E-Mail-Adresse aller vom System verschickten E-Mails.
- Neue Einstellung für Seiten: "Nur für Mitglieder sichtbar"
- Facebook-Einbindung nur noch als Link, damit keine Daten ungefragt an Facebook geschickt werden.
- Auf dem Hersteller-Profil befindet sich rechts oben ein Bearbeiten-Symbol (Für Admins, Superadmins und den jeweiligen Hersteller).

### Fixed
- Hersteller, die nur für Mitglieder sichtbar sind, wurden auf der Hersteller-Übersichtsseite angezeigt.
- Zahlreiche kleinere Bugfixes und Verbesserungen.

## v1.0.0 2016-10-30 / [View changes](https://github.com/foodcoopshop/foodcoopshop/commits/1.0.0)

Zur Erklärung: Bis zum 24.10.2016 wurde das große Update im Sommer als "1.0" bezeichnet. Ab diesem Datum wird dieses Update mit "0.9" bezeichnet, und "1.0" ist die erste offizielle Open-Source-Version vom FoodCoopShop.
