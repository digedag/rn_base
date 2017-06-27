sys_category
=========

TYPO3 bietet mit der sys_category Tabelle eine zentrale und generische Möglichkeit, um beliebige Datensätze zu kategorisieren. Damit das einfach nutzbar wird, bietet rn_base ein paar Hilfsmethoden. Dadurch beschränkt sich die Einbindung auf ein paar Methodenaufrufen und ein wenig Konfiguration. Und schon können im Frontend Datensätze anhand von sys_category Kategorien angezeigt werden.

TCA erweitern
-----
Für die Tabelle, welche über sys_category kategorisierbar sein soll, muss z.B. folgendes ergänzt werden:

ext_tables.sql

```
    my_categories_field int(11) DEFAULT '0' NOT NULL
```

Um die TCA zu erweitern, einfach in **Configuration/TCA/Overrides/my_table.php** folgendes ausführen (wichtig dass es in Overrides passiert):

```php

    tx_rnbase_util_Extensions::makeCategorizable(
        'my_ext_key',
        'my_table',
        'my_categories_field'
    );

```

[TYPO3 Dokumentation](https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/Categories/Index.html)

Search Klasse erweitern
-----
Damit mit den rn_base Methoden gesucht werden kann (basiert auf tx_rnbase_util_SearchBase), muss die Search Klasse der betroffenen Tabelle, noch die notwendigen Joins bereit stellen. Per default wird das über den Alias SYS_CATEGORY gemacht.

in getTableMappings() muss folgender Aufruf ergänzt werden:

```php

    $existingTableMappings = tx_rnbase::makeInstance('Tx_Rnbase_Category_SearchUtility')->addTableMapping($existingTableMappings);

```

Der Methode kann alternativ als 2. Parameter ein anderer Alias als SYS_CATEGORY übergeben werden.

in getJoins() muss folgender Aufruf ergänzt werden:

```php

    $joins .= $tx_rnbase::makeInstance('Tx_Rnbase_Category_SearchUtility')->addJoins(
        $this->getBaseTable(), $this->getBaseTableAlias(), 'my_categories_field', $tableAliases
    );

```

Der Methode kann alternativ als 5. Parameter ein anderer Alias als SYS_CATEGORY übergeben werden.

Damit kann nun bei Suchabfragen über SYS_CATEGORY.uid eine Einschränkung auf eine sys_category vorgenommen werden.

Filterung im Frontend integrieren
-----

rn_base ermöglicht es Datensätze anhand von sys_category Einträgen zu filtern. Dabei muss der zuständige Filter einer Listenansicht einfach von tx_rnbase_filter_BaseFilter erben oder direkt dieser sein.

**filtern anhand der sys_category einer aktuellen Detailansicht**

Ist man z.B. auf der Detailseite einer News, können einfach Datensätze anderer Typen (z.B. Produkte) angezeigt werden, welche die gleiche Kategorien haben. (natürlich müssen alle Tabelle sys_category wie oben beschrieben einbinden). Dazu wird das Plugin für die normale Listenansicht auf Basis von rn_base verwendet. In diesem Beispiel also auf einer News Detailseite (URL Parameter -> my_first_ext[news]) ein Plugin für die Listenansicht von Produkten einfügen. Dann noch das folgende exemplarische TypoScript (damit es auch anders herum funktioniert, muss diese Konfiguration dann natürlich auch in der Listenansicht der News verfügbar gemacht werden):

```
plugin_tx_mkextension.myListView.myFilterConfId.useSysCategoriesOfItemFromParameters = 1
### wir brauchen noch die Konfogiration für die unterstützten Parameter
plugin_tx_mkextension.myListView.myFilterConfId.useSysCategoriesOfItemFromParameters.supportedParameters {
    ### Konfiguration für die einzelnen Typen, bei denen andere Datentypen mit der
    ### gleichen sys_category ausgegeben werden sollen/können
    0 {
        parameterQualifier = my_first_ext
        parameterName = news
        table = my_first_ext_news
        categoryField = sys_categories
    }
    1 {
        parameterQualifier = my_second_ext
        parameterName = product
        table = my_second_ext_products
        categoryField = sys_categories
    }
}
```

Wenn ein anderer Alias für die sys_category als SYS_CATEGORY verwendet werden soll, dann diesen einfach so konfigurieren:

```
plugin_tx_mkextension.myListView.myFilterConfId.useSysCategoriesOfItemFromParameters.sysCategoryTableAlias = MY_NEW_ALIAS
```

**filtern anhand der sys_category aus dem Inhaltselement**

Bei jedem Inhaltselement können im BE sys_category Einträge gewählt werden. Diese lassen sich ganz einfach zum filtern verwenden. Dazu lediglich folgendes TypoScript:

```
plugin_tx_mkextension.myListView.myFilterConfId.useSysCategoriesOfPlugin = 1
```

Wenn ein anderer Alias für die sys_category als SYS_CATEGORY verwendet werden soll, dann diesen einfach so konfigurieren:

```
plugin_tx_mkextension.myListView.myFilterConfId.useSysCategoriesOfPlugin.sysCategoryTableAlias = MY_NEW_ALIAS
```

**filtern anhand einer sys_category aus den Parametern**

Es ist auch möglich anhand einer übergebenen Kategorie in den Request-Parametern zu filtern. Dazu folgendes TypoScript:

```
plugin_tx_mkextension.myListView.myFilterConfId.useSysCategoriesFromParameters = 1
### für den Parameter my_ext[my_category]
plugin_tx_mkextension.myListView.myFilterConfId.useSysCategoriesFromParameters {
    parameterQualifier = my_ext
    parameterName = my_category
}
```

Wenn ein anderer Alias für die sys_category als SYS_CATEGORY verwendet werden soll, dann diesen einfach so konfigurieren:

```
plugin_tx_mkextension.myListView.myFilterConfId.useSysCategoriesFromParameters.sysCategoryTableAlias = MY_NEW_ALIAS
```

So könnte z.B. mit einem Kategoriefilter eine einfacher Filterung integriert werden, die Nutzer steuern können.

**group by**

Wenn nach mehr als einer Kategorie gefiltert wird und ein Datensatz mehrere der gefilterten Kategorien hat, dann muss group by gesetzt werden. Ansonsten werden Datensätze mehrfach angezeigt. Das TypoScript könnte so aussehen:


```
plugin_tx_mkextension.myListView.myFilterConfId.options {
    groupby = MY_BASE_ALIAS.uid
    ### meistens notwendig, außer es sollen keine Models geliefert werden
    forcewrapper = 1
}
```
