# Tips & Tricks
## Wie findet man das verwendete HTML-Template?

Wenn man in ein fremdes Projekt kommt und mal eben eine Änderung am Layout machen soll, steht man häufig vor der Frage, welches HTML-Template eigentlich betroffen ist. Bei Plugins, die auf Basis von rn_base entwickelt wurden, kann man dieses Template über ein festes "Such-Schema" aufspüren:

1. Man öffnet das Plugin auf der betroffenen Seite und schaut nach, welcher View angezeigt wurde und ob im Tab des Views ein HTML-Template gesetzt ist
2. Ebenfalls im Plugin schaut man im Tab TS-Setup nach, ob da ein Plugin gesetzt ist
3. Hat man noch kein Ergebnis muss man im BE-Modul *Template* den *Typoscript-Object Browser* verwenden. Man klickt auf die betroffene Seite und sucht im Pfad plugin.tx_dasplugin. nach dem gesetzten Template. Dieses setzt sich immer aus der ID des View gefolgt von Template zusammen:
```
plugin.tx_dasplugin.viewidTemplate = fileadmin/vorlage.html
```
Welche ID ein View verwendet, kann man notfalls im Code nachschauen. Die Action-Klasse setzt die ID in der Methode **getTemplateName()**.

## Einbindung von DAM-Medien
### TypoScript

```
damimages = USER
damimages  {
  userFunc = tx_rnbase_util_TSDAM->printImages
  refField = [Feldname]
  refTable = [Tabellenname]
  forceIdField = [Feldname]
  template = [Pfad]
  media {
    file = IMAGE
    file.file.import.field = file
    file.file.maxH = [Wert]
    file.file.maxW = [Wert]
  }
}
```

### Bedeutung der Typoscript-Attribute
```
refField
    Referenzfeld für DAM - Name des Datensatz-Feldes, in dem die Bilder abgelegt werden
refTable
    Referenzfeld für DAM - Name der Tabelle, in dem die Datensätze abgelegt sind
forceIdField (optional)
    Referenzfeld für DAM - wenn dieses Attribut gesetzt ist, wird das darin abgelegte Feld als Referenz für die UID des zugehörigen Datensatzes verwendet. Nützlich ist dies bei der Lokalisierung von Datensätzen, wenn Bilder aus dem zugrunde liegenden Default-Datensatz verwendet werden sollen. Werden die Felder refField und refTable angepasst, können sogar Bilder aus anderen Tabellen verwendet werden.
template
    Pfad des Templates, das die Formatierung der DAM-Medien übernimmt
media.file.fil.max[H|W]
    Maximale Höhe / Breite des angezeigten Mediums
```

## Einzelansichten von Datensätzen
### ExceptionHandling
Wenn man eine Action zur Einzelansicht eines Datensatzes hat und das anzuzeigende Element nicht gefunden wird (Parameter falsch, fehlt ganz oder Element gelöscht), dann sollte das 404 Handling von TYPO3 gestartet werden. Dafür muss in der Action einfach nur die Exception `Tx_Rnbase_Exception_PageNotFound404` geworfen werden.
```php
if (!intval($itemId)) {
	throw new Tx_Rnbase_Exception_PageNotFound404('My error message');
}
```
Alternativ wird auch `TYPO3\CMS\Core\Error\Http\PageNotFoundException` behandelt, aber dafür gibt es keine Cross-Version Support in TYPO3.

## Kommaseparierte Liste mit dem ListBuilder

Wenn man einen Listbuilder hat, dann will man manchmal alle Elemente der Liste mit einem Komma trennen aber nach dem letzten soll kein Komma erscheinen. Dafür brauch man nur etwas TypoScript.

Template:
```
###PARENT_CHILDS###
	###PARENT_CHILD###
		###PARENT_CHILD_TITLE###
	###PARENT_CHILD###
###PARENT_CHILDS###
```
Damit man nun so etwas wie "child 1, child 2, child 3" erhält, benötigt man das folgende TS:

```
tx_myext.myAction.parent.child{
	### nach dieser Anzahl fängt rn_base von vorne an, also setzt den initial Wert auf 0.
	### würde der Wert also auf 3 stehen, dann könnte man in dem CASE Statement die
	### Bedingungen erweitern um 1 und 2, womit alle 3 Elemente von vorn angefangen werden würde
	### der Wert muss in unserem Fall also höher sein als die zu erwartende Anzahl der childs
	### damit die Bedingung für 0 in unserem CASE nur am Anfang verwendet wird und dann default
	roll.value = 1000
	title {
		stdWrap.cObject = CASE
		stdWrap.cObject {
			0 = TEXT
			0.field = title
			0.wrap = |
			default = TEXT
			default.noTrimWrap = |, ||
			default.field = title
			key.field = roll
		}
	}
}
```

Der ListBuilder würde dann theoretisch so aufgerufen:
```php
$childs = array(
	0 => tx_rnbase::makeInstance('tx_rnbase_model_data', array('title' => 'child 1')),
	1 => tx_rnbase::makeInstance('tx_rnbase_model_data', array('title' => 'child 2')),
	2 => tx_rnbase::makeInstance('tx_rnbase_model_data', array('title' => 'child 3')),
);
$listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
$listBuilder->render(
	$childs,
	NULL, $template, 'tx_rnbase_util_SimpleMarker',
	'parent.child.', 'PARENT_CHILD $formatter
);
```

## Debugging
Um den Debug-Modus zu aktivieren muss zunächst in der Extensionkonfiguration ein beliebiger **debugKey** gesetzt werden. Dieser debugKey muss zum Aktivieren des Debug-Modus in den Parameter debug geschrieben werden:

http://www.mydomain.de/index.php?id=home&debug=cRuyUDe4Ra4r

Im Programmcode kann über den Methodenaufruf tx_rnbase_util_Debug::isDebugEnabled() geprüft werden, ob wir uns gerade im Debugmodus befinden, und ggf. eigene Debugausgaben über tx_rnbase_util_Debug::debug() starten.
```php
if (rnbase_util_Debug::isDebugEnabled()) {
	tx_rnbase_util_Debug::debug('Debug is enabled!', 'D: '.__FILE__.'@'.__LINE__);
}
```

## Label-Debug

Dieser Debugmodus kann zu jedem Label, welches über Tx_Rnbase_Configuration_Processor::getLL() aufgelöst wird, zusätzlich zu dem enthaltenem Wert das ursprüngliche Label ausgeben. Dies kann nützlich für Redakteure in Verbindung mit der Mklltranslate Extension sein, da man so einfach an das Label kommt, welches geändert werden soll.

Aktiviert wird dieser Debug über Parameter labeldebug aktiviert, welcher zusätzlich zum debugkey gesetzt werden muss. Mögliche werte sind plain (default) oder html.

http://www.mydomain.de/index.php?id=home&debug=cRuyUDe4Ra4r&labeldebug=html

Siehe auch (https://github.com/digedag/rn_base/pull/1)

## Der PLUGIN_Marker

Bei rn_base-Plugins wird zusätzlich zu den normalen Markern des Views immer noch der tt_content Datensatz des Plugins als Marker bereitgestellt. Der Marker ###PLUGIN_UID## liefert also die UID aus tt_content für das Plugin. Per Typoscript kann man die Daten über folgenden Pfad bearbeiten:

```
plugin.tx_myext {
  myview {
    plugin.uid.wrap = <b>|</b>
    plugin.dcdemo = TEXT
    plugin.dcdemo.value = Demo
  }
}
```
Natürlich funktionieren hier auch die DC-Marker. Die Spalte dcdemo wird per ###PLUGIN_DCDEMO### herausgerendert. 
Mit diesem Feature hat man die Möglichkeit innerhalb eines Plugins bspw. auch die Daten eines komplett anderen Plugins zu rendern.

