# Datenbankzugriff

Der direkte Zugriff auf die Datenbank  erfolgt über die Klasse `Sys25\RnBase\Database\Connection`. Über eine Anzahl von Optionen kann man den Bau der Abfragen vereinfachen. Wenn möglich wird für die Abfragen der QueryBuilder von TYPO3 verwendet. 

## SELECT Queries

Eine typische Abfrage hat folgenden Aufbau:

```php
$rows = Connection::getInstance()->doSelect('g.*', ['table' => 'tx_cfcleague_games', 'alias'=>'g'], [
    'limit' => 10,
    'enablefieldsbe' => 1,
    'debug' => 1,
    'wrapperclass' => \tx_cfcleague_models_Match::class,
    'where' => function(QueryBuilder $qb) {
        $qb->innerJoin('g', 'tx_cfcleague_teams', 't', 't.uid = g.home');
        $qb->andWhere( sprintf('t.uid = %s', $qb->createNamedParameter(7, \PDO::PARAM_INT)));
    }
]);
``` 

Folgende Optionen werden unterstützt:

* 'where' - Callback zum Zugriff auf den QueryBuilder
* 'groupby' - the GroupBy-Clause
* 'orderby' - the OrderBy-Clause
* 'sqlonly' - returns the generated SQL statement. No database access.
* 'limit' - limits the number of result rows
* 'wrapperclass' - A wrapper for each result rows
* 'pidlist' - A list of page-IDs to search for records
* 'recursive' - the recursive level to search for records in pages
* 'enablefieldsoff' - deactivate enableFields check
* 'enablefieldsbe' - force enableFields check for BE (this usually ignores hidden records)
* 'enablefieldsfe' - force enableFields check for FE (not yet implemented for QB)
* 'db' - external database: tx_rnbase_util_db_IDatabase
* 'ignorei18n' - do not translate record to fe language (not yet implemented for QB)
* 'forcei18n' - force the translation of the record (not yet implemented for QB)
* 'i18nolmode' - translation mode, possible value: 'hideNonTranslated'

Da man über die Option `where` Zugriff auf den QueryBuilder erhält sind einige Optionen natürlich redundant. Man kann also bspw. den `orderby` auch direkt im QueryBuilder setzen. Wichtig: Der `where`-Callback wurde aufgerufen nachdem alle anderen Optionen gesetzt wurden.

