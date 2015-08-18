# Datenbankzugriff mit dem Suchframework

Anfragen an die Datenbank sind sich in vielen Fällen sehr ähnlich. Meist ändert sich nur eine WHERE-Klausel, eine per JOIN eingebundene Tabelle oder die Reihenfolge der Sortierung. Der Zusammenbau dieser SQL-Queries im PHP-Code ist nicht nur unschön, sondern dadurch auch häufig redundant. Was liegt also näher als diese Arbeit zentral in einer Klasse zu erledigen, die dann im Bedarfsfall die passende Query zusammenstellt.

Schauen wir uns zunächst ein Beispiel an:
```php
$fields['TRADE.UID'][OP_IN_INT] = '22,3';
$fields['CAT.UID'][OP_EQ_INT] = '1234';
$options['orderby']['SPONSOR.NAME1'] = 'asc';
$srv = tx_t3sponsors_util_ServiceRegistry::getSponsorService();
$sponsors = $srv->search($fields, $options);
```

Mit diesem Code werden Sponsoren gesucht, die in den Branchen mit der UID 22 oder 3, sowie in der Sponsorenkategorie mit der UID 1234 sind. Sortiert wird das Ergebnis nach dem Namen des Sponsors.

Wie man sieht, wurden die Namen der DB-Tabellen durch Aliase ersetzt. Diese muss man natürlich kennen. Man muss aber nicht wissen, wie der JOIN auf die beteiligten Tabellen durchgeführt wird. Die Operatoren definieren schon die Typen der Argumente. Der Operator OP_IN_INT erwartet also ein kommaseparierte Liste von Integer-Werten. Ein möglicher Einbruch per SQL-Injection ist somit also ausgeschlossen. Ausserdem läßt sich diese Notation sehr gut in Typoscript abbilden:

```
filter {
 fields.TRADE.UID.OP_IN_INT = '22,3
 fields.JOINED.0 {
   value = test
   cols = SPONSOR.NAME1, SPONSOR.NAME2, SPONSOR.DESCRIPTION
   operator = OP_LIKE
 }
 options.orderby.SPONSOR.NAME1 = asc
}
```
Eingebunden wird dieses Typoscript über folgende Methoden:
```php
tx_rnbase_util_SearchBase::setConfigFields($fields, $configurations, 'filter.fields.');
tx_rnbase_util_SearchBase::setConfigOptions($options, $configurations, 'filter.options.');
```

Nun wird man natürlich nur selten alle Bedingungen im Typoscript-Code festlegen. Aber man hat hier die Möglichkeit die Abfragen zumindest zu beeinflussen.

##Ein paar weitere Beispiele

Nach MySQL-Datum suchen.
```php
$fields['TABLE.DATUM'][OP_GTEQ] = '2009-02-23';
$fields['TABLE.DATUM'][OP_LT] = '2009-02-29';
```
Eigener SQL-Code. Hier muss aber sichergestellt werden, daß die Tabelle mit in den From/Join aufgenommen wurde.
```php
$fields['TABLE.UID'][OP_GT_INT] = 0; // TABLE ist der ALIAS von tx_extkey_table
$fields[SEARCH_FIELD_CUSTOM] = 'tx_extkey_table.wert IN (SELECT uid FROM tx_extkey_table2 WHERE...)';
```
Mehrere Spalten per Oder verbinden
```php
$joined['value'] = trim($searchword);
$joined['cols'] = array('TABLE.NAME', 'TABLE.DETAILS', 'TABLE.ORT');
$joined['operator'] = OP_LIKE;
$fields[SEARCH_FIELD_JOINED][] = $joined;
```
Hier eine Abfrage mit einem eigenen SQL-Teil. Es muss hier immer sichergestellt sein, daß die beteiligten Tabellen mit im JOIN geladen werden.
```php
$fields[SEARCH_FIELD_CUSTOM] = "
(
  tx_fewo_saisonzeiten.von < '$von' AND tx_fewo_saisonzeiten.bis > '$von' OR
  tx_fewo_saisonzeiten.von < '$bis' AND tx_fewo_saisonzeiten.bis > '$bis' OR
  tx_fewo_saisonzeiten.von > '$von' AND tx_fewo_saisonzeiten.bis < '$bis'
)";
$fields['PREIS.FEWO'][OP_EQ_INT] = $fewo->getUid();
// Diese Bedingung wird für den JOIN auf die Zeiten benötigt
$fields['SAISONZEIT.UID'][OP_GT_INT] = 0;
$options['distinct'] = 1;
```

## Die Implementierung

Die Abfragen erfolgen immer genau auf eine Zieltabelle. Zwar können JOINs zu anderen Tabellen verwendet werden, aber im Ergebnis-Set sind immer nur die Objekte der Zieltabelle. Für jede Zieltabelle muss ein Such-Klasse implementiert werden, die von der Basisklasse **tx_rnbase_util_SearchBase** erbt. Auch hier wird wieder das Pattern *Inversion of control* eingesetzt. Die Basisklasse übernimmt also die Steuerung und die Kindklassen liefern nur die notwendigen Informationen.

Im Fall der Suche sind das die Definition der beteiligten Tabellen sowie die notwendigen JOINs. Hier als Beispiel der Zugriff auf die Tabelle tx_t3sponsors_companies:

```php
class tx_t3sponsors_search_Sponsor extends tx_rnbase_util_SearchBase {

	protected function getTableMappings() {
		$tableMapping = array();
		$tableMapping['SPONSOR'] = 'tx_t3sponsors_companies';
		$tableMapping['CATMM'] = 'tx_t3sponsors_categories_mm';
		$tableMapping['CAT'] = 'tx_t3sponsors_categories';
		$tableMapping['TRADEMM'] = 'tx_t3sponsors_trades_mm';
		$tableMapping['TRADE'] = 'tx_t3sponsors_trades';
		// Hook to append other tables
		tx_rnbase_util_Misc::callHook('t3sponsors','search_Sponsor_getTableMapping_hook',
			array('tableMapping' => &$tableMapping), $this);
		return $tableMapping;
	}

  protected function getBaseTable() {
  	return 'tx_t3sponsors_companies';
  }
	protected function getBaseTableAlias() {return 'SPONSOR';}
  function getWrapperClass() {
  	return 'tx_t3sponsors_models_Sponsor';
  }

  protected function getJoins($tableAliases) {
  	$join = '';
    if(isset($tableAliases['CATMM']) || isset($tableAliases['CAT'])) {
    	$join .= ' JOIN tx_t3sponsors_categories_mm CATMM ON SPONSOR.uid = CATMM.uid_foreign AND CATMM.tablenames = \'tx_t3sponsors_companies\'';
    }
    if(isset($tableAliases['CAT'])) {
    	$join .= ' JOIN tx_t3sponsors_categories CAT ON CAT.uid = CATMM.uid_local';
    }
    if(isset($tableAliases['TRADEMM']) || isset($tableAliases['TRADE'])) {
    	$join .= ' JOIN tx_t3sponsors_trades_mm TRADEMM ON SPONSOR.uid = TRADEMM.uid_foreign AND TRADEMM.tablenames = \'tx_t3sponsors_companies\'';
    }
    if(isset($tableAliases['TRADE'])) {
    	$join .= ' JOIN tx_t3sponsors_trades TRADE ON TRADE.uid = TRADEMM.uid_local';
    }
    // Hook to append other tables
		tx_rnbase_util_Misc::callHook('t3sponsors','search_Sponsor_getJoins_hook',
			array('join' => &$join, 'tableAliases' => $tableAliases), $this);

    return $join;
  }
	protected function useAlias() {
		return TRUE;
	}
}
```

Ich denke der Code ist weitestgehend selbsterklärend. Es sind derzeit JOINs auf vier Tabellen möglich.

Abschließend noch ein Blick auf vorhandene Optionen bei der Abfrage:

* limit - maximale Anzahl von Datensätzen
* offset - Start bei einem bestimmten Datensatz
* i18n - Sprach-Support von TYPO3
* enablefieldsoff - Die Felder deleted und hidden komplett ignorieren
* enablefieldsbe - Bei der Sichtbarkeit wird nur das Feld deleted berücksichtigt
* enablefieldsfe - Bei der Sichtbarkeit werden die Felder deleted und hidden berücksichtigt
* groupby - GROUP BY auf bestimmte Spalten
* orderby - Sortierung
* count - Es werden keine Datensätze, deren Anzahl geliefert
* debug - Der erzeugte SQL-String wird im FE als Debugausgabe angezeigt. Sehr nützlich bei der Fehlersuche. :-)


## enableFields nutzen
Per default werden die enableFields automatisch und korrekt (abhängig vom BE etc.) für die Tabelle des Searchers gesetzt. Allerdings gilt das nicht für evtl. gejointe Tabellen. Dafür gibt es die Möglichkeit die Aliase kommasepariert per TypoScript zu konfigurieren, für welche die enableFields ebenfalls gesetzt werden.

```
filter {
 options.enableFieldsForAdditionalTableAliases = CAT
}
```
Wenn loadHiddenObjects in der Extension Konfiguration gesetzt wurde und jemand im Backend eingeloggt ist, dann werden
im Frontend auch versteckte Datensätze angezeigt.
