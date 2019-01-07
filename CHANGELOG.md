# Changelog

Das Format basiert auf [keepachangelog.com](http://keepachangelog.com) und verwendet [Semantic Versioning](http://semver.org/).

## Unveröffentlichte Änderungen

### Herzlichen Dank an alle beteiligten Personen 
* [AndreasEgger](https://github.com/AndreasEgger)
* [mrothauer](https://github.com/mrothauer)
* [SigiK](https://github.com/SigiK)
* [Speis-Vorchdorf](https://github.com/Speis-Vorchdorf)

### Neue Funktionen
- Das Aktivieren einer Lieferpause ist nur mehr möglich, wenn für den gewünschten Liefertag noch keine Bestellungen vorliegen. [PR#303](https://github.com/foodcoopshop/foodcoopshop/pull/303) / [I#297](https://github.com/foodcoopshop/foodcoopshop/issues/297) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> 
- Möglichkeit, bei Rechnungen Lagerprodukte explizit nicht anzufüren (app.includeStockProductsInInvoices). Außerdem ist die Übersicht für den Finanzverantwortlichen nun unter "Aktivitäten" zu finden und es ist möglich, die Rechnung herunterzuladen. / [PR#291](https://github.com/foodcoopshop/foodcoopshop/pull/291) / [PR#294](https://github.com/foodcoopshop/foodcoopshop/pull/294) / [I#289](https://github.com/foodcoopshop/foodcoopshop/issues/289) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> <a href="https://github.com/AndreasEgger"><img src="https://github.com/AndreasEgger.png" width="20"></a>
- Im Admin-Bereich werden die E-Mail-Adressen von Herstellern und Mitgliedern nach Klick auf den Button automatisch in die Zwischenablage kopiert. / [PR#287](https://github.com/foodcoopshop/foodcoopshop/pull/287)  / [I#254](https://github.com/foodcoopshop/foodcoopshop/issues/254) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Bugfixes
- Bei manchen MySQL-Versionen bzw. -Konfigurationen hat das Hinzufügen eines neuen Herstellers nicht funktioniert. [PR#301](https://github.com/foodcoopshop/foodcoopshop/pull/301) / [I#288](https://github.com/foodcoopshop/foodcoopshop/issues/288) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> <a href="https://github.com/SigiK"><img src="https://github.com/SigiK.png" width="20"></a>
- Produkte, die vor dem "Bestellbar-bis"-Datum in den Warenkorb gelegt wurden, konnten nach Ablauf dieses Datums auch bestellt werden. / [PR#292](https://github.com/foodcoopshop/foodcoopshop/pull/292) / [I#290](https://github.com/foodcoopshop/foodcoopshop/issues/290) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> <a href="https://github.com/Speis-Vorchdorf"><img src="https://github.com/Speis-Vorchdorf.png" width="20"></a>

### Für Entwickler
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
* [veronimus](https://github.com/veronimus)

### Neue Funktionen
- Für Produkte, bei denen das Gewicht zum Zeitpunkt der Bestellung noch nicht feststeht, kann der **Preis jetzt auch nach Gewicht** eingeben werden. (z. B. 25 € / kg oder 10 € / 100 g). Bei der Eingabe des tatsächlichen Gewichts wird der Preis dann automatisch berechnet. / [PR#223](https://github.com/foodcoopshop/foodcoopshop/pull/223) / [I#14](https://github.com/foodcoopshop/foodcoopshop/issues/14) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Hersteller können ihre Produkte auch in Stunden anbieten, abgerechnet wird über das neue [Stundenabrechnungs-Modul](https://foodcoopshop.github.io/de/stundenabrechnungs-modul). Beta-Version! / [PR#213](https://github.com/foodcoopshop/foodcoopshop/pull/213) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / <a href="https://github.com/SaibotP"><img src="https://github.com/SaibotP.png" width="20"></a>
- Mitglieder können **vergangene Bestellungen in ihren Warenkorb laden**. In der Bestellerinnerungs-Email befindet sich ein Link, mit dem die letzte Bestellung automatisch in den Warenkorb geladen werden kann. / [PR#215](https://github.com/foodcoopshop/foodcoopshop/pull/215) / [I#74](https://github.com/foodcoopshop/foodcoopshop/issues/74) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / <a href="https://github.com/tantelisl"><img src="https://github.com/tantelisl.png" width="20"></a>
- Mitglieder können jetzt in ihrem Profil ihr eigenes Mitgliedskonto löschen, Superadmins können das auch für andere Mitglieder tun. [PR#226](https://github.com/foodcoopshop/foodcoopshop/pull/226) / [I#29](https://github.com/foodcoopshop/foodcoopshop/issues/29) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Produktpreise können jetzt auch für Nicht-Mitglieder angezeigt werden. / [PR#231](https://github.com/foodcoopshop/foodcoopshop/pull/231) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#210](https://github.com/foodcoopshop/foodcoopshop/issues/210) / <a href="https://github.com/veronimus"><img src="https://github.com/veronimus.png" width="20"></a>
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

## v1.5.0 2017-12-18 / [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v1.4.0...v1.5.0)

### Herzlichen Dank an alle beteiligten Personen
* [christiankaindl](https://github.com/christiankaindl)
* [EvaSee](https://github.com/EvaSee)
* [k-pd](https://github.com/k-pd)
* [MacPac](https://github.com/MadPac)
* [mrothauer](https://github.com/mrothauer)
* [veronimus](https://github.com/veronimus)

### Neue Funktionen
- Sofort-Bestellungen und Pfand-Rückgabe sind jetzt auch in der Liste "Bestellte Produkte" erreichbar. Das spart Zeit beim Abholen der Produkte. Bei der Sofort-Bestellung ist das Mitglied vorausgewählt. / [PR#163](https://github.com/foodcoopshop/foodcoopshop/pull/163) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#155](https://github.com/foodcoopshop/foodcoopshop/issues/155) / <a href="https://github.com/MadPac"><img src="https://github.com/MadPac.png" width="20"></a>
- Möglichkeit zum Hochladen von Etiketten-Fotos für die lange Produktbeschreibung (Lebensmittelkennzeichnung). / [PR#170](https://github.com/foodcoopshop/foodcoopshop/pull/170) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- In den Rechnungen scheinen jetzt auch Bestellungen mit dem Status *offen* auf. / [PR#156](https://github.com/foodcoopshop/foodcoopshop/pull/156) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Im Admin-Bereich können jetzt die Produkte aller Hersteller in einer Liste angezeigt und bearbeitet werden. Es wird eine zusätzliche Spalte "Hersteller" angezeigt. / [PR#167](https://github.com/foodcoopshop/foodcoopshop/pull/167) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Eine Änderung von Guthaben-Aufladungen ist nicht mehr möglich, sobald sie bestätigt wurden. / [PR#143](https://github.com/foodcoopshop/foodcoopshop/pull/143) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Falls man uneingeloggt nur für Mitglieder sichtbare Blog-Artikel, Seiten oder Hersteller (bzw. deren Produkte) aufruft, wird jetzt statt der Fehler-Seite das Login-Formular angezeigt. / [PR#154](https://github.com/foodcoopshop/foodcoopshop/pull/154) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Wenn man das Passwort vergessen hat, muss man jetzt zusätzlich auf einen Bestätigungs-Link klicken, bevor das Passwort tatsächlich geändert wird. / [PR#141](https://github.com/foodcoopshop/foodcoopshop/pull/141) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Der Urlaubsmodus heißt jetzt *Lieferpause* und kann somit auch für Lieferpausen außerhalb des Urlaubs verwendet werden. / [PR#159](https://github.com/foodcoopshop/foodcoopshop/pull/159) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#158](https://github.com/foodcoopshop/foodcoopshop/issues/158) / <a href="https://github.com/veronimus"><img src="https://github.com/veronimus.png" width="20"></a>
- Bei den Herstellern können jetzt auch IBANs aus Deutschland eingetragen werden. / [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/25a5eb17fb2008993a9e6fd914348d84e0dcf093) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> 
- Mehr mögliche Kombinationen für Bestelllisten-Versendetag und Liefertag: *Mittwoch-Freitag* / *Dienstag-Freitag* / *Montag-Dienstag*. / [PR#173](https://github.com/foodcoopshop/foodcoopshop/pull/173) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#151](https://github.com/foodcoopshop/foodcoopshop/issues/151) / <a href="https://github.com/christiankaindl"><img src="https://github.com/christiankaindl.png" width="20"></a>
- Anpassungen für die Einbindung des Netzwerk-Moduls in der Version 1.0. / [PR#129](https://github.com/foodcoopshop/foodcoopshop/pull/129) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Bugfixes
- Wenn der Steuersatz eines Produktes 0% betragen hat und geändert wird, wurde der Preis auf € 0 zurückgesetzt. / [PR#153](https://github.com/foodcoopshop/foodcoopshop/pull/153) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bereits hochgeladene Bilder, die durch neue ersetzt wurden, werden jetzt auch am Frontend sofort angezeigt. / [PR#138](https://github.com/foodcoopshop/foodcoopshop/pull/138) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die automatische Backup-Funktion über die konfigurierbare BCC-Adresse hat nicht korrekt funktioniert. / [PR#136](https://github.com/foodcoopshop/foodcoopshop/pull/136) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Gelöschte Unterseiten wurden auf der übergeordneten Seite als Button angezeigt. / [PR#135](https://github.com/foodcoopshop/foodcoopshop/pull/135) / <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a> / [I#131](https://github.com/foodcoopshop/foodcoopshop/issues/131) / <a href="https://github.com/veronimus"><img src="https://github.com/veronimus.png" width="20"></a>

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

### 24.10.2016
- Bugfix: Überprüfung des Urlaubsmodus beim Abschließen einer Bestellung.
- Bugfix: Abmelden-Link hat in der mobilen Version nicht funktioniert.
- Bugfix: Bild-Upload-Fenster war zu klein, wenn noch kein Bild hochgeladen war.
- Einstellung "Wie viele Tage sollen Produkte "als neu markiert" bleiben?": 1 – 14 Tage als Eingabe möglich

### 12.10.2016
- Steuersätze (für Superadmins) und Slideshow-Bilder (für Admins und Superadmins) können jetzt im FoodCoopShop verwaltet werden.
- Das Auswählen des Steuersatzes für die Produkte ist jetzt übersichtlicher.
- Die Mitglieder-Liste ist jetzt nach Namen sortiert
- Bei Herstellern und Blog-Artikeln wird das Logo bzw. das Bild auf der Detail-Seite nur mehr angezeigt, wenn auch eines hochgeladen wurde (und nicht mehr das Logo der Foodcoop).
- Bugfix: Beim Ändern des Preises von Varianten wurde unter "Aktivitäten" immer "von € 0,00 auf …" angezeigt. Die Änderung selbst hat aber funktioniert.
- Bugfix: Negative Werte beim Eintragen von Pfand sind jetzt nicht mehr zulässig (wurden bisher in positive Werte umgewandelt)
- Bugfix: Bei kleinen Bildschirmen und vielen Kategorien war der Speicher-Button beim Ändern der Kategorien nicht mehr sichtbar. Gleiches Problem beim Bild-Upload mit Hochformat-Bildern… behoben!
- Bugfix: Pfeil zum Nach-oben-Scrollen im Admin-Bereich ist wieder da
- Kleinere Bugfixes bei der Seiten-Verwaltung
- CakePHP-Update von 2.8.5 auf 2.9.0
- Alle foodcoop-relevanten Funktionen sind in den FoodCoopShop umgezogen, der Prestashop ist daher nicht mehr verfügbar.

### 02.10.2016
-  Bugfix: Hochladen von PDFs und Bildern im Editor hat nicht funktioniert.

### 28.09.2016
- Varianten und Kategorien können jetzt im FoodCoopShop verwaltet werden
- Der Bestellstatus ist nur mehr von Admins oder Superadmins änderbar, nicht von Mitgliedern
- Adresse des Mitglieds jetzt in der Mitglieder-Liste ersichtlich (Maus über Adressbuch)
- Der Admin-Bereich hat jetzt einen angenehmeren Hintergrund (nur bei großen Bildschirmen sichtbar)
- In der Artikelverwaltung springt man jetzt nach jeder Änderung automatisch auf die Zeile, die bearbeitet wurde. Das ist für Hersteller, die viele Artikel haben, sehr hilfreich.
- Die Blog-Artikel über den Produkten können auf dem Smartphone jetzt nach links und rechts gewischt werden.
- Das Bild bei den Blog-Artikeln ist jetzt rechtsbündig (und damit gleich platziert wie das Logo bei den Herstellern)
- Herbstliches Hintergrundbild nach Klick auf Admin-Berich bzw. Hersteller-Bereich
- Bugfix: Beim Hersteller-Login waren bei den bestellten Artikeln die Summe des Warenwertes und vom Pfand in der falschen Spalte
- Update von zahlreichen externen Javascript-Komponenten

### 14.09.2016
- Lightbox im Admin-Bereich ausgetauscht (Notwendig für die Open-Source-Lizenz vom FoodCoopShop)
- Hersteller können das Profil nicht mehr selbst deaktivieren, sondern nur den Urlaubsmodus verwenden.
- Viele Software-Einstellungen können jetzt von Superadmins selbst vorgenommen werden (Homepage-Verwaltung / Einstellungen).
- Neue Einstellung: "Ist die Bestell-Funktion aktiv?"
- Link auf foodcoopshop.com in Footer und PDF konfigurierbar

### 06.09.2016
- Bugfix: Die Buttons zum Verändern der Menge im Warenkorb waren klickbar, obwohl sie teilweise deaktiviert waren. (Durch ein Browser-Update oder das Update von Bootstrap) => Verursachte zwei fehlerhafte Bestellungen, weil die Menge auf 0 gesetzt werden konnte.

### 29.08.2016
- Die Seiten-Vewaltung ist stark vereinfacht und in den FoodCoopShop umgezogen
- Bugfix: Upload von PDFs funktioniert jetzt im CKeditor

### 27.08.2016
- Bugfix: Registrierung und Mitglieder-Profil-bearbeiten hat teilweise Fehlermeldungen nicht angezeigt
- Bugfix: Telefonnummern werden jetzt korrekt validiert
- Bugfix: Filtern bei Aktivitäten nach Herstellern funktioniert jetzt korrekt
- Bei der Sofort-Bestellung wird als erste Seite die Startseite geladen => schneller
- Aktuelle Version von Bootstrap und Bootstrap-Select

### 17.08.2016
- E-Mails auf Html umgestellt: Preis geändert, Pfand geändert
- Bestellte Anzahl ändern: Hersteller können die Funktion jetzt auch benutzen, weil ab sofort eine E-Mail an das Mitglied verschickt wird. Der Grund ist wie beim Stornieren ein Pflichtfeld.
- Blog-Artikel können jetzt gelöscht werden.
- Im Hersteller-Profil wird eine Nachricht angezeigt, falls der Hersteller auf Urlaub ist.
- Benutzer-Gruppen für Mitglieder können jetzt im FoodCoopShop geändert werden. Es wurde außerdem eine neue Gruppe "Superadmin" eingeführt.
- Berichte zum Überprüfen von Guthaben-Eintragungen, Pfand-Eintragungen und Mitgliedsbeiträgen – nur für Superadmins sichtbar.
- Bugfix: Aktivierte Produkte von deaktivierten Herstellern waren fälschlicherweise bestellbar.
- Bugfix: Beim Urlaubsmodus haben noch ein paar Kleinigkeiten gefehlt
- Bugfix: In der Infobox unter dem Warenkorb wurde teilweise das falsche Abholdatum angezeigt.

### 10.08.2016
- Falls von einem Produkt mehr als 1x pro Bestellung bestellt wurde, ist die Menge nun hervorgehoben (Bestellte Artikel, Bestelllisten, Rechnungen, Bestellungen als PDF). Dies verschafft mehr Klarheit für alle Beteiligten!
- Deaktivierte Kategorien sind jetzt bei der Checkbox-Zuweisung als offline gekennzeichnet.
- Die Sofort-Bestellung ist jetzt auch am Samstag verfügbar. Es wird in die entsprechende Vorperiode rückdatiert
- Hersteller können jetzt selbst im Hersteller-Login unter "Mein Profil" mehr Daten ändern: Logo, Adressdaten für Rechnung und auch die Kontodaten.
- Für Hersteller gibt es jetzt einen Urlaubsmodus. Das Profil scheint auf, die Produkte nicht. "Alle Artikel (de)-aktivieren" gibts nicht mehr.
- Admins können alle Hersteller-Daten jetzt auch im FoodCoopShop ändern und dort auch neue Hersteller anlegen. Das Erstellen eines eigenen Kunden-Kontos ist nicht mehr notwendig.
- Menü im Admin-Bereich ist übersichticher strukturiert: Guthaben und Mitgliedsbeitrag unter "Mein Profil", Artikel unterhalb von Hersteller.
- Der Text-Editor funktioniert jetzt auch auf dem Smartphone (bei Produktbeschreibung, Hersteller und Blog-Artikel)
- Emails auf Html umgestellt: Passwort vergessen, Guthaben-Erinnerung, Rechnungsversand, Info-Mail an Finanzverantwortlichen
- Rechnungs- und Bestelllisten-Versand: Im Aktivitäten-Log scheinen jene Hersteller nicht mehr auf, bei denen das Versenden deaktiviert wurde.
- Rechnungsversand: Die Summe und Anzahl der Produkte scheint jetzt im Aktivitäten-Log auf. (Wie bei den Bestelllisten).
- E-Mail Fallback: Falls sich die Konfiguration der Zugangsdaten der E-Mail-Adresse, über die das System E-Mails verschickt, ändert (z.B. Passwort, Port usw.) werden die E-Mails über eine FoodCoopShop.com – E-Mail-Adresse versendet, bis die Änderungen auch in F behoben ist.

### 06.07.2016
- Die Menüpunkte Bestellte Artikel, Stornierte Artikel und Bestelllisten sind jetzt ein Untermenü vom Menüpunkt Bestellungen
- Hersteller sind jetzt im FCS aktivierbar / deaktivierbar – Beim Aktivieren wird automatisch eine E-Mail mit einem neu generierten Passwort an das Mitglied verschickt.
- Verbessertes Layout für den Admin-Bereich (grüner Hintergrund)
- Fehler- bzw. Statusmeldungen sind jetzt in der Mitte der Seite platziert.
- Bestell-Erinnerungs-E-Mail und Bestelllisten-E-Mail werden jetzt im Html-Format verschickt.
- Buttons für 1 Tag vor bzw. 1 Tag zurück im FCS Admin
- Bugfix: Manchmal konnte nicht bestellt werden "Fehler: Für einige der ausgewählten Adressen steht kein Versanddienst zur Verfügung."
- Bestelllisten und Rechnungen übersichtlicher: Kein Bestellcode und keine Produkt-Detail-Info mehr.

### 08.06.2016
- Frühlingshafteres Hintergrund-Bild
- Bugfix: Neuer Warenbestand nach Ändern von bestellter Menge wurde teilweise falsch berechnet
- Mitglied aktivieren / deaktivieren im FCS möglich
- Stornierungs-E-Mail im HTML-Format

### 02.07.2016 Neue Funktionen in der Version 0.9
- Das aktuelle Guthaben wird im Warenkorb angezeigt
- Konfigurierbares Einkaufslimit, d.h. das Guthaben-Konto kann nur bis zu einem Betrag x überzogen werden.
- Optimierte Darstellung für Smartphones (völlig neu implementiert)
- Bei den Produkten kann die lange Beschreibung unter "Mehr anzeigen" eingeblendet werden
- Das Bestellen muss nicht mehr zwei mal bestätigt werden
- Die 4 aktuellsten Blog-Artikel werden oberhalb der Produkte und nach dem Bestellen angezeigt – so verpassen die Mitglieder nichts Neues
- Nur noch ein Login für Admin-Bereich und Bestellen notwendig
- Übersichtlichere Darstellung der Aktuelles-Seite, vor- und zurückblättern auf den einzelnen Blog-Detail-Seiten ist jetzt möglich
- Produkte sind nun automatisch deaktiviert, wenn der Hersteller deaktiviert ist. Man muss die Produkte also nicht mehr einzeln deaktivieren, wenn der Hersteller z.B. auf Urlaub ist.
- Im Menü der Hersteller und Kategorien wird die aktuelle Anzahl der Produkte in Klammer angegeben, Unterkategorien werden besser dargestellt (Plus-Icon)
- Die Kontonummern im Footer werden nur angezeigt, wenn man eingeloggt ist
- Bei der Registrierung kann jetzt ein Datei-Anhang mitgeschickt werden und neue Mitglieder sind standardmäßig deaktiviert (konfigurierbar). Es kann somit verhindert werden, dass sich jemand anmeldet und sofort bestellt.
- Neu-Button bei neuen Produkten ist jetzt klickbar
- Blog-Artikel sind jetzt bequem für Admins und Hersteller im FoodCoopShop zu erstellen. Zudem gibt es auch die Möglichkeit, Blog-Artikel einem Hersteller zuzuweisen. Diese scheinen dann auf der Hersteller-Seite auf.
- Bessere Performance dank Umstellung auf PHP7
- Hersteller-Verwaltung stark vereinfacht und im FoodCoopShop möglich.

### 02.07.2016 Bugfixes in der Version 0.9
- Mit dem Hersteller-Konto kann jetzt nicht mehr bestellt werden.
- Produkte auf der Neue-Produkte-Seite und der Such-Ergebnis-Seite können jetzt direkt dort in den Warenkorb gelegt werden
- Die Höhe des Warenkorbes passt sich an die Höhe des Bildschirms an, sobald genügend Produkte im Warenkorb sind
- Bestellung bei Summe 0 (Nullpreis-Artikel) ist jetzt nicht mehr automatisch auf Status "bar bezahlt", d.h. Nullpreis-Produkte müssen nicht mehr 0,01 € kosten.
- Die Sofort-Bestellung ist neu implementiert und sollte von nun an fehlerfrei funktionieren.
- Nach einer Sofort-Bestellung ist man jetzt nicht mehr als das Mitglied, in dessen Namen bestellt wurde, eingeloggt.
- Wenn zu einem Hersteller kein Logo hochgeladen wurde, erscheint jetzt das Logo der Initiative und nicht mehr das große Fragezeichen

### 02.07.2016
- fairteiler-scharnstein.at läuft auf FoodCoopShop v0.9

### 30.05.2016
- Das Produkt-Dropdown auf den Seiten "Aktivitäten", "Bestellte Artikel" und "Artikel" wird jetzt nachgeladen (Ladezeit-Optimierung)
- Beim Eintragen der Mitgliedsbeiträge kann jetzt auch der Zeitraum ausgewählt werden (noch nicht aktiviert)
- Option "Angemeldet bleiben" beim Login-Formular (für 6 Tage)
- Beim Eintragen von bargeldlosem Pfand kann jetzt eine Anmerkung hinzugefügt werden

### 12.05.2016
- Ändern der Mitglieder-Daten (Name, E-Mail, Straße, Telefonnummer) im FCS ("Mein Profil")
- Ändern des Passwort im FCS geändert werden (Unterpunkt von "Mein Profil")
- Nach-Oben-Button im FCS (unterhalb von Menü links)
- E-Mail-Bestell-Erinnerung anstelle von SMS-Bestell-Erinnerung
- Modul zur Verwaltung der Mitgliedsbeiträge (kann ab Juli 2016 verwendet werden)

### 27.04.2016
- Bugfix: Login hat für Internet Explorer nicht funktioniert

### 23.04.2016
- FCS / Bestelllisten: hier stimmt jetzt an jedem Tag der vorausgewählte Abholtag
- Bugfix: Bestellung stornieren hat nicht mehr funktioniert
- Prestahop-Modulupdates
- Einheitliche, schönere Buttons
- Produkte und Warenkorb können für uneingeloggte Besucher ausgeblendet werden

### 11.04.2016
- FoodCoopShop verwendet jetzt gepacktes CSS und Javascript und müsste um einiges schneller laden.
- Schönere Fehler-Seite

### 30.03.2016
-  FoodCoopShop kann jetzt auch mit Bestellschluss Montag (und nicht nur Dienstag) Mitternacht umgehen.

### 29.03.2016
- Filter-Leiste im FCS bleibt beim Scrollen fix oben stehen (so bleiben lange Listen übersichtlich)

### 21.03.2016

- Bugfix: Telefonnummer bei Hersteller konnte nicht mit Schrägstrich eingegeben werden
- Im FoodCoopShop ist die Sortierung nach Mitglieder-Nachname jetzt möglich
- Email-Adresse für Superadmin jetzt konfigurierbar
- Menüpunkt "Einstellungen" nur mehr für Superadmins
- formatAsEuro Refactoring

### 29.02.2016
- Symbol neben Name des Mitgliedes, wenn es weniger als 4 Bestellungen hat. Damit der Shopdienst Bescheid weiß und sich besser drum kümmern kann.
- In den FCS-Einstellungen wird jetzt die letzte Aktualisierung der Software und die letzte Quellcode-Änderung angezeigt (damit man weiß, welche Version gerade verwendet wird). Desweiteren wird ab sofort bei jedem Deploy ein Log-Eintrag "Update eingespielt" erzeugt.

### 22.02.2016
- Zusätzlicher Text für Hersteller-Rechnungen (für pauschalierte Betriebe) möglich. Über Prestashop oder Hersteller-Login bearbeitbar.

### 15.02.2016
- Automatische Weiterleitung von FCS-Home auf /admin
- Login-Seite war auf Smartphone nicht zentriert
- Hersteller sehen nur mehr die offenen Bestellungen der aktuellen Woche (und nicht mehr alle offenen Bestellungen)
- Bestellte Artikel sind jetzt nach Hersteller gruppiert, bestellte Artikel von Sammelbestellungen sind ausgegraut (übersichtlicher)
- Die UID-Nummer des Herstellers kann jetzt gespeichert werden und wird auf im Footer der Rechnung angeführt.

### 10.02.2016
- CakePHP-Update auf Version 2.7.9
- TCPDF-Update auf Version 6.2.12
- Umstrukturierung der bestehenden Cake-Anwendung in das Plugin "Admin"
- Auf der Mitglieder-Liste ist der Pfand im Guthaben bereits eingerechnet, somit werden die tatsächlichen Guthaben angezeigt.
- Produkt-Id in Produkt-Liste wird nicht mehr angezeigt
- In der Liste "Bestellte Artikel" wird im Hersteller-Login die Spalte "Hersteller" nicht mehr angezeigt
- Bugfix: lange Mitglieder-Namen haben Seite mit den Bestellungen unübersichtlich gemacht

### 28.01.2016
- Wenn ein Mitglied storniert, wird ein Hinweis anzezeigt, dass man nur stornieren darf, wenn es mit dem Hersteller abgesprochen ist.
- Stornieren und Preis von bestelltem Artikel ändern: Grund ist jetzt Pflichtfeld (nachvollziehbarer).
- Die Erfassung von Nutzerdaten mittels Google Analytics ist ab sofort nicht mehr möglich. Es kann allerdings eine datenschutztechnisch bessere Open-Source-Software Piwik verwendet werden. Falls dies gewünscht ist, bitte mir die Url mitteilen.

### 20.01.2016
- Falls der Preis eines bestellten Nullpreis-Artikels geändert wird, geht keine Mail an den Hersteller raus.

### 17.01.2016
- Es besteht ab sofort die Möglichkeit, jeden Mitwoch, Donnerstag und Freitag Bestelllisten per Mausklick zu versenden. Dies ist sinnvoll, falls nach dem automatischen Versenden der Bestelllisten neue Bestellungen dazugekommen sind und der Hersteller bis zum Lieferdatum noch reagieren kann. Bitte mir Bescheid sagen, falls diese Funktion erwünscht ist.
- BCC-Empfänger für Registrierungs-E-Mails kann jetzt definiert werden

### 12.01.2016
- Beim Korrigieren des Preises im Nachhinein (d.h. für bereits bestellte Artikel) kann nun wie beim Stornieren ein Grund angegeben werden. Außerdem werden E-Mails an Hersteller und Mitglied versendet.

### 30.12.2015

Bestelllisten können nur mehr von Admins geöffnet werden
- Bestellungen können jetzt auch auf der Seite "Bestellte Artikel" abgeschlossen werden, das vereinfacht den Shopdienst enorm! Der Button befindet sich unterhalb der Artikel-Liste und wird nur angezeigt, wenn kein Filter nach Hersteller oder Artikel gesetzt ist und nach Mitglied gefiltert ist.
- Es werden nur noch die notwendigen Bestellstati angezeigt und bei eindeutiger Konfiguration (entweder bargeldlos oder bar) wird "abgeschlossen" anstelle von "bar bezahlt" bzw. "überwiesen" angezeigt.
- Wird der Steuersatz geändert, wird ab sofort der alte Steuersatz unter "Aktivitäten" gespeichert. Zudem wird unter "Aktivitäten" der tatsächliche Steuersatz angezeigt und nicht der Name der Steuerregel.

### 22.12.2015
- Vertikale Navigation im FoodCoopShop (notwendig für Umbau auf Open-Source)
- Logo der Foodcoop wird im FoodCoopShop immer angezeigt (damit Hersteller, die mehrere Foodcoops belieferen, ihre Daten / Artikel nicht versehentlich bei der falschen Foodcoop eintragen)
- Wenn der Preis eines Artikels 0 ist, ist er in Artikelliste rot hinterlegt
- Benachrichtigung schließt sich automatisch nach 7,5 Sekunden selbst
- Checkboxen größer (Chrome, IE)
- Ids in Listen werden nicht mehr angezeigt (Bestellte Artikel, Bestellungen, Mitglieder, Hersteller, Aktivitäten) => bessere Übersichtlichkeit
- Hilfesymbol neben Drucksymbol, Layout der Buttons verbessert
- Anzahl in Bestelllisten und Rechnungen in erster Spalte => bessere Übersichtlichkeit

### 09.12.2015
- Rechte-Verwaltung für Mitglieder im FoodCoopShop (Admin bzw. Mitglied)
- Grund: weniger Rechte für Mitglieder, die nur einkaufen und keinen Shopdienst machen bzw. sonst aktiv mitarbeiten.
- Standardeinstellung für neue Mitglieder ist konfigurierbar.

### 06.12.2015
- Pfand wird ab sofort zu jeder einzelnen Bestellung gespeichert und ist nicht mehr abhängig vom aktuellen Pfand des Produktes.
- Bei Stornierung gehen die E-Mails nur noch mittwochs, donnerstags und freitags an den Hersteller. Das Mitglied wird immer benachrichtigt. Ist bei einem Hersteller "Sammelbestellungen möglich" aktiviert, bekommt dieser niemals Storno-E-Mails.
- FoodCoopShop ist gerüstet für bargeldlosen Pfand ab 01.01.2016

### 27.11.2015
- Anzahl in Bestellbestätigungs-Mail an erster Stelle (übersichtlicher beim Einkaufen)

### 22.11.2015
- Sammelbestelungen können ab sofort einfacher getätigt werden, genaueres unter Support / Diverses

### 19.11.2015
- Bugfix: Wenn Varianten hinzugefügt und dann die letzte Variante wieder gelöscht wurde, war Artikel nicht bestellbar.
- Bugfix: Varianten mit Menge 0 konnten gar nicht bestellt werden (auch keine andere Variante)
- Varianten mit Menge 0 werden beim Bestellen jetzt nicht mehr angezeigt
- Prestashop Modul Updates

### 04.11.2015
- Zusätzliches Feld "Einheit" für Artikel ohne Varianten
- Copyright-Hinweis bei Bildupload
- Pfand des Hauptproduktes wird im FCS nicht mehr angezeigt, falls es auch Varianten gibt
- Der Button zum Abschließen aller angezeigten Bestellungen ist zurück

### 28.10.2015
- Prestashop-Update auf Version 1.6.1.1 für alle Installationen
- Bei Verminderung der bestellten Menge ist jetzt der Name des bestellenden Mitglieds im Log ersichtlich
- Bugfix: Manchmal wurden Guthaben-Erinnerungs-E-Mails verschickt, obwohl der Kontostand auf 0 war
- Bugfix: Trennzeichen beim E-Mail-Export von ";" auf "," geändert

### 16.10.2015
- Prestashop-Update auf Version 1.6.1.1 beim Fairteiler online
- Such-Index wird jetzt täglich um Mitternacht aktualisiert

### 07.10.2015
- Aufwandsentschädigung für Hersteller kann jetzt global definiert werden (app.defaultCompenstationPercentage)
- Prozentsatz der Aufwandsentschädigung wird jetzt unter "Bestellte Artikel – Gruppiert nach Hersteller" und bei "Hersteller" angezeigt
- Bugix: Unterkategorien haben ab und zu Fehler erzwungen
- Unterkategorien werden jetzt übersichtlicher dargestellt (standardmäßig eingeklappt)

### 06.10.2015
- Bugfix: Hochformat-Bilder wurden auf Listen-Seiten zu groß angezeigt (bei neuem Bild-Upload)

### 05.10.2015
- Bugfix: Artikel anlegen hat im FCS unter bestimmten Umständen nicht funktioniert

### 30.09.2015
- Bequemes Ändern der Hersteller-Optionen in der FCS-Hersteller-Liste
- Zusätzliche Option für Hersteller: Voreingestellter Steuersatz für neue Artikel
- Rechnung-Versand an Hersteller kann deaktiviert werden

### 29.09.2015
- Bugfix: SMS-Bestell-Erinnerung kann jetzt wieder selbstständig aktiviert bzw. deaktiviert werden
- Guthaben-Liste jetzt übersichtlicher

### 28.09.2015
- FCS: Bei der Hersteller-Liste wird jetzt die Anzahl der aktiven Artikel angezeigt
- FCS: Bei Produktbeschreibung ändern wird jetzt ein Hinweis angezeigt, dass Mengenangaben in die ersten Zeile zu schreiben sind (damit die Mengeneinheit mit auf die Bestellbestätigung kommen).

### 24.09.2015
- Bessere Implementierung des Datenbank-Backup-Skripts (mit E-Mail-Versand des Dumps)

### 21.09.2015
- Erinnerungs-SMS ist jetzt konfigurierbar (scheint somit nicht mehr bei der Registrierung auf, wenn nicht aktiv)
- Erinnerungs-SMS standardmäßig nicht mehr angehakt bei Registrierung
- Vorname und Nachname bei Adressen können jetzt Titel enthalten
- FCS: User bleiben jetzt länger eingeloggt

### 18.09.2015
- Artikel-Bildupload inkl. Bild-Drehen-Funktion ab sofort im FoodCoopShop

### 16.09.2015
- Performance-Optimierung: FCS-Artikelliste
- Facelifting FCS-Mitglieder-Liste

### 14.09.2015
- Bei Fehlern im FCS wird ab sofort eine automatisierte E-Mail an mich versendet.
- Prestashop Modul Updates
- FCS: Hersteller-Seite übersichtlicher
- Beim Einfügen einer neuen Varianten im FCS wird jetzt der Preis des Artikels auf 0 gesetzt, ansonsten wurde der alte Preis im Shop immer addiert, wenn man ihn vorher nicht zurückgesetzt hat.

### 08.09.2015
- Bugfix: Pfand für Varianten konnte nicht geändert werden (hinzugefügt schon)
- Land und productQuantityToCountAsAlmostSold in Konfiguration sichtbar
- Logout-Button jetzt mit Bestätigung

### 03.09.2015
- Varianten können jetzt im FoodCoopShop wieder gelöscht werden

### 02.09.2015
- Unter Bestellungen befindet sich jetzt ein neuer Button "Bestellungen als PDF generieren". Damit können alle Bestellungen komfortabel auf einmal gedruckt werden.

### 01.09.2015
- zusätzliche Spalte "Guthaben" in Mitgliederliste
- Summe der Spalten "SMS" und "Bestellungen" in Mitgliederliste

### 24.08.2015
- Bilder von Artikeln werden im FoodCoopShop angezeigt.

### 18.08.2015
- Hinzufügen von Artikel-Varianten und ändern der Standard-Variante im FoodCoopShop
- Bugfix: Bei Rechnungen wurde ab und zu in der Übersicht der falsche Steuersatz angezeigt. Die Beträgen haben aber gestimmt.
- Besseres Layout für Varianten im FoodCoopShop
- Artikel mit der Menge 0 sind jetzt rot gekennzeichnet und können so nicht mehr mit deaktivierten Artikeln verwechselt werden.

### 13.08.2015
- Buttons nach Klick deaktiviert (Doppelklick vermeiden)
- Bugfix: Bei Änderung der Anzahl bei Varianten (im FCS) wird jetzt die Gesamtmenge des Produkts automatisch berechnet (damit der Artikel in der Detailansicht bestellbar bleibt).

### 25.07.2015
- Bugfix: Sofort-Bestellung hat nicht immer funktioniert
- Bugfix: Stornierungs-Mails wurden seit 20. Juli nicht verschickt

### 20.07.2015
- Prestashop – Mein Konto: SMS-Benachrichtigung änderbar
- Logo und Name auf der FCS-Login-Seite
- Bei Stornierungen kann jetzt ein Grund angegeben werden, der per Mail verschickt wird.
- Bugfix: Sonderzeichen werden automatisch aus Produknamen entfernt (verursachte Fehler bei Bestellung)
- Menüpunkt "Bestellungen" im Prestashop entfernt (es gibt jetzt die Sofort-Bestellung)
- Der Menüpunkt "Artikel" wird im FoodCoopShop jetzt bei allen Mitgliedern angezeigt
- Hersteller-Login: Neuer Menüpunkt "Stornierte Artikel", damit Hersteller sehen, wer wann ihre Artikel storniert hat.
- Wenn ein Artikel von einem Mitglied storniert wird, wird eine Kopie der Stornierungs-Mail an den Hersteller verschickt.

### 14.07.2015
- Anzahl bestellter Artikel änderbar
- Vereinfachte Sofort-Bestellung (Nachbestellung am Abholtag)

### 12.07.2015
- Bestellungen nur mehr stornierbar, wenn keine bestellten Artikel mehr vorhanden sind.
- Bestellstatus "überwiesen" und "bar bezahlt" sind jetzt linksbündig
- Bugfix: Wenn Bestellung fälschlicherweise storniert wurde, konnte sie über "Aktivitäten" nicht mehr aufgerufen und geändert werden.

### 08.07.2015
- Modul-Update Facebook Like-Box im Footer

### 06.07.2015
- Warenkorb bleibt auch bei Smartphones rechts oben stehen
- Kommentare zu Mitgliedern möglich (FCS / Mitglieder)

### 30.06.2015
- Anlegen von Artikeln jetzt im Hersteller-Login möglich (Varianten noch nicht)
- Die Zahlungsmethoden sind jetzt konfigurierbar, das heißt man sieht im FoodCoopShop nur noch die Buttons, die man auch braucht. Verwendbar sind "bar", "bargeldlos" oder "beides".
- Ganze Bestellungen können jetzt storniert werden (man muss nicht mehr alle Artikel einzeln stornieren). Unter "Bestellstatus ändern" – storniert. Achtung. Die Bestellung ist dann nicht mehr aufrufbar (aber weiterhin in der Datenbank gespeichert)!

### 22.06.2015
- Pfand und Hersteller in Bestellbestätigungs-Mail
- Der Preis bereits bestellter Artikel kann nun im Nachhinein geändert werden (nur bei offenen Bestellungen)
- Login-Timeout im FoodCoopShop auf 60 Minuten erhöht

### 01.06.2015
- Pfand wird ab sofort im Shop automatisch zu jedem Artikel (inkl. Varianten) angezeigt.
- "Neue Produkte" als erster Untermenüpunkt von "Produkte"

### 25.05.2015
- Hersteller-Login: Artikel können nun als "neu" markiert werden
- Menüpunkt "Aktivitäten" nun auch für alle Mitglieder (wichtig für Shopdienst)
- Name des Mitglieds ist jetzt im Aktivitäten-Eintrag beim Ändern des Bestellstatus (bessere Rückverfolgbarkeit)
- Guthabensystem: Bestellungen verlinkt mit bestellten Artikeln (für bessere Transparenz)

### 17.05.2015
- Neues Modul "Bargeldlos zahlen" ist online
- überall deutsches Datumsformat

### 09.05.2015 
- Steuersatz im FoodCoopShop änderbar
- Bestellungen können ab sofort im Shopdienst als bezahlt markiert werden
- Vereinfachte Hersteller-Liste für Shopdienst (für Telefonnummern falls mal jemand nicht liefert)

### 21.04.2015
- Das Rückdatieren von Bestellungen wird jetzt geloggt
- Besseres Layout für Artikel-Liste (Artikel und Varianten besser getrennt)
- Das Ändern der Steuer ist jetzt testweise online (Fairteiler Scharnstein, Demo).

### 17.04.2015
- Im Shop wird jetzt beim Produkt die verfügbare Anzahl angezeigt, sofern weniger als 10 Stück vorhanden sind.
- Hintergrundbild auf Home und Login-Seite im FoodCoopShop
- Content im FoodCoopShop wird erst angezeigt, wenn er geladen ist (bessere Darstellung)

### 15.04.2015
- Implementierung einer Aufwandsentschädigung für Hersteller (optional). Hier geht’s zur Dokumentation (ganz unten)

### 07.04.2015
- Es können BCC E-Mail-Adressen (Backup) für Versand von Rechnungen, Bestell-Listen und Storno-Mails angegeben werden.
- Aktuelles-Beiträge auf Home und aktuelles.html: Sortierung jetzt nach Geändert-Datum (um wiederkehrende Beiträge wieder einfach nach vorne zu bringen)
- kleine Layout-Verbesserungen der Aktuelles-Beiträge

### 31.03.2015
- Kategorien können jetzt direkt im FCS (also auch im Hersteller-Login) geändert werden

### 26.03.2015
- Frei definierbarer HTML-Text auf der Registrierungs- bzw. Login-Seite (FCS_AUTHENTICATION_INFO_TEXT)
- Druck-Symbol im FoodCoopShop
- Produkte mit Menge 0 sind im FCS immer ausgegraut, auch wenn sie aktiviert sind
- FCS: ActionLogs jetzt mit Artikel-Filter
- FCS: Layout für Navigation verbessert

### 23.03.2015
- "Pfand" und "Bestellungen abschließen" werden ab sofort mitgeloggt
- Einspielen von Modul-Updates

### 16.03.2015
- FCS: Bestelllisten sind jetzt alfabetisch geordnet
- FCS-Login für User, der Hersteller und Mitarbeiter gleichzeitig ist, ist nicht mehr möglich.
- FCS: Die Logs enthalten jetzt einen Link auf die editierten Produkte bzw. Hersteller
- Aktuelles-Artikel (Blog-Beiträge) scheinen nur mehr dann auf der Startseite auf, wenn das Feld "is featured" aktiv ist. Man kann dadurch Beiträge weiterhin unter dem Menüpunkt "Aktuelles zugänglich machen", die Startseite aber übersichtlich halten.
- Verbesserung des Layouts der Aktuelles-Seite
- Jede Bestellungbestätigung wird im BCC an eine frei zu definierende E-Mail-Adresse geschickt. Im Falle eines System-Ausfalles beim Shopdienst können so die Bestellungen rekonstruiert werden und der Shopdienst kann – wenn auch eingeschränkt – durchgeführt werden. Die E-Mail-Adresse scheint im FoodCoopShop unter "Konfiguration – FCS_ORDER_CONFIRMATION_BCC" auf.

### 12.03.2015
- Nach dem automatisierten Versenden der Rechnungen erhält der Finanz-Verantwortliche der FoodCoop ab sofort per E-Mail einen Link, der übersichtlich anzeigt, wie viel Geld an welchen Hersteller zu überweisen ist. Die E-Mail-Adresse ist frei definierbar.
- Interner Admin-User scheint nicht mehr in der FCS-Mitglieder-Liste und im Dropdown auf => Anzahl der Mitglieder stimmt somit
- Infos zum Hersteller-Login ab sofort in wöchentlichen Bestell-Listen-Emails integriert
- Mitglieder-Liste filterbar nach letztem Bestelldatum

### 11.03.2015
- FCS Hersteller-Liste: Name verlinkt mit Artikel-Liste des Herstellers
- FCS Hersteller-Liste: Pfeil-Icon mit Verlinkung auf die Hersteller-Seite im Frontend
- FCS Mitglieder-Liste: Name verlinkt mit allen abgeschlossenen Bestellungen
- FCS Bestellungen und Bestellte Artikel: Markierung der Zeile jetzt mit Klick auf Checkbox (bessere Usability)
- FCS  Zeilen-Hover (orange) bei Konfiguration und Logs weg

### 08.03.2015
- Besseres Layout für allgemeine Hersteller-Seite und Hersteller-Detail-Seite (größeres Logo, andere Überschrift…)
- Hersteller-Login: Hersteller können ihre Beschreibung jetzt selbst ändern (inkl. Bild-Upload)

### 07.03.2015
- Feld "Bestellcode" nicht mehr in "Bestellungen" bzw. "Bestellte Artikel" (wurde nie verwendet)
- Bugfix: Hersteller-Name war in Artikel-Storno-Mails nicht mehr vorhanden
- Bugfix: Neues-Passwort-Mail: Leerzeichen am Ende des Passworts wird nicht mehr mitkopiert
- Wenn die Variante eines Produktes nicht verfügbar ist (Menge 0), dann erhält der Benutzer bei Klick auf Radio-Button eine Fehlermeldung
- Wenn das Produkt (ohne Varianten) nicht verfügbar ist (Menge 0), dann erscheint unterhalb des In-den-Warenkorb-Buttons eine Fehlermeldung

### 02.03.2015
- Logo und Telefonnummer bei Hersteller-Liste in FoodCoopShop
- Keinere Layout-Verbesserungen am Frontend

### 25.02.2015
- Bei den Herstellern ist der Mehr-Lesen-Link besser hervorgehoben, außerdem wird die lange Beschreibung automatisch angezeigt, wenn weniger als 5 Artikel online sind.

### 23.02.2015
- Logs sind jetzt filterbar nach User
- Cronjobs werden in Logs gespeichert
- Neuer Menüpunkt "Stornierte Artikel"
- WYSIWIG-Editor für das Bearbeiten der Produkt-Beschreibung im FoodCoopShop
- Bei Stornierung eines Artikels berichtigt (erhöht) sich die Menge jetzt automatisch
- Bei der Bestellliste nach Artikel (PDF) sind jetzt die Mitglieder nach Vorname sortiert, was das Einräumen in die Kisterl veranfacht
- Das Ändern der Artikelbeschreibung wird im Log besser dargestellt

### 17.02.2015
- User-Aktionen wie zB. Artikel-Stornierung, Artikel-Änderung (Preis, Menge, Name, Beschreibung) werden im neuen FCS-Menüpunkt "Logs" angezeigt. Dies war besonders für die Artkel-Stornierung wichtig, da sonst im Nachhinein nicht mehr festgestellt werden konnte, welcher Artikel wann und von wem storniert wurde.

### 15.02.2015
- Hersteller haben Zugriff auf E-Mail-Adressen von Mitgliedern, die bestellt haben. Dies ist praktisch, falls Mal Kunden angeschrieben werden müssen, weil es Fragen zur Bestellung gibt.
- Artikel-Dropdown im FoodCoopShop ist jetzt nach Hersteller gefiltert, wenn die Liste auch nach Hersteller gefiltert ist
- Redirects der alten Subdomain foodcoopshop.example.com zur neuen Subdomain fcs.example.com

### 09.02.2015
- Bestelllisten können von nun an auch an eine beliebige Anzahl von CC-Empfängern versendet werden.
- Der Versand von Bestelllisten kann für bestimmte Hersteller deaktiviert werden.
- Dokumentation: http://www.foodcoopshop.com/hersteller-verwaltung

### 18.01.2015
- Rückdatieren von Bestellungen vereinfacht (jetzt mit Dropdown der letzten 5 Tage)
- Aktiviert / Deaktiviert-Filter bei Produkten im FoodCoopShop
- Shopdienst dokumentiert

### 17.01.2015
- Bei den Bestellungen im FoodCoopShop gibt es jetzt die Möglichkeit, die E-Mails von den Mitgliedern, die bestellt haben, zu kopieren (Gruppieren nach Mitglied, Button erscheint rechts unten). Man kann somit Aussendungen an alle Leute schicken, die in einem gewissen Zeitraum bestellt haben.

### 11.01.2015
- Beim Menüpunkt "Artikel" im FoodCoopShop (Hersteller-Login) werden nun Steuersatz und Kategorien des jeweiligen Artikels angezeigt. Sollte die Kategorie "alle Produkte" fehlen, wird darauf hingewiesen. Somit ist sichergestellt, dass auch alle Produkte im Frontend unter "Produkte" angezeigt werden.

### 07.01.2015
- Die automatisiert verschickten Bestelllisten sind ab sofort im FoodCoopShop unter dem Punkt "Bestelllisten" aufrufbar, mit Datumsfilter.

### 06.01.2015
- Beim Löschen von Varianten bleibt der Pfand jetzt richtig verknüpft und scheint weiterhin in der Liste auf.

### 05.01.2015
- Rechnungen beinhalten ab sofort alle Bestellungen vom letzten Monat (erster bis letzter Tag) und werden jeden 10. des Folgemonats automatisiert verschickt. Weiters werden sie in einer übersichtlichen Ordner-Struktur am Server gespeichert, was den Jahresabschluss vereinfacht.
- Der Link "Unsere Filialen" heißt jetzt "Unser Abholllager" (im Footer)

### 02.01.2015
- Bessere Dateinamen für Bestelllisten, Lieferdatum anstelle von Bestellzeitraum im PDF.
- Bestelllisten werden jetzt in einer übersichtlichen Ordner-Struktur auf dem Server gespeichert, wenn sie versendet werden. (Vorbereitung für automatisierten Rechnungs-Versand.)
- "Ja, ich möchte wöchentlich per SMS ans Bestellen erinnert werden." bei Registrierung angehakt.

### 21.12.2014
- Pfand kann jetzt im FoodCoopShop von allen Mitgliedern mit Prestashop-Zugang hinzufügt, geändert und gelöscht werden. Für Produkte und Varianten implementiert.
- Facebook-Box im Footer funktioniert wieder.
