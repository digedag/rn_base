#Modulentwicklung
##Entwicklung von BE-Modulen mit Unterstützung von rn_base

Mit Version 0.5.3 bietet rn_base auch Unterstützung für die Modulentwicklung an. Dabei werden eigentlich nur die Standard-Module von TYPO3 auf Basis der Klasse t3lib_scbase so bereitgestellt, das man sie einfach verwenden kann.

Grundlegendes

Damit man in der Modul-Leiste einen neuen Eintrag bekommt, muss das Modul zunächst bei TYPO3 angemeldet werden. Dies geschieht in der ext_table.php:
```php
if (TYPO3_MODE == 'BE') {
 // Einbindung des eigentlichen BE-Moduls. Dieses bietet eine Hülle für die eigentlichen Modulfunktionen
 tx_rnbase_util_Extensions::addModule('user', 'txmkmailerM1', "", tx_rnbase_util_Extensions::extPath($_EXTKEY) . 'mod1/');
```
Diese Zeilen kann man sich auch vom Kickstarter erzeugen lassen. Wichtig: jedes TYPO3-Modul benötigt ein eigenes Verzeichnis. Darin sucht TYPO3 dann automatisch nach einer conf.php und index.php für den Aufruf des Modul.

Da die Modul-Leiste bei zuvielen Modulen schnell auch unübersichtlich wird, sollte man mit eigenes Modulen sparsam umgehen. Statt viele kleine Module zu schreiben, sollte man lieber die Module so gestalten, dass sie verschiedene Aufgaben übernehmen können. Mit dem Modul Web-Funktionen zeigt TYPO3 wie man dass mit einem Funktions-Umschalter im Modul realisieren kann. Die verfügbaren Funktionen können dann wieder dynamisch erweitert werden. Mit der Klasse t3lib_scbase liefert TYPO3 sogar eine Basisklasse in der die wichtigsten Dinge für so ein dynamisches Modul sogar schon implementiert sind. Wie so oft, wird es aber sehr umständlich gemacht. Und zu allem Überfluß erzeugt der Kickstarter sogar ein Dummy-Modul, das diese Erweiterbarkeit zerstört...

tx_rnbase_mod_BaseModule

Wenn man davon erbt, hat man 2 Möglichkeiten das Backend Modul zu rendern. Entweder der alte Weg mit der
DocumentTemplate Klasse von TYPO3, welche auf einem Haupttemplate basiert. (Das Template wird über TS
konfiguriert) Oder man verwendet den Weg über die ModuleTemplate Klasse. Dabei muss man dann in seiner
Backend Modul Klasse die Methode useModuleTemplate überschreiben und dort TRUE liefern.


### Dispatcher

Der neue Weg, BE-Module über dispatcher zu registrieren.

Damit man in der Modul-Leiste einen neuen Eintrag bekommt,
muss das Modul zunächst bei TYPO3 über die ext_tables.php angemeldet werden.  
Im sechsten Paremeter `$moduleConfiguration` die Konfiguration übergeben,
die bisher in der conf.php stand.

```php
if (TYPO3_MODE == 'BE') {
    // register web_MkpostmanBackend
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'mkpostman',
        'web',
        'backend',
        'bottom',
        array(
        ),
        array(
            'access' => 'user,group',
            'routeTarget' => 'DMK\\Mkpostman\\Backend\\ModuleBackend',
            'icon' => 'EXT:mkpostman/ext_icon.gif',
            'labels' => 'LLL:EXT:mkpostman/Resources/Private/Language/Backend.xlf',
        )
    );
}
```

Die Klasse für das Module muss lediglich von `tx_rnbase_mod_BaseModule` erben
und den Extension-Key liefern:

```php
/**
 * MK Postman backend module
 *
 * @package TYPO3
 * @subpackage DMK\Mkpostman
 * @author Michael Wagner
 */
class ModuleBackend
    extends \tx_rnbase_mod_BaseModule
{
    /**
     * Method to get the extension key
     *
     * @return string Extension key
     */
    public function getExtensionKey()
    {
        return 'mkpostman';
    }
}
```

###Functions

Zu der Modulhülle gehören die eigentlichen Module, die Module Functions.

Diese werden ebenfalls über die ext_tables.php bei TYPO3 angemeldet. 
```php
if (TYPO3_MODE == 'BE') {
    // register subscriber be module
    tx_rnbase_util_Extensions::insertModuleFunction(
        'web_MkpostmanBackend',
        'DMK\\Mkpostman\\Backend\\Module\\SubscriberModule',
        null,
        'LLL:EXT:mkpostman/Resources/Private/Language/Backend.xlf:label_func_subscriber'
    );
}
```

Normalerweise stellt die Modulefunction wieder nur ein Container dar, 
welcher mehrere Handler beinhaltet.  
Hier ein Beispiel mit einem Handler.  
Die Methode `getSubMenuItems` liefert ein array mit allen enthaltenen Handlern, 
die wiederum meist als Tabgruppe im Backend ausgegeben werden:
```php
/**
 * MK Postman subscriber module
 *
 * @package TYPO3
 * @subpackage DMK\Mkpostman
 * @author Michael Wagner
 */
class SubscriberModule
    extends \tx_rnbase_mod_ExtendedModFunc
{
    /**
     * Method getFuncId
     *
     * @return    string
     */
    protected function getFuncId()
    {
        return 'mkpostman_subscriber';
    }
    /**
     * Returns all sub handlers
     *
     * @return array
     */
    protected function getSubMenuItems()
    {
        return array(
            \tx_rnbase::makeInstance(
                'DMK\\Mkpostman\\Backend\\Handler\\SubscriberHandler'
            ),
        );
    }
    /**
     * Liefert false, wenn es keine SubSelectors gibt.
     * sonst ein Array mit den ausgewählten Werten.
     *
     * @param string $selectorStr
     *
     * @return array or false if not needed. Return empty array if no item found
     */
    protected function makeSubSelectors(&$selectorStr)
    {
        return false;
    }
}
```


###Handler

Ein Handler ist ein Teil einer Modulefunction und
für die eigentliche Ausgabe verantwortlich.

In den meisten Fällen wird es sich bei der Ausgabe
um eine Auflistung von Datensätzen handeln.  
Unter anderem dafür stellt rn_base wieder einen Abstrakten Handler zur Verfügung
der verwendet werden kann:

```php
/**
 * Subscriber handler
 *
 * @package TYPO3
 * @subpackage DMK\Mkpostman
 * @author Michael Wagner
 */
class SubscriberHandler
    extends \Tx_Rnbase_Backend_Handler_SearchHandler
{
    /**
     * Returns a unique ID for this handler.
     * This is used to created the subpart in template.
     *
     * @return string
     */
    public function getSubModuleId()
    {
        return 'mkpostman_subscriber_main';
    }
    /**
     * Returns the label for Handler in SubMenu. You can use a label-Marker.
     *
     * @return string
     */
    public function getSubLabel()
    {
        return '';
    }
    /**
     * The class for the searcher
     *
     * @return string
     */
    protected function getListerClass()
    {
        return 'DMK\\Mkpostman\\Backend\\Lister\\SubscriberLister';
    }
}
```

###Lister

Damit der Search Handler auch eine Ausgabe durch führt, ist ein Lister notwendig.  
Ein Lister kümmert sich um die Bereitstellung von Filtermöglichkeiten,
der Ergebnisliste mit den darzustellenden Spalten 
und steuert auch das Repository an, um die Daten für die Ausgabe zu holen.
```php

/**
 * Subscriber lister
 *
 * @package TYPO3
 * @subpackage DMK\Mkpostman
 * @author Michael Wagner
 */
class SubscriberLister
    extends \Tx_Rnbase_Backend_Lister_AbstractLister
{
    /**
     * The Subscriber repository
     *
     * @return Tx_Rnbase_Domain_Repository_InterfaceSearch
     */
    protected function getRepository()
    {
        return \DMK\Mkpostman\Factory::getSubscriberRepository();
    }
    /**
     * The decorator to render the rows
     *
     * @return string
     */
    protected function getDecoratorClass()
    {
        return 'DMK\\Mkpostman\\Backend\\Decorator\\SubscriberDecorator';
    }
    /**
     * Liefert die Spalten für den Decorator.
     *
     * @param array $columns
     *
     * @return array
     */
    protected function addDecoratorColumns(
        array &$columns
    ) {
        $columns['email'] = array(
            'title' => 'label_tableheader_email',
            'decorator' => $this->getDecorator(),
        );
        $columns['name'] = array(
            'title' => 'label_tableheader_name',
            'decorator' =>  $this->getDecorator(),
        );
        
        return $columns;
    }
}
```

###Decorator

Ein Decorator kümmert sich um die Formatierung jeder einzelnen Spalte
jedes einzelnen Datensatzes für die Liste im Backendmodul.

```php
/**
 * Subscriber decorator
 *
 * @package TYPO3
 * @subpackage DMK\Mkpostman
 * @author Michael Wagner
 */
class SubscriberDecorator
    extends \Tx_Rnbase_Backend_Decorator_BaseDecorator
{
    /**
     * Renders the label column.
     *
     * @param \Tx_Rnbase_Domain_Model_DataInterface $item
     *
     * @return string
     */
    protected function formatEmailColumn(
        \Tx_Rnbase_Domain_Model_DataInterface $item
    ) {
        return sprintf(
            '<span title="UID: %2$d">%1$s</span>',
            $item->getEmail(),
            $item->getProperty('uid'),
        );
    }
    /**
     * Renders the label column.
     *
     * @param \Tx_Rnbase_Domain_Model_DataInterface $item
     *
     * @return string
     */
    protected function formatNameColumn(
        \Tx_Rnbase_Domain_Model_DataInterface $item
    ) {
        return trim($item->getFirstName() . ' ' . $item->getLastName()) ?: 'unknown';
    }
}
```

TODO!
