# Changelog v3

Das Format basiert auf [keepachangelog.com](http://keepachangelog.com) und verwendet [Semantic Versioning](http://semver.org/).


## Unveröffentlicht [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v3.0.2...develop)

### Neue Funktionen
- Automatischer Kontoabgleich für das Guthaben-System. [Zur Online-Doku](https://foodcoopshop.github.io/de/guthaben-system-mit-automatischem-kontoabgleich). [I#463](https://github.com/foodcoopshop/foodcoopshop/issues/463) / [PR#474](https://github.com/foodcoopshop/foodcoopshop/pull/474) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Verbesserungen bei der Gewichtsanpassung: Auch gleiches Gewicht ist nach dem Speichern nicht mehr rot hinterlegt. / Bei bereits verrechneten Bestellungen wird das Gewicht niemals rot angezeigt. / Neues Gewicht ist in der E-Mail-Betreffzeile - damit Fehler wie z.B. 540 kg (statt g) schneller auffallen. / Kein E-Mail-Versand falls das Gewicht gleich bleibt. [I#423](https://github.com/foodcoopshop/foodcoopshop/issues/423) / [PR#479](https://github.com/foodcoopshop/foodcoopshop/pull/479) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Es ist jetzt möglich, als Bestellschluss für bestimmte Produkte auch **zwei Tage** vor dem Standard-Bestellschluss auszuwählen. Bisher war das nur für den Vortag möglich. [I#487](https://github.com/foodcoopshop/foodcoopshop/issues/487) / [PR#489](https://github.com/foodcoopshop/foodcoopshop/pull/489) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- 😍 Ab sofort können Emojis verwendet werden: Z.B. in Blog-Artikeln, Seiten oder beim Stornieren. Im Editor gibt's dazu ein neues Icon, beim Tippen von einem Doppelpunkt und zwei Buchstaben wird automatisch eine Liste mit Emojis angezeigt. [I#464](https://github.com/foodcoopshop/foodcoopshop/issues/464) / [PR#478](https://github.com/foodcoopshop/foodcoopshop/pull/478) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Man bleibt jetzt statt 30 Tage lang angemeldet, wenn man die Funkion "Angemeldet bleiben" verwendet. Bisher waren es 6. [I#492](https://github.com/foodcoopshop/foodcoopshop/issues/492) / [PR#493](https://github.com/foodcoopshop/foodcoopshop/pull/493) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Falls Produkte auch für uneingeloggte Mitglieder angezeigt werden, wird nun auch der In-den-Warenkorb-Button angezeigt. Wenn man darauf klickt, erhält man die Meldung, dass man sich zuerst registrieren muss. [I#499](https://github.com/foodcoopshop/foodcoopshop/issues/499) / [PR#500](https://github.com/foodcoopshop/foodcoopshop/pull/500) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Neue Produkte werden nun auch auf der Startseite angezeigt. Das kann in den Einstellungen ausgestellt werden. [I#504](https://github.com/foodcoopshop/foodcoopshop/issues/504) / [PR#506](https://github.com/foodcoopshop/foodcoopshop/pull/506) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

### Bugfixes / Optimierungen
- Horizontales Scrollen auf kleinen Bildschirmen hat das Layout zerschossen. [I#497](https://github.com/foodcoopshop/foodcoopshop/issues/497) / [PR#498](https://github.com/foodcoopshop/foodcoopshop/pull/498) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Die Daten für die Mitglieder-Drodowns im Admin-Bereich werden nun erst bei Klick geladen. Das lädt die Seiten schneller, besonders bei Initativen mit vielen Mitgliedern. [I#477](https://github.com/foodcoopshop/foodcoopshop/issues/477) / [PR#501](https://github.com/foodcoopshop/foodcoopshop/pull/501) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Wenn im Miglieder-Profil das Feld Nachname mehr als 32 Zeichen enthielt, landete man beim Speichern auf einer Fehler-Seite. Jetzt ist das Eingabefeld beschränkt. [I#485](https://github.com/foodcoopshop/foodcoopshop/issues/485) / [PR#488](https://github.com/foodcoopshop/foodcoopshop/pull/488) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Infotext bei der Lieferpause ist jetzt leichter verständlich. [I#469](https://github.com/foodcoopshop/foodcoopshop/issues/469) / [PR#482](https://github.com/foodcoopshop/foodcoopshop/pull/482) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Bestelllisten sind ab und zu nicht über die Fallback-Konfiguration versendet worden. [I#495](https://github.com/foodcoopshop/foodcoopshop/issues/495) / [PR#496](https://github.com/foodcoopshop/foodcoopshop/pull/496) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>
- Der neue PDF-Writer kann nun PDFs unabhängig von Controllern erzeugen (als Attachment, Inline oder File). [I#412](https://github.com/foodcoopshop/foodcoopshop/issues/412) / [PR#508](https://github.com/foodcoopshop/foodcoopshop/pull/508) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>


## v3.0.2 / 2020-03-26 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v3.0.1...v3.0.2)

### Bugfix
- Produkte waren fehlerhafterweise bestellbar, wenn das Produkt früher mal als Lagerprodukt deklariert war und das Feld "Bestellbar bis zu einer Anzahl von" einen Wert < 0 enthielt.
- Es gab immer wieder Probleme beim automatischen Vermindern der Anzahl, wenn im gleichen Warenkorb ein Produkt mit einer Variante vorhanden war und dieses Produkt genau vor dem entsprechenden Produkt gereiht war. War schwer zu finden... / [PR#484](https://github.com/foodcoopshop/foodcoopshop/pull/484) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>

## v3.0.1 / 2020-03-22 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v3.0.0...v3.0.1)

### Bugfix
- Kategorien wurden nicht korrekt sortiert. / [Commit](https://github.com/foodcoopshop/foodcoopshop/commit/35d940d82912200d6aab60dd6adc5fedbb68b4de) <a href="https://github.com/mrothauer"><img src="https://github.com/mrothauer.png" width="20"></a>


## v3.0.0 / 2020-03-20 [View changes](https://github.com/foodcoopshop/foodcoopshop/compare/v2.7.1...3.0.0)

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

[Zum Changelog von FoodCoopShop v2.x](CHANGELOG-v2.md)
