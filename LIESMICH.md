# AiphilosSearch
## Über AiphilosSearch
Dieses Plugin bietet eine Implementierung der [aiPhilos](https://aiphilos.com) Produktsuche für die [Shopware](https://shopware.com/) eCommerce platform.

Es bietet die Synchonisierung zwischen der Shopware Produktdatenbank und der aiPhilos Datenbank. Es unterstützt mehrere Subshops sowie Sprachen (Eine DB Pro Shop/Sprache, momentan unterstützt aiPhilos nur die Deutsche Sprache).


Sucheregebnisse werden über die aiPhilos API ermittelt jedoch wird die Shopware Standardsuche noch verwendet und benötigt.

Kompatibilität mit anderen Plugins welche die Suche erweitern oder ersetzen ist nicht gewährleistet.

## Lizenz

Vertrieben unter den Bedingungen der GNU GPLv3.

Weitere Informationen befinden sich in der [Lizenzdatei](LICENSE.md).

## Installation

Stellen Sie sicher das Sie mindestens Shopware Version 5.2.0 verwenden.

Packen Sie den Inhalt dieses Ordnern in eine Zipdatei und laden Sie ihn über Shopwares Plugin Manager in Ihren Shop hoch.

Von dort aus lässt sich das Plugin wie alle anderen Plugins installieren, deinstallieren, aktivieren etc.

### NHinweis für Shopware Versionen < 5.2.15

Das Shopware 5.2 Pluginsystem ist erst ab der Version 5.2.15 fähig, Cronjobs per cronjob.xml automatisch zu installieren.
Sie können diesen nachträglich manuell installieren indem Sie unter "Einstellungen > Grundeinstellungen > System > Cronjobs" auf die Schaltfläche Hinzufügen klicken, als Namen "Update aiPhilos databases" und als Aktion "Shopware_CronJob_VerignAiPhilosSearchSyncDatabase" angeben.
Stellen Sie den Cronjob so ein, dass er mindestens einmal täglich läuft, aktivieren Sie ihn jedoch noch nicht, bevor Sie nicht das Plugin korrekt konfiguriert haben (siehe unten).

## Konfiguration

### Shopware Konfiguration

Damit Sie nicht Ihr Kontigent an Suchaufrufen grundlos verbrauchen, ist es absolut ratsam die minimale Suchwortlänge in Shopware von 3 Zeichen auf mindestens 5 zu erhöhen.
Dies ist besonders wichtig, da die AJAX Live Suche ansonsten schon bei zu kurzen Benutzereingaben Suchen abfeuert und somit Ihre Anfragen verbraucht mit Eingaben, die zu kurz sind um sinnvoll ausgewertet werden zu können.

Sie können diese Einstellungen unter "Einstellungen > Grundeinstellungen > Storefront > Suche" unter dem Punkt "Minimale Suchwortlänge" finden.

### Plugin Konfiguration
Öffnen Sie die Plugin Konfiguration über Shopwares Plugin Manager.
Dort finden Sie folgende Einstellungsmöglichkeiten

* KI Suche für diesen Shop verwenden?

Diese Einstellung bestimmt, ob die KI Suche in diesem Subshop aktiv ist oder nicht. Da aiPhilos momentan nur Deutsch unterstützt, sollten Sie diese Option für alle fremdsprachigen Subshops explizit deaktiveren.

* aiPhilos Benutzername

Der von aiPhilos bereitgestellte Benutzername.
Wird von allen Subshiops verwendet.

* aiPhilos Passwort

Das von aiPhilos bereitgestellte Passwort zu Ihrem Benutzernamen.
Wird von allen Subshiops verwendet.

* aiPhilos Datenbankname

Der Name der aiPhilos Datenbank die für diesen Subshop verwendet werden soll.
Dies muss ein eindeutiger Name bestehend aus Groß- und Kleinbuchstaben ohne Umlaute oder ß sein sowie Ziffern oder Unterstriche.
Keine Subshops dürfen sich untereinander die gleiche Datenbank teilen.
Es ist nicht notwendig, dass diese Datenbank bereits existiert, da Sie wenn nötig vom Plugin angelegt wird.

* Anzahl der Monate für Bestseller

Um Suchanfragen bezüglich der Beliebtheit von Produkten korrekt verstehen zu können, verwendet aiPhilos einen Messwert für die Beliebtheit eines Produktes. Dieses Plugin bitete diesen in Form der Anzahl der Verkäufe über einen Zeitraum in Monaten, den Sie hier angeben können.

* Freitextfeld Spalten

Hier können sie optional eine Liste an Semikolon getrennten Freitextfeld Datenbankspalten angeben, um diese an die aiPhilos Datenbank zu übertragen. Um Spalten hinzuzufügen müssen Sie diese genau so eingeben wie unter dem Feld Spaltenname in Shopwares Freitextfeld-Verwaltung für die Tabelle "s_articles_details" angegeben.
Falls Sie Beispielsweise ein Feld genannt "Kommentar" mit dem Spaltennamen "attr1" und ein weiteres Feld "Zusatzbeschreibung" unter dem Spaltennamen "additional_description" verwenden wollen, so müssen Sie hier "attr1;additional_description" eintragen.

Verwenden Sie nur Spalten die menschenlesbare Texte in der Sprache ihres Subshops als Inhalt haben. Es reicht aus, Shopwares integrierte Übersetzungsfunktion für Freitextfelder zu verwenden, falls Sie jedoch ein eigenes Feld pro Sprache verwenden, können Sie dieses pro Subshop explizit angeben.

* Ausgeschlossene Kategorie-IDs

Mit dieser Option können Sie die Artikel aus gewissen Kategorien vom Upload in die aiPhilos Datenbank anhand einer semikolon-getrennten Liste von Kategorie-IDs ausschließen.

Dies ist nützlich wenn beispielsweise drittanbieter Plugins eigene Kategorien verwenden um ihre Funktionalität anzubieten und diese Kategorien keine sinnvollen Artikel enthalten.

Sie können die Kategorie-ID in Shopwares Kategorieverwaltungsoberfläche finden, indem sie auf die gewünschte Kategorie klicken und dort die Zahl neben "System-ID" hier eintragen.

Es ist nicht notwendig dass Sie die Kategorien aus fremden Subshops oder Blogkategorien hier manuell ausschließen, da dies automatisch geschieht.

Wenn sie eine Kategorie ausschließen, werden alle Kindkategorien dieser Kategorie ebenfalls ausgeschlossen.

* Fallback Modus

Mit dieser Option können Sie festlegen ob und unter welchen Umständen ein Fallback zur Standardsuche geschehen soll.

__Niemals (nicht empfohlen)__

Unter keinen Umständen wird die Standardsuche von Shopware verwendet.
Verwenden Sie dies nur zu Entwicklungszwecken und nicht in Produktivsystemen.

__Fehler und keine Ergebnisse (Standardeinstellung)__

Fällt auf die Standardsuche zurück falls aiPhilos keine Ergebnisse findet oder ein Fehler passiert ist. Dies ist die Standardeinstellung. 

__Nur bei Fehlern (minimale Empfehlung)__

Fällt nur auf die Standardsuche zurück, falls bei der Suche mit aiPhilos ein Fehler passiert ist. Dies ist die empfohlene Minimaleinstellung und besonders nützlich wenn aiPhilos ihren Artikelstamm so gut erlernt hat, dass man mit guter Gewissheit sagen kann, dass wenn aiPhilos nichts findet, dies die richtige Antwort ist.

__Only when no results returned__

Fällt nur auf die Standardsuche zurück, falls keine Ergebnisse geliefert wurden. Diese Option existiert überwiegend der Vollständigkeit halber.

* Lernmodus

Der Lernmodus ist besonders nützlich um die aiPhilos Suche in einen bereits laufenden Shop zu integrieren. Wenn er aktiv ist werden Suchanfragen nicht an aiPhilos gesendet und stattdessen immer die Standardsuche verwendet. Der Rest des Plugins bleibt für den betroffenen Subshop aktiv, so dass die Artikeldaten synchronisiert werden und aiPhilos Ihren Datenstamm erlernen kann. Sie können trotzdem einen Einblick in das Ergebniss bekommen, welches aiPhilos zurückliefern würde, indem sie manuell "&forceAi" an Ihre Suchanfragen anhängen.

Nehmen wir an Ihr Shop ist unter "www.myshop.local" gehostet und sie wollen nach "apple" Suchen, so würde das Aufrufen folgender URL die Suche mit aiPhilos erzwingen "www.myshop.local/search?sSearch=apple&forceAi"

### Cronjob Konfiguration

Sobald Sie ihr Plugin richtig konfiguriert haben und Ihr Artikelbestand mit halbwegs sinnvollen Daten - kein Blindtext in Beschreibungen - befüllt ist, können Sie den Cronjob "Update aiPhilos databases" aktivieren und starten.

Der Cronjob sollte mindestens einmal tägliche laufen, jedoch könnte es ratsam sein, den Cronjob öfters laufen zu lassen, falls sich Ihre Artikeldaten noch recht schnell ändern.

### Problembehebung

Das Plugin verwendet Shopwares Logging Funktionen für so gut wie alle kritischen Stellen im Programm. Falls Sie Probleme haben, ist es ratsam zuerst in die Logdatei zu schauen, welche sie unter "Einstellungen > Logfile > System-Log" finden. Dort wählen Sie die Datei welche mit "aiphilos_search" im Namen beginnt und das korrekte Datum hat.
Klicken Sie auch unbedingt auf die Lupe um die Details der Meldung unter "Context" sehen zu können.