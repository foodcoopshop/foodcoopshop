<h1 align="center">
  <a href="https://www.foodcoopshop.com"><img src="https://raw.githubusercontent.com/foodcoopshop/foodcoopshop/develop/webroot/files/images/logo.png" alt="FoodCoopShop"></a>
</h1>

# Changelog v3.x

Das Format basiert auf [keepachangelog.com](http://keepachangelog.com) und verwendet [Semantic Versioning](http://semver.org/).

# Unveröffentlichte Version

### Herzlichen Dank an alle beteiligten Personen
* <img src="https://github.com/mrothauer.png" width="20"> [mrothauer](https://github.com/mrothauer)

### Neue Funktionen
- Pfand-Rückgaben für Mitglieder können nun auch dann eingegeben werden, auch wenn das Mitglied in der aktuellen Woche nicht bestellt hat. Der Button "Pfand-Rückgabe" wird immer angezeigt, das Mitglied kann dann aus einer Dropdown-Liste ausgewählt werden. [I#654](https://github.com/foodcoopshop/foodcoopshop/issues/654) / [PR#655](https://github.com/foodcoopshop/foodcoopshop/pull/655) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Das in die Jahre gekommene Frontend wurde optisch aufgepeppt: Blog-Artikel, Hauptmenü, das mobile Menü, die Produkt-Liste und der Footer sind nun frischer. Weiters ist die Haupt-Schrift etwas größer und die Fett-Schrift dezenter. [I#643](https://github.com/foodcoopshop/foodcoopshop/issues/643) / [PR#648](https://github.com/foodcoopshop/foodcoopshop/pull/648) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Kleinere Verbesserungen
- Beim Erstellen eines neuen Blog-Artikels kann nun angegeben werden, wie lange er auf der Startseite angezeigt werden soll. Danach verschwindet er automatisch. [I#601](https://github.com/foodcoopshop/foodcoopshop/issues/601) / [PR#664](https://github.com/foodcoopshop/foodcoopshop/pull/664) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Beim Registrieren mit automatischer Aktivierung muss ab sofort die E-Mail-Adresse bestätigt werden, sonst bleibt der neue User inaktiv. [I#656](https://github.com/foodcoopshop/foodcoopshop/issues/656) / [PR#657](https://github.com/foodcoopshop/foodcoopshop/pull/657) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Das Bestelldatum wird nun im Tooltip über dem Bestellstatus-Icon angezeigt. [I#652](https://github.com/foodcoopshop/foodcoopshop/issues/652) / [PR#653](https://github.com/foodcoopshop/foodcoopshop/pull/653) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Alle Formulare sind jetzt noch besser gegen potenzielle Angriffe abgesichert. [I#659](https://github.com/foodcoopshop/foodcoopshop/issues/659) / [PR#661](https://github.com/foodcoopshop/foodcoopshop/pull/661) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Bugfixes
- Guthaben-Aufladungen mit CSV-Upload: Wenn ein Mitglied nicht ausgewählt wurde und man speichern wollte, wurde nicht die Fehlermeldung beim Mitglied angezeigt, sondern die allgemeine Fehlerseite. [I#677](https://github.com/foodcoopshop/foodcoopshop/issues/677) / [PR#678](https://github.com/foodcoopshop/foodcoopshop/pull/678) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Code Cleaning
- Die alte Funktion zum Verwalten der Mitgliedbeitrag wurde entfernt. [Anleitung zum Umstellen auf das neue System](https://foodcoopshop.github.io/de/mitgliedsbeitraege.html). [I#666](https://github.com/foodcoopshop/foodcoopshop/issues/666) / [PR#667](https://github.com/foodcoopshop/foodcoopshop/pull/667) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Sauberere SQL-Statements durch Verwendung von QueryExpression. [I#644](https://github.com/foodcoopshop/foodcoopshop/issues/644) / [PR#645](https://github.com/foodcoopshop/foodcoopshop/pull/645) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

[Mehr Details zum Release](https://github.com/foodcoopshop/foodcoopshop/projects/15) / [Alle Änderungen anzeigen](https://github.com/foodcoopshop/foodcoopshop/compare/v3.2.2...develop)

# v3.2.2

### Security Fix
* Das Registrierungsformular und das Formular zum Bearbeiten des User-Profils sind nun besser abgesichert.

Datum: 12.04.2021 / [Alle Änderungen anzeigen](https://github.com/foodcoopshop/foodcoopshop/compare/v3.2.1...v3.2.2)

# v3.2.1

### Bugfixes
* Wenn ein Hersteller die eigenen Einstellungen speichert, wird die Ansprechperson jetzt nicht mehr gelöscht.
* Layout-Fix im Overlay für die Produkt-Beschreibung.
* Kamera-Icon wurde auf der Login-Seite für den Selbstbedienung-Modus auf Smartphones nicht angezeigt.
* Wenn die Erstinstallation <= v3.0 war, müssen [zwei Migrations manuell ausgeführt werden](https://foodcoopshop.github.io/en/migration-guide.html).

Datum: 23.03.2021 / [Alle Änderungen anzeigen](https://github.com/foodcoopshop/foodcoopshop/compare/v3.2.0...v3.2.1)

# v3.2.0

### Herzlichen Dank an alle beteiligten Personen
* <img src="https://github.com/AndreasEgger.png" width="20"> [AndreasEgger](https://github.com/AndreasEgger)
* <img src="https://github.com/mantensteiner.png" width="20"> [mantensteiner](https://github.com/mantensteiner)
* <img src="https://github.com/markuskoban.png" width="20"> [markuskoban](https://github.com/markuskoban)
* <img src="https://github.com/mrothauer.png" width="20"> [mrothauer](https://github.com/mrothauer)
* <img src="https://github.com/reicharm.png" width="20"> [reicharm](https://github.com/reicharm)

### Neue Funktionen
- Das neue Modul zur Erstellung von Kunden-Rechnungen ermöglicht die Verwendung der Software im Einzelhandel. [Zur Online-Doku](https://foodcoopshop.github.io/de/dorfladen-online.html). [I#572](https://github.com/foodcoopshop/foodcoopshop/issues/572) / [PR#580](https://github.com/foodcoopshop/foodcoopshop/pull/580) / [PR#584](https://github.com/foodcoopshop/foodcoopshop/pull/584) / [PR#599](https://github.com/foodcoopshop/foodcoopshop/pull/599) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Verwaltung der Mitgliedsbeiträge ist nun stark vereinfacht. [Zur Online-Doku](https://foodcoopshop.github.io/de/mitgliedsbeitraege.html). [I#471](https://github.com/foodcoopshop/foodcoopshop/issues/471) / [PR#608](https://github.com/foodcoopshop/foodcoopshop/pull/608) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- 📷 Beim Einkaufen im Selbstbedienungs-Modus kann man nun direkt mit der Smartphone-Kamera (ganz ohne App) die Barcodes scannen. [I#557](https://github.com/foodcoopshop/foodcoopshop/issues/557) / [PR#563](https://github.com/foodcoopshop/foodcoopshop/pull/563) <a href="https://github.com/AndreasEgger"><img src="https://github.com/AndreasEgger.png" width="20"></a> <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die stark verbesserte Pfand-Übersicht bringt endlich Licht 💡 in den Pfand-Dschungel 🐵, der sich bei manchen Initiativen über die Jahre ergeben hat. [I#570](https://github.com/foodcoopshop/foodcoopshop/issues/570) / [PR#571](https://github.com/foodcoopshop/foodcoopshop/pull/571) </a> <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- ☑ Beim Kontrollieren der Bestellungen bleiben die Produkte jetzt angehakt, wenn z.B. Gewicht oder Preis geändert wird. Wenn die Hakerl nicht wieder entfernt werden, sind sie nach 24 Stunden automatisch weg. [I#616](https://github.com/foodcoopshop/foodcoopshop/issues/616) / [PR#617](https://github.com/foodcoopshop/foodcoopshop/pull/617) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Das eingestellte Guthaben-Limit kann nun beim normalen Bestellen nicht mehr unterschritten werden. Bei Sofort-Bestellungen und Preis- bzw. Gewichtsanpassungen ist dies aber weiterhin möglich. [I#555](https://github.com/foodcoopshop/foodcoopshop/issues/555) / [PR#574](https://github.com/foodcoopshop/foodcoopshop/pull/574) / [PR#603](https://github.com/foodcoopshop/foodcoopshop/pull/603) / [PR#635](https://github.com/foodcoopshop/foodcoopshop/pull/635) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Guthaben-Höhe, ab der die Guthaben-Erinnerungsmail versendet wird, kann nun individuell eingestellt werden. Eine Erhöhung auf z.B. 50 € ist für Initiativen sinnvoll, die den CSV-Upload verwenden. [I#621](https://github.com/foodcoopshop/foodcoopshop/issues/621) / [PR#622](https://github.com/foodcoopshop/foodcoopshop/pull/622) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Das individuelle Farbschema wird jetzt auch im Admin-Bereich angewendet. [I#613](https://github.com/foodcoopshop/foodcoopshop/issues/613) / [PR#630](https://github.com/foodcoopshop/foodcoopshop/pull/630) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Kleinere Verbesserungen
- Es gibt neue Lieferrhythmen: "jeder 2., 3. bzw. 4. Freitag im Monat" [I#581](https://github.com/foodcoopshop/foodcoopshop/issues/581) / [PR#582](https://github.com/foodcoopshop/foodcoopshop/pull/582) / Validierung: [PR#624](https://github.com/foodcoopshop/foodcoopshop/pull/624) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Slideshow-Bilder auf der Startseite können jetzt verlinkt und außerdem auch "nur für Mitglieder" angezeigt werden. [I#600](https://github.com/foodcoopshop/foodcoopshop/issues/600) / [PR#606](https://github.com/foodcoopshop/foodcoopshop/pull/606) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die automatisch versendeten E-Mails bei Preis- und Gewichtsanpassungen von bestellten Produkten können nun global abgestellt werden. [I#576](https://github.com/foodcoopshop/foodcoopshop/issues/576) / [PR#577](https://github.com/foodcoopshop/foodcoopshop/pull/577) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Fehlerhafte Gewichtsänderungen (z.B. 700 kg statt 700 g) können nun nicht mehr getätigt werden. [I#590](https://github.com/foodcoopshop/foodcoopshop/issues/590) / [PR#593](https://github.com/foodcoopshop/foodcoopshop/pull/593) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Beim Ändern des Abgeholt-Status wird nun überprüft, ob das Gewicht für alle Produkte eingetragen wurde. [I#614](https://github.com/foodcoopshop/foodcoopshop/issues/614) / [PR#615](https://github.com/foodcoopshop/foodcoopshop/pull/615) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Zur besseren Übersicht wird das "Bestellbar bis"-Datum jetzt bei jedem Produkt angezeigt. Außer bei Produkten mit wöchentlichem Lieferrhythmus und Standard-Bestellschluss. [I#585](https://github.com/foodcoopshop/foodcoopshop/issues/585) / [PR#594](https://github.com/foodcoopshop/foodcoopshop/pull/594) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Produktbilder im Hochformat werden jetzt in der Lightbox besser dargestellt. [I#579](https://github.com/foodcoopshop/foodcoopshop/issues/579) / [PR#596](https://github.com/foodcoopshop/foodcoopshop/pull/596) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Möglichkeit zur kompletten Deaktivierung des Pfand-Systems. [I#604](https://github.com/foodcoopshop/foodcoopshop/issues/604) / [PR#607](https://github.com/foodcoopshop/foodcoopshop/pull/607) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Es wird nun ein Cookie-Banner eingeblendet und die Datenschutzerklärung ist wieder aktuell bezüglich der verwendeten Cookies. [I#619](https://github.com/foodcoopshop/foodcoopshop/issues/619) / [PR#620](https://github.com/foodcoopshop/foodcoopshop/pull/620) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Auf den Produkt-Bestelllisten scheint nun auch die Summe der Produkt-Einheit (kg, g) auf. [I#333](https://github.com/foodcoopshop/foodcoopshop/issues/333) / [PR#578](https://github.com/foodcoopshop/foodcoopshop/pull/578) <a href="https://github.com/markuskoban"><img src="https://github.com/markuskoban.png" width="20"></a> <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Software ist kompatibel mit PHP 8.0.
- Das Logo kann jetzt auch in anderen Formaten verwendet werden. Neuer Standard ist PNG. [PR#637](https://github.com/foodcoopshop/foodcoopshop/pull/637) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Bugfixes
- Bestelllisten- und Rechnungsversand werden jetzt über eine Queue versendet. Das verhindert seltene, aber nervige Fehler beim Versenden. [I#410](https://github.com/foodcoopshop/foodcoopshop/issues/410) / [I#560](https://github.com/foodcoopshop/foodcoopshop/issues/560) / [I#562](https://github.com/foodcoopshop/foodcoopshop/issues/562) / [PR#561](https://github.com/foodcoopshop/foodcoopshop/pull/561) / [PR#566](https://github.com/foodcoopshop/foodcoopshop/pull/566) / [PR#553](https://github.com/foodcoopshop/foodcoopshop/pull/553) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Hochgeladene Bilder (z.B. Slideshow) waren machmal leicht unscharf. [I#558](https://github.com/foodcoopshop/foodcoopshop/issues/558) / [PR#573](https://github.com/foodcoopshop/foodcoopshop/pull/573) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Deutsche IBANs können nun eingetragen werden, das Feld war zu kurz. [I#559](https://github.com/foodcoopshop/foodcoopshop/issues/559) / [PR#564](https://github.com/foodcoopshop/foodcoopshop/pull/564) <a href="https://github.com/mantensteiner"><img src="https://github.com/mantensteiner.png" width="20"></a> <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Lieferrhythmus "erster Freitag im Monat" kombiniert mit "Sonntag Bestellschluss" hat nicht korrekt funktioniert. [I#567](https://github.com/foodcoopshop/foodcoopshop/issues/567) / [PR#568](https://github.com/foodcoopshop/foodcoopshop/pull/568) <a href="https://github.com/AndreasEgger"><img src="https://github.com/AndreasEgger.png" width="20"></a> <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Monatlicher Lieferrhythmus kombiniert mit "erster Liefertag" hat nicht korrekt funktioniert. [I#623](https://github.com/foodcoopshop/foodcoopshop/issues/623) / [PR#624](https://github.com/foodcoopshop/foodcoopshop/pull/624) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Code Cleaning
- Die Übersetzung auf Polnisch wurde entfernt. Sie wurde nicht mehr verwendet und auch nicht mehr upgedatet. [I#631](https://github.com/foodcoopshop/foodcoopshop/issues/631) / [PR#632](https://github.com/foodcoopshop/foodcoopshop/pull/632) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- CI-Umstellung von Travis auf Github Actions [PR#556](https://github.com/foodcoopshop/foodcoopshop/pull/556) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Deprecations
- ⚠️⚠️⚠️ Wer das [Stundenabrechnungs-Modul](https://foodcoopshop.github.io/de/stundenabrechnungs-modul.html) aktiv verwendet, soll sich bitte bei mir melden. Ich habe nämlich den Eindruck, dass es kaum in Gebrauch ist. Da aber die Wartung Aufwand bedeutet, werde ich das Modul - sofern sich bis dahin niemand meldet - ab v3.3 (Herbst 2021) aus der Software entfernen.

Datum: 08.03.2021 / [Mehr Details zum Release](https://github.com/foodcoopshop/foodcoopshop/projects/14) / [Alle Änderungen anzeigen](https://github.com/foodcoopshop/foodcoopshop/compare/v3.1.0...v3.2.0)

# v3.1.0

### Herzlichen Dank an alle beteiligten Personen
* <img src="https://github.com/AndreasEgger.png" width="20"> [AndreasEgger](https://github.com/AndreasEgger)
* <img src="https://github.com/dpakach.png" width="20"> [dpakach](https://github.com/dpakach)
* <img src="https://github.com/mrothauer.png" width="20"> [mrothauer](https://github.com/mrothauer)
* <img src="https://github.com/swoichha.png" width="20"> [swoichha](https://github.com/swoichha)
* <img src="https://github.com/vmvbruck.png" width="20"> [vmvbruck](https://github.com/vmvbruck)

### Neue Funktionen
- Automatischer Kontoabgleich für das Guthaben-System (CSV-Upload). [Zur Online-Doku](https://foodcoopshop.github.io/de/guthaben-system-mit-automatischem-kontoabgleich). [I#463](https://github.com/foodcoopshop/foodcoopshop/issues/463) / [PR#474](https://github.com/foodcoopshop/foodcoopshop/pull/474) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Mitglieder können nun Feedback zu Produkten abgeben, der Hersteller wird automatisch per E-Mail darüber informiert. [I#391](https://github.com/foodcoopshop/foodcoopshop/issues/391) / [PR#536](https://github.com/foodcoopshop/foodcoopshop/pull/536) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> <a href="https://github.com/vmvbruck"><img src="https://github.com/vmvbruck.png" width="20"></a>
- Viele Overlays (z.B. "Gewicht ändern", "Bild-Upload", "Abmelden") sind nun benutzerfreundlicher und systemweit vereinheitlicht. [I#328](https://github.com/foodcoopshop/foodcoopshop/issues/328) / [PR#524](https://github.com/foodcoopshop/foodcoopshop/pull/524) / [PR#530](https://github.com/foodcoopshop/foodcoopshop/pull/530) / [PR#537](https://github.com/foodcoopshop/foodcoopshop/pull/537) / [PR#538](https://github.com/foodcoopshop/foodcoopshop/pull/538) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Verbesserungen bei der Gewichtsanpassung: Auch gleiches Gewicht ist nach dem Speichern nicht mehr rot hinterlegt. / Bei bereits verrechneten Bestellungen wird das Gewicht niemals rot angezeigt. / Neues Gewicht ist in der E-Mail-Betreffzeile - damit Fehler wie z.B. 540 kg (statt g) schneller auffallen. / Kein E-Mail-Versand falls das Gewicht gleich bleibt. [I#423](https://github.com/foodcoopshop/foodcoopshop/issues/423) / [PR#479](https://github.com/foodcoopshop/foodcoopshop/pull/479) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Es ist jetzt möglich, als Bestellschluss für bestimmte Produkte auch **zwei Tage** vor dem Standard-Bestellschluss auszuwählen. Bisher war das nur für den Vortag möglich. [I#487](https://github.com/foodcoopshop/foodcoopshop/issues/487) / [PR#489](https://github.com/foodcoopshop/foodcoopshop/pull/489) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- 😍 Ab sofort können Emojis verwendet werden: Z.B. in Blog-Artikeln, Seiten oder beim Stornieren. Im Editor gibt's dazu ein neues Icon, beim Tippen von einem Doppelpunkt und zwei Buchstaben wird automatisch eine Liste mit Emojis angezeigt. [I#464](https://github.com/foodcoopshop/foodcoopshop/issues/464) / [PR#478](https://github.com/foodcoopshop/foodcoopshop/pull/478) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Falls Produkte auch für uneingeloggte Mitglieder angezeigt werden, wird nun auch der In-den-Warenkorb-Button angezeigt. Wenn man darauf klickt, erhält man die Meldung, dass man sich zuerst registrieren muss. [I#499](https://github.com/foodcoopshop/foodcoopshop/issues/499) / [PR#500](https://github.com/foodcoopshop/foodcoopshop/pull/500) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Neue Produkte werden nun auch auf der Startseite angezeigt. Das kann in den Einstellungen ausgestellt werden. [I#504](https://github.com/foodcoopshop/foodcoopshop/issues/504) / [PR#506](https://github.com/foodcoopshop/foodcoopshop/pull/506) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Kunden von Hofläden können den Abholtag selbst beim Bestellabschluss auswählen. [Zur Online-Doku](https://foodcoopshop.github.io/de/hofladen-online.html). [PR#542](https://github.com/foodcoopshop/foodcoopshop/pull/542) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Bugfixes / Optimierungen
- Beim Ändern der Anzahl bzw. Anzahl-Einstellungen von Produkten wird der alte Wert nun wieder unter Aktivitäten angezeigt. [I#514](https://github.com/foodcoopshop/foodcoopshop/issues/514) / [PR#515](https://github.com/foodcoopshop/foodcoopshop/pull/515) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Horizontales Scrollen auf kleinen Bildschirmen hat das Layout zerschossen. [I#497](https://github.com/foodcoopshop/foodcoopshop/issues/497) / [PR#498](https://github.com/foodcoopshop/foodcoopshop/pull/498) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Man bleibt jetzt 30 Tage lang angemeldet, wenn man die Funkion "Angemeldet bleiben" verwendet. Bisher waren es 6. [I#492](https://github.com/foodcoopshop/foodcoopshop/issues/492) / [PR#493](https://github.com/foodcoopshop/foodcoopshop/pull/493) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Daten für die Mitglieder-Drodowns im Admin-Bereich werden nun erst nach dem Daraufklicken geladen. Das lädt die Seiten schneller, besonders bei Initativen mit vielen Mitgliedern. [I#477](https://github.com/foodcoopshop/foodcoopshop/issues/477) / [PR#501](https://github.com/foodcoopshop/foodcoopshop/pull/501) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die automatische Zeichenbeschränkung in Formularen hat nicht mehr funktioniert (z.B. Feld "Kammer", Feld "Vorname") und führte zu einem Datenbank-Fehler. [I#485](https://github.com/foodcoopshop/foodcoopshop/issues/485) / [I#521](https://github.com/foodcoopshop/foodcoopshop/issues/521) / [PR#488](https://github.com/foodcoopshop/foodcoopshop/pull/525) / [PR#525](https://github.com/foodcoopshop/foodcoopshop/pull/488) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> <a href="https://github.com/AndreasEgger"><img src="https://github.com/AndreasEgger.png" width="20"></a>
- Infotext bei der Lieferpause ist jetzt leichter verständlich. [I#469](https://github.com/foodcoopshop/foodcoopshop/issues/469) / [PR#482](https://github.com/foodcoopshop/foodcoopshop/pull/482) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bestelllisten sind ab und zu nicht über die Fallback-Konfiguration versendet worden. [I#495](https://github.com/foodcoopshop/foodcoopshop/issues/495) / [PR#496](https://github.com/foodcoopshop/foodcoopshop/pull/496) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Der neue PDF-Writer kann nun PDFs unabhängig von Controllern erzeugen (als Attachment, Inline oder File). [I#412](https://github.com/foodcoopshop/foodcoopshop/issues/412) / [PR#508](https://github.com/foodcoopshop/foodcoopshop/pull/508) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bei der Validierung der E-Mail-Adressen wird jetzt auch der MX-Eintrag überprüft. Das vermeidet das Eintragen von ungültigen E-Mail-Adressen, die zwar syntaktisch korrekt sind, bei denen sich aber beim Domainnamen ein Tippfehler eingeschlichen hat. [I#465](https://github.com/foodcoopshop/foodcoopshop/issues/465) / [PR#516](https://github.com/foodcoopshop/foodcoopshop/pull/516) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Home- und Blog-Slider: OwlCarousel2 wurde ersetzt mit Swiper. [I#512](https://github.com/foodcoopshop/foodcoopshop/issues/512) / [PR#535](https://github.com/foodcoopshop/foodcoopshop/pull/535) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- All tests now work without HttpClient and use IntegrationTestTrait, the tests are now about 45% faster! [I#404](https://github.com/foodcoopshop/foodcoopshop/issues/404) / [PR#550](https://github.com/foodcoopshop/foodcoopshop/pull/550) / [PR#529](https://github.com/foodcoopshop/foodcoopshop/pull/529) / [PR#531](https://github.com/foodcoopshop/foodcoopshop/pull/531) / [PR#532](https://github.com/foodcoopshop/foodcoopshop/pull/532) <a href="https://github.com/swoichha"><img src="https://github.com/swoichha.png" width="20"></a> <a href="https://github.com/dpakach"><img src="https://github.com/dpakach.png" width="20"></a> <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- FoodCoopShop verwendet jetzt CakePHP v4.1.x. [I#541](https://github.com/foodcoopshop/foodcoopshop/issues/541) / [PR#545](https://github.com/foodcoopshop/foodcoopshop/pull/545) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

Datum: 07.09.2020 / [Mehr Details zum Release](https://github.com/foodcoopshop/foodcoopshop/projects/13) / [Alle Änderungen anzeigen](https://github.com/foodcoopshop/foodcoopshop/compare/v3.0.2...v3.1.0)

# v3.0.2

### Bugfix
- Produkte waren fehlerhafterweise bestellbar, wenn das Produkt früher mal als Lagerprodukt deklariert war und das Feld "Bestellbar bis zu einer Anzahl von" einen Wert < 0 enthielt.
- Es gab immer wieder Probleme beim automatischen Vermindern der Anzahl, wenn im gleichen Warenkorb ein Produkt mit einer Variante vorhanden war und dieses Produkt genau vor dem entsprechenden Produkt gereiht war. War schwer zu finden... / [PR#484](https://github.com/foodcoopshop/foodcoopshop/pull/484) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

Datum: 26.03.2020 / [Alle Änderungen anzeigen](https://github.com/foodcoopshop/foodcoopshop/compare/v3.0.1...v3.0.2)

# v3.0.1

### Bugfix
- Kategorien wurden nicht korrekt sortiert. / [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/35d940d82912200d6aab60dd6adc5fedbb68b4de) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

Datum: 22.03.2020 / [Alle Änderungen anzeigen](https://github.com/foodcoopshop/foodcoopshop/compare/v3.0.0...v3.0.1)

# v3.0.0

### Herzlichen Dank an alle beteiligten Personen
* [AndreasEgger](https://github.com/AndreasEgger)
* [mrothauer](https://github.com/mrothauer)

### Neue Funktionen
- Bei Produkten kann nun als Anzahl "immer verfügbar" eingestellt werden. Weiters kann mittels "Standard-Anzahl pro Lieferrhythmus" festgelegt werden, auf welche verfügbare Anzahl nach erfolgtem Bestelllisten-Versand automatisch wieder hochgezählt wird. Details in der [Online-Dokumentation](https://foodcoopshop.github.io/de/produkte). [I#452](https://github.com/foodcoopshop/foodcoopshop/issues/452) / [I#324](https://github.com/foodcoopshop/foodcoopshop/issues/324) / [PR#457](https://github.com/foodcoopshop/foodcoopshop/pull/457) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / <a href="https://github.com/AndreasEgger"><img src="https://github.com/AndreasEgger.png" width="20"></a>
- Das Hauptmenü des Frontends wird nun eingeblendet, sobald man nach oben scrollt. [I#438](https://github.com/foodcoopshop/foodcoopshop/issues/438) / [PR#440](https://github.com/foodcoopshop/foodcoopshop/pull/440) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Produkte von Sammelbestellungen, bei denen die Bestellfrist bereits erreicht wurde, können über die Sofort-Bestellung jetzt trotzdem bestellt werden. Das ist praktisch für Nachbuchungen. [I#443](https://github.com/foodcoopshop/foodcoopshop/issues/454) / [PR#454](https://github.com/foodcoopshop/foodcoopshop/pull/440) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Bugfixes / Updates
- Die Auto-Login-Funktion ("Angemeldet bleiben") hat nicht mehr richtig funktioniert. [I#439](https://github.com/foodcoopshop/foodcoopshop/issues/439) / [PR#444](https://github.com/foodcoopshop/foodcoopshop/pull/444) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Beim Löschen eines Mitgliedes werden die Bestellungen nun auf "mit dem Hersteller verrechnet" überprüft (und nicht mehr, ob sie 2 Monate alt sind). Weiters wird überprüft, ob die Guthaben-Aufladungen der letzten zwei Jahre bestätigt sind. [I#451](https://github.com/foodcoopshop/foodcoopshop/issues/451) / [PR#456](https://github.com/foodcoopshop/foodcoopshop/pull/456) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Unter "Aktivitäten" wird ab sofort bei Einträgen des Rechnungsversands die korrekte Uhrzeit angezeigt. Diese war bisher auf 00:00 gesetzt. [I#451](https://github.com/foodcoopshop/foodcoopshop/issues/451) / [PR#455](https://github.com/foodcoopshop/foodcoopshop/pull/455) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- FoodCoopShop verwendet jetzt [CakePHP v4.0](https://book.cakephp.org/4/en/index.html). [I#445](https://github.com/foodcoopshop/foodcoopshop/issues/445) / [PR#446](https://github.com/foodcoopshop/foodcoopshop/pull/446) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- FoodCoopShop ist jetzt mit PHP 7.4 kompatibel. [I#448](https://github.com/foodcoopshop/foodcoopshop/issues/448) / [PR#449](https://github.com/foodcoopshop/foodcoopshop/pull/449) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Begrenzung der maximalen Zeichenanzahl bei Seiten, Blog-Artikel, Produkt- und Herstellerbeschreibung wurde erhöht. [I#460](https://github.com/foodcoopshop/foodcoopshop/issues/460) / [PR#462](https://github.com/foodcoopshop/foodcoopshop/pull/462) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Neue Kategorien waren nicht mehr alfabetisch sortiert. [I#458](https://github.com/foodcoopshop/foodcoopshop/issues/458) / [PR#459](https://github.com/foodcoopshop/foodcoopshop/pull/459) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Legacy-Code von FCS v2 wurde entfernt. [PR#468](https://github.com/foodcoopshop/foodcoopshop/pull/468) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

Datum: 20.03.2020 / [Mehr Details zum Release](https://github.com/foodcoopshop/foodcoopshop/projects/12) / [Alle Änderungen anzeigen](https://github.com/foodcoopshop/foodcoopshop/compare/v2.7.1...3.0.0)

[Zum Changelog von FoodCoopShop v2.x](devtools/CHANGELOG-v2.md)
