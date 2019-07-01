# MVC Plugins

Die Extension rn_base stellt Klassen für die Entwicklung von TYPO3 Extensions bereit. Es werden dabei sehr viele Bereiche der Entwicklung abgedeckt, angefangen von der Plugin-Entwicklung nach MVC, über den Zugriff auf die Datenbank, die Verarbeitung von Typoscript, bis hin zur Entwicklung von BE-Modulen.

## Die Grundlagen

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
     * @param Tx_Rnbase_Configuration_ProcessorInterface $configurations
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

### fluid
Hinweis: Es muss mind. TYPO3 7.6 installiert sein.

Es gibt den View Sys25\RnBase\Fluid\View\Action, um die Templates mit fluid rendern zu können. Per default werden die fluid Dateien in den Ordner Templates, Layouts und Partials in 'EXT:' . $extensionKey . '/Resources/Private/' erwartet. Das kann auch überschrieben bzw. weitere hinzugefügt werden, ganz nach der Konvention von ExtBase/Fluid:

```
plugin.tx_myextension {
    view {
        templateRootPaths.0 = EXT:plugin_tx_mkextension/Resources/Private/MyOtherTemplates/
        partialRootPaths.0 = EXT:plugin_tx_mkextension/Resources/Private/MyOtherPartials/
        layoutRootPaths.0 = EXT:plugin_tx_mkextension/Resources/Private/MyOtherLayouts/
    }
    settings {
    }
}
```

Ist plugin.tx_myextension.templatePath konfiguriert, dann überschreibt das die Einstellungen in plugin.tx_myextension.view.templateRootPaths.0. Das direkte setzen eines Templates mit plugin.tx_myextension.myAction.template.file bzw. plugin.tx_myextension.myActionTemplate ist auch weiterhin möglich.

#### fluid standalone
Es gibt auch einen Standalone-View, um Templates unabhängig von einer Action zu rendern. Das kann so genutzt werden:

```php

/* @var $configurations Tx_Rnbase_Configuration_ProcessorInterface */
$view = \Sys25\RnBase\Fluid\View\Factory::getViewInstance($configurations);
$view->setPartialRootPaths($partialsPaths);
$view->setLayoutRootPaths($layoutPaths);
$view->setTemplatePathAndFilename($absolutePathToTemplate);
$view->assignMultiple(array('someData' => 'test'));
return $view->render();

```

#### translate ViewHelper
Damit wird ein label über die rnbase configuration übersetzt.
In folgenden Beispielen wird das Label `label.description` übersetzt:

```html
{namespace rn=Sys25\RnBase\Fluid\ViewHelper}

<!-- translate inline from variable -->
{varWithLabelDescriptionKey -> rn:translate()}

<!-- translate inlnie with connation -->
{f:format.raw(value: 'label.description') -> rn:translate()}

<!-- translate inlnie with key -->
{rn:translate(key: 'label_filter_reset')}

<!-- translate as fluid tag with key-->
<rn:translate key="label.description" />

<!-- translate as fluid tag with child content-->
<rn:translate>label.description</rn:translate>
```


#### configurations ViewHelper
Damit kann auf die get Methode der configurations zugegriffen werden:

```html

{namespace rn=Sys25\RnBase\Fluid\ViewHelper}

{rn:configurations.get(confId: confId, typoscriptPath: 'my.path')}

```

#### pagebrowser ViewHelper
Damit kann auf die get Methode der configurations zugegriffen werden:

```html

{namespace rn=Sys25\RnBase\Fluid\ViewHelper}

<div class="pagebrowser">
    <rn:pageBrowser
        maxPages="5"
        hideIfSinglePage="1"
    >
        <rn:pageBrowser.firstPage
            addQueryString="TRUE"
            argumentsToBeExcludedFromQueryString="{0: 'id', 1: 'L', 2: 'cHash'}"
            section="comments"
        >
            &lt;&lt;
        </rn:pageBrowser.firstPage>
        <rn:pageBrowser.prevPage
            addQueryString="TRUE"
            argumentsToBeExcludedFromQueryString="{0: 'id', 1: 'L', 2: 'cHash'}"
            section="comments"
        >
            &lt;
        </rn:pageBrowser.prevPage>
        <rn:pageBrowser.currentPage
            addQueryString="TRUE"
            argumentsToBeExcludedFromQueryString="{0: 'id', 1: 'L', 2: 'cHash'}"
            class="active"
            usePageNumberAsLinkText="1"
            section="comments"
        />
        <rn:pageBrowser.normalPage
            addQueryString="TRUE"
            argumentsToBeExcludedFromQueryString="{0: 'id', 1: 'L', 2: 'cHash'}"
            usePageNumberAsLinkText="1"
            section="comments"
        />
        <rn:pageBrowser.nextPage
            addQueryString="TRUE"
            argumentsToBeExcludedFromQueryString="{0: 'id', 1: 'L', 2: 'cHash'}"
            section="comments"
        >
            &gt;
        </rn:pageBrowser.nextPage>
        <rn:pageBrowser.lastPage
            addQueryString="TRUE"
            argumentsToBeExcludedFromQueryString="{0: 'id', 1: 'L', 2: 'cHash'}"
            section="comments"
        >
            &gt;&gt;
        </rn:pageBrowser.lastPage>
    </rn:pageBrowser>
</div>
```
Hinweis: Nicht alle fluid ViewHelper können ohne weiteres verwendet werden. Wenn z.B. ein Extbase Controller nötig ist,
wie bei Formularen, dann funktioniert das nicht out-of-the-box.

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

Um die Models abzufragen oder Änderungen in die Datenbank zu übernehmen, wird in der Regel ein Repository verwendet.
Mehr dazu unter [Repositories](repositories.md)

## Typo3-Cache

Die Plugins sollten im TypoScript immer über ein gecachtes USER-Object, kein ungecachtes USER_INT-Object eingebunden werden!

Um nun eine bestimmte Action doch über ein USER_INT einzubinden, muss bzw sollte nie das ganze Plugin auf USER_INT umgestellt werden. Stattdessen sollte die Action in ein USER_INT konvertiert werden! Dies kann direkt über PHP in der Action als auch im Typoscript/Flexform getan werden: Beispiel für die Konvertierung im TypoScript:

```
 # global für alle actions convertieren
plugin_tx_mkextension.toUserInt = 1
 # nur für die eine bestimmte action convertieren
plugin_tx_mkextension.showdata.toUserInt = 1
```

Convertierung direkt in der Action:
```php
$this->getConfigurations()->convertToUserInt();
```

Bei einer Konvertierung von USER auf USER_INT ruft TYPO3 das Plugin mehrfach auf. Der Output des Aufrufs, bei dem die Konvertierung durchgeführt wird, wird komplett ignoriert. Stattdessen wird ein neuer Aufruf über USER_INT erzeugt. Die in rn_base integrierte Konvertierung kümmert sich bereits darum, daß die Plugins nicht unnötig doppelt aufgerufen werden. Dies geschieht dadurch, daß beim Setzen der Konvertierung eine Skip Exception geworfen und somit das Rendering ignoriert wird.

## Exceptionhandling

In rn_base gibt es ein spezielles Exception Handling für den Frontendcontroller. Der Exceptionhandler fängt alle innerhalb des Plugins erzeugten Exceptions ab und erzeugt je nach Konfiguration eine entsprechende Ausgabe.

### Extensionkonfiguration

Im Extensionmanager gibt es dazu folgende Optionen:

**verboseMayday**

Ist diese Option aktiviert, werden detaillierte Informationen zum Fehler direkt im Frontend ausgegeben. Dies sollte in Produktivumgebungen immer deaktiviert sein!

**exceptionHandler**

Hier kann ein Spezieller Exceptionhandler definiert werden, welcher Sich um die Exceptions kümmern soll. Default ist tx_rnbase_exception_Handler.

**send503HeaderOnException**

Legt Fest, ob im Fehlerfall ein 503 Header gesetzt werden soll. Dies ist vor allen für die Suchmaschinen notwendig, damit diese Fehlerhafte Seiten nicht indizieren. Diese Option sollte immer aktiv sein.

**sendEmailOnException**

Legt fest, ob und an welche Email-Adresse im Falle eines Fehlers versendet werden soll.

### spezielle Fehlermeldungen

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

### Abfangen von Fehlern

Alternativ zur speziellen Fehlermeldung, gibt es auch die Möglichkeit einen Fehler über TypoScript komplett abzufangen. Dies hat den Vorteil, daß dann kein 503 Header gesetzt, kein DevLog Eintrag geschrieben und auch keine Fehler-E-Mail versendet wird.

Diese Möglichkeit bietet sich aktuell über TypoScript oder einen eigenen Exception Handler.

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

## Einbinden von JS und CSS-Dateien

Man kann auf Ebene einer Action Resourcedateien einbinden. Das kann man direkt per Typoscript konfigurieren:

```
plugin.tx_myext {
    myview {
        includeJSFooter {
            1 = EXT:myext/Resources/Public/Scripts/validator.js
            1 {
                excludeFromConcatenation = 1
                dontCompress = 1
            }
        }
        includeJSFooterlibs {
            1 = EXT:myext/Resources/Public/Scripts/validator.js
        }
        includeCSS {
            1 = EXT:myext/Resources/Public/Styles/validator.css
        }
        includeJSlibs {
            1 = https://my-external-library
            1.external = 1
        }
    }
}
```

Externe Ressourcen sollte immer mit vollem Protokoll eingebunden werden. Eine Einbindung mit //my-external-library kann u.U. zu Problemen führen.
## Cachehandling

Bei USER Plugins muss der Cache in den pages geleert werden, wenn sich ein Datensatz ändert. Das geht mit rn_base ganz einfach. Zunächst müssen im TypoScript die Cache Tags für die Action definiert werden.

```
plugin.tx_myext {
    myview {
        cacheTags {
            0 = first-tag
            1 = second-tag
        }
    }
}
```

Dieser View wird klassicherweise einen Datensatz aus einer Tabelle anzeigen. Damit der Cache bei Änderungen in dieser Tabelle automatisch geleert wird, müssen die Tags nach TYPO3 Konvention verwendet werden oder in der TCA für die Tabelle die Cache Tags definiert werden. Die TYPO3 Konvention lautet dass die Tabellennamen verwendet werden. Wenn ein Datensatz im BE gespeichert wird, löscht TYPO3 automatisch den Cache für die Tags mit dem Tabellennamen und Tabellenname + UID des Datensatz, also z.B. pages und pages_123. Durch rn_base können in der TCA aber weitere Cache Tags gesetzt werden.

```php
$TCA['tx_myext_data_for_my_view']['ctrl']['cacheTags'] = array('first-tag', 'second-tag');
```

rn_base leert dann in Cache Gruppe "pages" alle Einträge mit dem konfigurierten Tag. Es ist also zwingend notwendig, dass alle gewünschten Caches der Gruppe **"pages"** angehören, die geleert werden sollen. Das ist bei der default Konfiguration von TYPO3 schon der Fall. Wenn aber z.B. auch der tt_news geleert werden soll, dann müssten die Caching Konfiguration angepasst werden:

```php
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tt_news_cache'] = array(
    'backend' => 'MyCachingBackend',
    'frontend' => 'MyCachingFrontend',
    'options' => array(
        ...
    ),
    'groups' => array('pages')
);
```

Hinweis zu sonstigen Extensions, die nicht auf rn_base basieren: Die Cache Tags für die Plugins sollten über TypoScript mit einer UserFunc hinzugefügt werden. Damit muss man nicht verschiedene Hooks nutzen und hat es global für jeden View. Hier ein Beispiel für tt_news:
```ts
plugin.tt_news.stdWrap.postUserFunc = Tx_Rnbase_Utility_Cache->addCacheTagsToPage
plugin.tt_news.stdWrap.postUserFunc {
    0 = tt_news
    1 = tt_news_category
}
```

### Extbase Controller
Das ganze gibt es auch für Extbase Controller. Dazu muss im Controller der Trait \Sys25\RnBase\Controller\Extbase\CacheTagsTrait hinzugefügt werden. Damit können per TypoScript die Cache Tags für Actions konfiguriert werden nach dem Schema plugin.ty_my_ext.settings.cacheTags.$lowerCamelCaseControllerNameOmittingController.$lowerCaseActionNameOmittingAction.0 = my_cache_tag. Für tx_news könnte ein Beispiel so aussehen:

```
plugin.tx_news {
    settings {
        cacheTags {
            news {
                detail {
                    0 = first-tag
                    1 = second-tag
                }
            }
        }
    }
}
```
