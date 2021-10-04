Cache-API
=========

TYPO3 hat mit der Version 4.3 eine eigene Cache-API eingeführt. Der Umgang damit ist leider nicht ganz trivial. Außerdem ist es nervig, wenn man in seiner Extension den Cache nutzen will, aber auch kompatibel zu älteren Versionen bleiben möchte. Dann müssten man bei jedem Zugriff die TYPO3-Version prüfen.

rn_base schafft hier Abhilfe und bietet seit der Version 0.6.4 einen passenden Wrapper. Die Anwendung ist zunächst ganz einfach:

```php
$marker = tx_rnbase_cache_Manager::getCache('mycache')->get('tx_t3users_util_FEUserMarker');
if(!$marker) {
  $marker = tx_rnbase::makeInstance('tx_t3users_util_FEUserMarker');
  tx_rnbase_cache_Manager::getCache('mycache')->set('tx_t3users_util_FEUserMarker', $marker, 0);
}
```
In dem Beispiel wird der Cache also als Objekt-Cache für eine Markerklasse verwendet. Der dritte Parameter in der set-Methode ist die Lebenszeit der Daten in Sekunden. Wenn man in einer alteren Version von TYPO3 arbeiten dann kann der Code genauso aussehen. Allerdings wird der Cache dann niemals ein Ergebnis liefern. Da man Ergebnisse aus dem Cache aber sowieso immer prüfen muss, ist das kein Problem. Grundsätzlich könnte man aber auch andere Cache-Implementierungen anbinden.

Konfiguration für TYPO3-Cache
-----------------------------

Wenn man in TYPO3 4.3 arbeitet wird automatisch der TYPO3-Cache verwendet. Die Konfiguration muss dann auch direkt dafür erfolgen. Das geschieht über Einträge in der TYPO3_CONF_VARS:

```php
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['your_cache_name']['backend'],
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['your_cache_name']['options']
```
Der Cache-Name ist der String, den man auch dem Cache-Manager übergibt. Wenn keine Konfiguration gesetzt ist, dann wird die Klasse t3lib_cache_backend_TransientMemoryBackend als Backend (Speicher) verwendet. Hier werden die Daten nur für den aktuellen Request gespeichert. Für andere Caches wie MemCached oder den DB-Cache einfach mal nach Beispielen im Web suchen.

Beispielconfig für MemCached:
```php
 $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['your_cache_name']['backend'] = 't3lib_cache_backend_MemcachedBackend';
 $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['your_cache_name']['options'] = array(
            'servers' => array('localhost:11211'),
        );
```
Weitere Infos gibt es im Web. Aber aufpassen, da sind einige Beispiele veraltet und funktioniert so nicht!

Bei der Verwendung von rn_base kann man seinen Cache systemunabhängig registrieren. Die Cache-Manager-Klasse stellt dafür eine passende Methode bereit:

```php
    tx_rnbase_cache_Manager::registerCache('your_cache_name',
            tx_rnbase_cache_Manager::CACHE_FRONTEND_VARIABLE,
            tx_rnbase_cache_Manager::CACHE_BACKEND_MEMCACHED,
            array(
                'servers' => array('localhost:11211'),
            ));
```


Konkretes Beispiel für DB-basierten Cache
-----------------------------------------
Für mkforms wird ein Cache verwendet, um Session-Daten zu persistieren. Der Cache hat die ID mkforms. Mit folgender Config in der Datei typo3conf/localconf.php wird der Cache auf Datenbank-Basis eingerichtet:
```php
    tx_rnbase_cache_Manager::registerCache('mkforms',
            tx_rnbase_cache_Manager::CACHE_FRONTEND_VARIABLE,
            tx_rnbase_cache_Manager::CACHE_BACKEND_T3DATABASE,
            array(
        'cacheTable' => 'tx_mkforms_cache',
        'tagsTable' => 'tx_mkforms_tags',
            ));
```
Natürlich müssen die beiden DB-Tabellen tx_mkforms_cache und tx_mkforms_tags vorher angelegt werden:
```sql
 CREATE TABLE tx_mkforms_cache (
    id int(11) NOT NULL auto_increment,
    identifier varchar(128) DEFAULT '' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    content longtext NOT NULL,
    lifetime int(11) DEFAULT '0' NOT NULL,

    PRIMARY KEY (id),
    KEY cache_id (identifier)
 );

 #
 # Unused dummy table for TYPO3 caching framework
 #
 CREATE TABLE tx_mkforms_tags (
    id int(11) NOT NULL auto_increment,
    identifier varchar(128) DEFAULT '' NOT NULL,
    tag varchar(128) DEFAULT '' NOT NULL,

    PRIMARY KEY (id),
    KEY cache_id (identifier),
    KEY cache_tag (tag)
 );
```
Die Tags-Tabelle wird nicht verwendet, aber von TYPO3 erwartet.

Konkretes Beispiel für MemCached
--------------------------------
So sollte es für memcached aussehen. Das ist aber noch ungetestet:

```php
    tx_rnbase_cache_Manager::registerCache('mkforms',
            tx_rnbase_cache_Manager::CACHE_FRONTEND_VARIABLE,
            tx_rnbase_cache_Manager::CACHE_BACKEND_MEMCACHED,
            array(
        'servers' => array('localhost:11211'),
            ));
```

Manuelles Cache leeren
----------------------
Um einen Cache über die TYPO3-Funktion "Clear all Caches" auch mit zu leeren, wird (zumindest unter TYPO3 4.5?) folgende Konfiguration in der ext_tables.php benötigt:

```php
if (TYPO3_MODE == 'BE') {
    // register the cache in BE so it will be cleared with "clear all caches"
    try {
        t3lib_cache::initializeCachingFramework();
        // State cache
        $GLOBALS['typo3CacheFactory']->create(
                 'cf_mkmms',
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['mkmms']['frontend'],
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['mkmms']['backend'],
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['mkmms']['options']
        );
    } catch(t3lib_cache_exception_NoSuchCache $exception) {

    }
}
```
