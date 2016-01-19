# MVC Plugins

Die Extension rn_base stellt Klassen für die Entwicklung von TYPO3 Extensions bereit. Es werden dabei sehr viele Bereiche der Entwicklung abgedeckt, angefangen von der Plugin-Entwicklung nach MVC, über den Zugriff auf die Datenbank, die Verarbeitung von Typoscript, bis hin zur Entwicklung von BE-Modulen.

##Die Grundlagen

In rn_base wird für die Pluginentwicklung nicht der herkömmliche Plugin-Ansatz auf Basis der
Klasse **tslib_pibase** bzw. **\TYPO3\CMS\Frontend\Plugin\AbstractPlugin** verwendet. Stattdessen werden die Plugins nach dem Pattern Model-View-Controller umgesetzt. Die meisten Plugins in TYPO3 verfügen über verschiedene Darstellungen. Als Beispiel sei die bekannte Extension tt_news genannt. Diese liefert u.a. eine Newsliste, eine News-Detailansicht, ein Archiv, eine Suche und vieles andere mehr. Die Plugins auf Basis von rn_base haben bereits eine eingebaute Unterstützung für diese Darstellungen. Man muss im Flexform des Plugins lediglich den Namen der Klasse angeben und diese dann natürlich noch anlegen. Den Rest übernimmt der Basis-Controller in rn_base. Hier ein Auschnitt aus dem Flexform der Extension **t3sponsors**:

```xml
<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3DataStructure>
  <sheets>
    <sDEF>
      <ROOT>
        <TCEforms>
          <sheetTitle>LLL:EXT:t3sponsors/Resources/Private/Language/locallang_db.xml:plugin.t3sponsors.flexform.tab.common</sheetTitle>
        </TCEforms>
        <type>array</type>
        <el>
          <action>
            <TCEforms>
              <exclude>1</exclude>
              <label>LLL:EXT:t3sponsors/Resources/Private/Language/locallang_db.xml:plugin.t3sponsors.flexform.action</label>
              <config>
                <type>select</type>
                <items type="array">
                  <numIndex index="10" type="array">
                    <numIndex index="0">LLL:EXT:t3sponsors/Resources/Private/Language/locallang_db.xml:plugin.t3sponsors.flexform.action.SponsorList</numIndex>
                    <numIndex index="1">tx_t3sponsors_actions_SponsorList</numIndex>
                  </numIndex>
                  <numIndex index="20" type="array">
                    <numIndex index="0">LLL:EXT:t3sponsors/Resources/Private/Language/locallang_db.xml:plugin.t3sponsors.flexform.action.SponsorShow</numIndex>
                    <numIndex index="1">tx_t3sponsors_actions_SponsorShow</numIndex>
                  </numIndex>
                </items>
                <multiple>0</multiple>
                <maxitems>10</maxitems>
                <size>10</size>
              </config>
            </TCEforms>
          </action>
        </el>
      </ROOT>
    </sDEF>
 </sheets>
</T3DataStructure>
```

Hier sind zwei Klassen für die Darstellung konfiguriert: **tx_t3sponsors_actions_SponsorList** und **tx_t3sponsors_actions_SponsorShow**. Anhand des Namens kann der Basiscontroller die Klassen automatisch laden und den Request übergeben. Natürlich können beliebig viele Darstellungen gleichzeitig ausgewählt werden. Die Abarbeitung erfolgt dann in der gewählten Reihenfolge.

Vorher muss allerdings das Plugin bzw. der Basiscontroller bei TYPO3 angemeldet werden. Dies geschieht zum einen in der ext_tables.php

```php
$TCA['tt_content']['types']['list']['subtypes_excludelist']['tx_t3sponsors']='layout,select_key,pages';
// Show tt_content-field pi_flexform
$TCA['tt_content']['types']['list']['subtypes_addlist']['tx_t3sponsors']='pi_flexform';
// Add flexform and plugin
tx_rnbase_util_Extensions::addPiFlexFormValue('tx_t3sponsors','FILE:EXT:'.$_EXTKEY.'/flexform_main.xml');
tx_rnbase_util_Extensions::addPlugin(Array('LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang_db.php:plugin.t3sponsors.label','tx_t3sponsors'));
tx_rnbase_util_Extensions::addStaticFile($_EXTKEY,'Configuration/Typoscript/Base/', 'T3 Sponsors');
```
In der letzten Zeile wird das Static-Template der Extension angemeldet. In dieser Datei setup.txt müssen wir weitere Angaben zum Plugin machen:

```
includeLibs.tx_rnbase_controller = EXT:rn_base/class.tx_rnbase_controller.php
plugin.tx_t3sponsors                = USER
plugin.tx_t3sponsors.flexform       = flexform_main.xml
plugin.tx_t3sponsors.userFunc       = tx_rnbase_controller->main
plugin.tx_t3sponsors.defaultAction  = tx_t3sponsors_actions_SponsorList
plugin.tx_t3sponsors.qualifier      = t3sponsors
plugin.tx_t3sponsors.templatePath   = EXT:t3sponsors/templates
plugin.tx_t3sponsors.locallangFilename = EXT:t3sponsors/resources/Private/Language/locallang.xml
tt_content.list.20.tx_t3sponsors    =< plugin.tx_t3sponsors
```
Danach sollte das Plugin korrekt integriert sein und sich als Content-Element auswählen lassen.
Nun benötigen wir natürlich noch den Action-Controller, der den eigentlichen Output des Plugins liefert. In rn_base werden verschiedene Basisklassen bereitgestellt. Am häufigsten wird die Actionklasse von tx_rnbase_action_BaseIOC erben und dann mininal folgendes Aussehen haben:
```php
class tx_t3sponsors_actions_SponsorList extends tx_rnbase_action_BaseIOC {
	/**
	 *
	 *
	 * @param array_object $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param array_object $viewData
	 * @return string error msg or null
	 */
	protected function handleRequest(&$parameters,&$configurations, &$viewdata){
		$srv = tx_t3sponsors_util_ServiceRegistry::getSponsorService();
		$filter = tx_rnbase_filter_BaseFilter::createFilter($parameters, $configurations, $viewdata, $this->getConfId(). 'sponsor.filter.');
		$fields = array();
		$options = array();
		$filter->init($fields, $options);
		$service = tx_t3sponsors_util_ServiceRegistry::getSponsorService();
		$cfg = array();
		$cfg['colname'] = 'name1';
		$cfg['searchcallback'] = array($service, 'search');
		tx_rnbase_filter_BaseFilter::handleCharBrowser($configurations, $this->getConfId().'sponsor.charbrowser', $viewdata, $fields, $options, $cfg);
		tx_rnbase_filter_BaseFilter::handlePageBrowser($configurations, $this->getConfId().'sponsor.pagebrowser', $viewdata, $fields, $options, $cfg);
		$sponsors = $srv->search($fields, $options);
		$viewdata->offsetSet('sponsors', $sponsors);
		return null;
	}

	function getTemplateName() { return 'sponsorlist';}
	function getViewClassName() { return 'tx_t3sponsors_views_SponsorList';}
}
```
Theoretisch kann die Methode **handleRequest()** direkt einen String zurückliefern. Dieser würde dann ohne weitere Verarbeitung im Frontend angezeigt. Im Beispiel oben wird statt dessen **NULL** zurückgegeben. Dadurch wird die Ausgabe über eine View-Klasse geleitet und man kann HTML-Template verwenden. Alle Daten, die für die Ausgabe benötigt werden, sollten in das Objekt $viewdata geschrieben werden.

## Die Views

Wenn der Controller nun mit seiner Arbeit fertig ist und alle notwendigen Daten gesammelt bzw. verarbeitet wurden, dann ist die View-Klasse am Zug. Vom Controller wird der View über dessen Methode *render($view, &$configurations)* aufgerufen. Wie der Controller hat auch der View einige Dinge zu tun, die sich immer wieder gleichen: Das Laden des HTML-Templates und die Suche des gewünschten Subparts. Diese Arbeiten erledigt die Klasse tx_rnbase_view_Base. Wenn wir also unseren View davon erben lassen, dann hat die Klasse ungefähr dieses Aussehen:
```php
class tx_t3sponsors_views_SponsorList extends tx_rnbase_view_Base {
	function createOutput($template, &$viewData, &$configurations, &$formatter) {
		// Wir holen die Daten von der Action ab
		$sponsors =& $viewData->offsetGet('sponsors');
		$listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
		$template = $listBuilder->render($sponsors,
				$viewData, $template, 'tx_t3sponsors_marker_Sponsor',
				'sponsorlist.sponsor.', 'SPONSOR', $formatter);
		return $template;
	}
	/**
	 * Subpart der im HTML-Template geladen werden soll. Dieser wird der Methode
	 * createOutput automatisch als $template übergeben.
	 *
	 * @return string
	 */
	function getMainSubpart() {
		return '###SPONSORLIST###';
	}
}
```
Durch die Implementierung der Methode getMainSubpart() bekommt der View direkt den passenden Abschnitt aus dem HTML-Template übergeben. Doch wie findet der View eigentlich die Datei? Diese wird per Konvention ermittelt. Die Actionklasse definiert in der Methode **getTemplateName()** den Typoscript-Key für das HTML-Template. Wenn die Action-Klasse also **sponsorlist** liefert, dann wird das Template über den TS-Key **plugin.t3sponsors.sponsorlistTemplate** gesucht.

Im Beispiel für das Template über den ListBuilder von rn_base gerendert. Nähere Informationen findet man beim [Rendern der Daten](rendering_data.md).


## Das Model

Bei Zugriffen auf die Datenbank liefert PHP die Daten in Form eines Arrays zurück. Nun ist es aber meist so, daß jede Tabelle in der Datenbank einem bestimmten Datentyp entspricht. Für diese Datentypen werden üblicherweise auch entsprechende Klassen angelegt. Nun wäre es ja ganz sinnvoll, wenn wir bei Abfragen an die Datenbank keine einfachen Arrays, sondern direkt Instanzen dieser Klassen geliefert bekommen.

Die Methode **doSelect()** der Klasse **rn_base_util_DB** unterstützt diese Abfragen mit einer einfachen Konvention. Die Klasse für den Datentyp benötigt ein Attribut für das Array mit den Daten und einen passenden Konstruktor:
```php
class tx_extkey_data {
 var $record;
 tx_extkey_data($row) {
   $this->record = $row;
 }
}
```
Wenn man nun diese Klasse bei der Datenbankabfrage als Option mit angibt, dann wird automatisch für jeden gefunden Datensatz eine Instanz dieser Klasse erzeugt und das Datenarray dem Konstruktor übergeben.
```php
 $options['where'] = 'email like \'%gmail%\;
 $options['orderby'] = 'nname asc, vname asc';
 $options['wrapperclass'] = 'tx_extkey_data';
 $persons = tx_rnbase_util_DB::doSelect('*', 'tx_extkey_person', $options)
```
In $persons erhalten wir dann ein Array von Objekten. Mit der Klasse Tx_Rnbase_Domain_Model_Base stellt rn_base ein Basisklasse mit einigen zusätzlichen Features bereit. Die Kindklassen müssen hier nur noch ein abstrakte Methode implementieren:
```php
class tx_extkey_models_export extends Tx_Rnbase_Domain_Model_Base {
 function getTableName(){return 'tx_extkey_person';}
}
```

##Typo3-Cache

Die Plugions sollten im TypoScript immer über ein gecachtes USER-Object, kein ungecachtes USER_INT-Object eingebunden werden!

Um nun eine bestimmte Action doch über ein USER_INT einzubinden, muss bzw sollte nie das ganze Plugin auf USER_INT umgestellt werden. Stattdessen sollte die Action in ein USER_INT konvertiert werden! Dies kann direkt über PHP in der Action als auch im Typoscript/Flexform getan werden: Beispiel für die Konvertierung im TypoScript:
```
### global für alle actions convertieren
plugin_tx_mkextension.toUserInt = 1
### nur für die eine bestimmte action convertieren
plugin_tx_mkextension.showdata.toUserInt = 1
```

Convertierung direkt in der Action:
```php
$this->getConfigurations()->convertToUserInt();
```
Bei der Konvertierung in der Action ist darauf zu achten, das dieser Vorgang so zeitig wie möglich durchgeführt wird!

Bei einer Konvertierung von USER auf USER_INT ruft TYPO3 das Plugin mehrfach auf. Der Output des Aufrufs, bei dem die Konvertierung durchgeführt wird, wird komplett ignoriert. Stattdessen wird ein neuer Aufruf über USER_INT erzeugt. Die in rn_base integrierte Konvertierung kümmert sich bereits darum, daß die Plugins nicht unnötig doppelt aufgerufen werden. Dies geschieht dadurch, daß beim Setzen der Konvertierung eine Skip Exception geworfen und somit das Rendering ignoriert wird.

##Exceptionhandling

In rn_base gibt es ein spezielles Exception Handling für den Frontendcontroller. Der Exceptionhandler fängt alle innerhalb des Plugins erzeugten Exceptions ab und erzeugt je nach Konfiguration eine entsprechende Ausgabe.

###Extensionkonfiguration

Im Extensionmanager gibt es dazu Folgende Optionen:

**verboseMayday**

Ist diese Option aktiviert, werden detaillierte Informationen zum Fehler direkt im Frontend ausgegeben. Dies sollte in Produktivumgebungen immer deaktiviert sein!

**exceptionHandler**

Hier kann ein Spezieller Exceptionhandler definiert werden, welcher Sich um die Exceptions kümmern soll. Default ist tx_rnbase_exception_Handler.

**send503HeaderOnException**

Legt Fest, ob im Fehlerfall ein 503 Header gesetzt werden soll. Dies ist vor allen für die Suchmaschinen notwendig, damit diese Fehlerhafte Seiten nicht indizieren. Diese Option sollte immer aktiv sein.

**sendEmailOnException**

Legt fest, ob und an welche Email-Adresse im Falle eines Fehlers versendet werden soll.


###spezielle Fehlermeldungen

Im Falle, das verboseMayday nicht gesetzt ist, wird versucht eine Nutzer freundliche Meldung auszugeben. Dazu wird der Fehlercode der Exception genutzt, um definierte Meldungen zu ermitteln. Zunächst wird im TypoScript nachgesehen. Wurde dort nichts zum Fehler gefunden, wird die Übersetzungsdatei geprüft. Ist auch hier nichts definiert, wird eine Defaultmeldung von rn_base ausgegeben.

Beispielkonfiguration TypoScript:
```
plugin.tx_extension.error {
	### alle fehler versteckt ausgeben
	default = TEXT
	default.current = 1
	default.wrap = <!-- | -->
	180150 = Oops, diesen Datensatz gibt es leider nicht mehr.
	180160 = COA
	180160 {
		10 = TEXT
		10.value = Dieser Eintrag ist leider nur für angemeldete Personen sichtbar. Bitte führen Sie eine erneute Suche durch.
		10.wrap = <p>|</p>
		### Suchplugin im Fehlerfall ausgeben
		20 = < plugin.tx_mksearch
	}
}
```

Beispiel Sprachdatei

```xml
<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3locallang>
	<data type="array">
		<languageKey index="default" type="array">
			<label index="ERROR_default">Es ist ein unerwarteter Fehler aufgetreten!</label>
			<label index="ERROR_180150">Oops, diesen Datensatz gibt es leider nicht mehr.</label>
			<label index="ERROR_180160">Dieser Eintrag ist leider nur für angemeldete Personen sichtbar.</label>
		</languageKey>
	</data>
</T3locallang>
```

###Abfangen von Fehlern

Alternativ zur speziellen Fehlermeldung, gibt es auch die Möglichkeit einen Fehler über TypoScript komplett abzufangen. Dies hat den Vorteil, das dann kein 503 Header gesetzt, kein DevLog Eintrag geschrieben und auch keine Fehler-E-Mail versendet wird.

Diese Möglichkeit bietet sich aktuell nur über TypoScript oder einen eigenen Exception Handler.

Beispiel für das Fangen von Exceptions über den Fehlercode im TypoScript:
```
plugin.tx_extension {
	catchException.180160 = COA
	catchException.180160 {
		10 = TEXT
		10.value = Dieser Eintrag existiert leider nicht mehr. Eventuell könnte Sie dies interesieren:
		10.wrap = <p>|</p>
		20 = < plugin.tx_extension
		20.action = tx_extension_action_AlternativeAusgabe
	}
}
```
