<h1 align="center">
  <a href="https://www.foodcoopshop.com"><img src="https://raw.githubusercontent.com/foodcoopshop/foodcoopshop/develop/webroot/files/images/logo.png" alt="FoodCoopShop"></a>
</h1>

# Changelog v3.x

Das Format basiert auf [keepachangelog.com](http://keepachangelog.com) und verwendet [Semantic Versioning](http://semver.org/).

### Herzlichen Dank an alle beteiligten Personen

* <img src="https://github.com/mrothauer.png" width="20"> [mrothauer](https://github.com/mrothauer)

### Neue Funktionen / Verbesserungen
- Superadmins können Tag und Uhrzeit der Cronjobs (z.B. die automatische Bestell-Erinnerung, Rechnungsversand) jetzt selber im Admin-Bereich (Homepage-Verwaltung / Einstellungen / neuer Tab "Cronjobs") ändern. [I#860](https://github.com/foodcoopshop/foodcoopshop/issues/860) / [PR#74](https://github.com/foodcoopshop/foodcoopshop/pull/874) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Konfiguration "Freitag Bestellschluss / Samstag Bestelllisten-Versand / Donnerstag Abholtag" ist jetzt möglich. [I#866](https://github.com/foodcoopshop/foodcoopshop/issues/866) / [PR#867](https://github.com/foodcoopshop/foodcoopshop/pull/867) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### For developers
- 🐳 The new [Docker Dev Environment](https://foodcoopshop.github.io/en/docker-dev-environment.html) makes contributing much easier. Try it out! [I#871](https://github.com/foodcoopshop/foodcoopshop/issues/871) / [PR#76](https://github.com/foodcoopshop/foodcoopshop/pull/876) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>


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

Datum: 12.09.2022 / [Mehr Details zum Release](https://github.com/foodcoopshop/foodcoopshop/projects/17) / [Alle Änderungen anzeigen](https://github.com/foodcoopshop/foodcoopshop/compare/v3.4.2...v3.5.0)

[Zum Changelog von FoodCoopShop v3.0-v3.4](devtools/CHANGELOG-v3.md)
