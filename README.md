TYPO3 commerce shopping system [![Build Status](https://travis-ci.org/CommerceTeam/commerce.svg?branch=master)](https://travis-ci.org/CommerceTeam/commerce)
=================

## Commerce für TYPO3 7.6
- Kompatibles Backend
- Unverändertes Frontend mit allen Hooks
- Noch kein Support für „Orders“, „Statistics“, „Manufacturer“ und „Supplier“

### Was ist Commerce
Das Team rund um Ingo Schmitt hat mit **Commerce** eine TYPO3 Erweiterung kreiert, die einen Web-Shop für TYPO3 zur Verfügung stellt.
Als kostenlose TYPO3 Erweiterung bietet es nicht den Funktionsumfang einer dedizierten und kostenpflichtigen Shop-Lösung wie z.B. OXID eSales.
Es schließt aber die Lücke zwischen Web-Shop und CMS. Für viele unserer Kunden steht auch nicht der Endkunden-Shop inkl. Check Out und Bezahlung im Vordergrund, sondern ein PIM-System.
Das erfassen aller Produktrelevanter Daten und das flexible darstellen auf der Homepage, das Integrieren in individuell gestaltete Content-Seiten 
Als solches erfüllt Commerce hervorragend seine Rolle und wird gerne eingesetzt.

### Viele Neuerungen mit TYPO3 7.6
Commerce setzt auf eine tiefe Integration in TYPO3.
Produkt- und Kategorie- Listen leiten sich von der TYPO3 List-Ansicht ab, der Produktbaum vom TYPO3 Page-Tree.
Zugriffsrechte für Redakteure/Backend-User werden wie im Page-Tree gehandhabt.
Alle Eingabemasken für Produkte, Artikel und Kategorien werden von TYPO3 mittels „TYPO3 Editing Forms“ dargestellt und verarbeitet.
Diese tiefe Integration bringt ein Einheitliches TYPO3 Look and Feel.
Das Problem: TYPO3 stellt die Meisten Komponenten nicht explizit als Library oder API zur Verfügung. So wurden weite Teile aus dem TYPO3 Source kopiert und umgeschrieben um damit die verschiedenen Commerce-Komponenten zu erstellen.
Mit der aktuellen TYPO3 Version 7.6 wurden aber genau in diesem Bereich viele Neuerungen eingeführt. Die vom alten TYPO3-Source kopierten und angepassten Komponenten funktionieren nicht mehr, oder nicht mehr richtig. Der Aufwand das neu herauszuziehen und umzuschreiben ist hoch. Und bei jedem Major-Release von TYPO3 kann sich das Spiel wiederholen.

### Designentscheid: Weg von TYPO3 Codeportierungen
Wir spielen schon länger mit dem Gedanken, einen Fork von Commerce zu erstellen und diesen an unsere Anforderungen anzupassen.
Da zZ. Commerce in der freien Version nach wie vor nicht für TYPO3 7.6 erhältlich ist, haben wir nun die Gelegenheit genutzt und uns an die Portierung für TYPO3 7.6 gemacht.
Dabei stellen wir gezielt den Designentscheid TYPO3-Sourcen zu nutzen zurück.
Die Liste der Produkte/Kategorien ist nun ein eigenes Template, das nur im Design an die TYPO3-Liste angelehnt ist, nichts aber mit dem entsprechenden Code zu tun hat.
Dasselbe gilt für den Produktbaum. Hier setzen wie die hervorragende JavaScript Library „FancyTree“ ein. Auch hier gilt, nur das Design ist an TYPO3 angelehnt.
So machen wir uns unabhängiger von TYPO3 Änderungen. Nur das „TYPO3 Editing Forms“ verwenden wir weiter. Dieses ist von TYPO3 auch für alle nicht-native TYPO3-Tabellen vorgesehen und kann gefahrlos verwendet werden.

### Frontend Rendering und Hooks bleiben bestehen
Wer eine komplexe Commerce-Installation betreibt, den wird es freuen zu hören, dass der Umstieg ohne Umstieg auf Fluid funktioniert.
In Fakt bleibt das Frontend-Rendering inklusive aller Hooks weitgehen unberührt.

### Ausblick
- Portierung der fehlenden BE-Module.
- Optionales Fluid-Frontend Rendering.
- Performanceoptimierung. Hier gibt es viele (und notwendige) Verbesserungsmöglichkeiten.

Wir setzen Commerce meistens im Ensemble mit weiteren eigenen Erweiterungen ein.

Dazu zählt etwa unser **Mediacenter**. So lassen sich Datenblätter, Broschuren und Downloads zu Produkten handhaben und für das Frontend ein übersichtliches Downloadportal gestalten.

Oder unsere **Web2Print**-Lösung. So können Kunden aus der Produktdatenbank automatisch und auf Knopfdruck einen Produktkatalog herausgenerieren. Dieser ist ready für den Print, oder lässt sich noch nachbearbeiten.

Neu haben wir nun auch eine **SalesApp**. Eine auf cordova basierende Vertriebs-App für alle Plattformen, welche ihre Daten aus TYPO3 und Commerce bezieht.

Unser Augenmerk ist also primer auf ein PIM-System gerichtet, zum Erfassen von Medienneutralen Produktdaten.