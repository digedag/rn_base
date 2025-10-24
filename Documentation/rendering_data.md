# Ausgabe von Daten

Innerhalb von TYPO3 muss man des öfteren Information aus der Datenbank in eine HTML-Seite rendern. In TYPO3 ist dafür die Marker-Technik sehr verbreitet. Ein Marker ist ein Platzhalter, den man in aller Regel an den Rauten erkennt: ###MARKER###.

Das Vorgehen herkömmliche Vorgehen ist recht einfach. Im Plugin bereitet man ein Array mit allen Markern vor und weist diesen einen Wert zu:

```php
  $markers['###MARKER1###'] = 'content1';
  $markers['###FIELD###'] = $myRowFromDB['field'];
```
TYPO3-Entwickler kennen diesen Code. Man findet in vielen Plugin größere Blöcke davon. In rn_base bekommt man diese Arbeit abgenommen und zusätzlich noch weitere TYPO3-Features als Zugabe geschenkt!

## Markerklassen
Für die Erstellung der Template-Marker sind sogenannte Markerklassen verantwortlich. Meist wird für jedes Entity-Model eine entsprechende Markerklasse angelegt. Es empfield sich von der Klasse **tx_rnbase_util_BaseMarker** zu erben und die Methode **parseTemplate()** zu implementieren:

```php
    /**
     * @param string $template das HTML-Template
     * @param Tx_Rnbase_Domain_Model_Base $item
     * @param tx_rnbase_util_FormatUtil $formatter der zu verwendente Formatter
     * @param string $confId Pfad der TS-Config
     * @param string $marker Name des Markers
     * @return String das geparste Template
     */
    public function parseTemplate($template, $item, $formatter, $confId, $marker) {
        // Es wird das MarkerArray mit den Daten des Records gefüllt.
        $ignore = self::findUnusedCols($item->getRecord(), $template, $marker);
        $markerArray = $formatter->getItemMarkerArrayWrapped($item->getRecord(), $confId , $ignore, $marker.'_', $item->getColumnNames());
        $wrappedSubpartArray = $subpartArray = array();
        // das Template rendern
        $out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);
        return $out;
    }
```
Das ist die kürzeste Variante für eine Markerklasse. Es werden nur die Marker erzeugt und im HTML-Template ersetzt. Darüber hinaus kümmern sich diese Klassen noch um die Erzeugung von Links und die Bereitstellung von Referenzen auf andere Klassen.

> Eine alternative Basisklasse ist **tx_rnbase_util_SimpleMarker**. Im Gegensatz zum BaseMarker ist diese Klasse nicht abstrakt. Sie kann sowohl direkt als fertiger Marker für Entities genutzt werden, als auch als Basisklasse für eigene Marker. Mehr dazu im im [entsprechenden Abschnitt](#simplemarker).

Im Beispielcode ist die wichtigste Zeile der Aufruf von **$formatter->getItemMarkerArrayWrapped()**. Das $item ist ein rn_base-Model. Und für jede Spalte aus der Datenbank wird automatisch ein passender TYPO3-Marker erstellt. Zusätzlich besteht aber automatisch die Möglichkeit die Werte per Typoscript zu manipulieren. Dafür wird die Variable $confId übergeben, die den aktuellen Typoscript-Pfad enthält. Dadurch, daß diese $confId, aber auch der Marker-Prefix $marker als Parameter übergeben werden, kann der Marker wiederverwendet werden.

In T3sports werden Teams gerendet. Das kann entweder in der Team-Liste oder Detailansicht geschehen. Oder aber das Team wird als Teil eines Spiels gerendert. Im ersten Fall wird der Marker mit **###TEAM_ ** beginnen. Im zweiten Fall startet der Marker mit **###MATCH_HOME_**. In beiden Fällen landet der Aufruf bei der selben Markerklasse für das Team. Nur die Parameter $confId und $marker unterscheiden sich. Jede Funktion die eine Markerklasse bereitstellt, ist somit in allen Views verfügbar, in denen die Entity gerendert wird. Und das ohne zusätzlichen Aufwand!

### Referenzen auf andere Entities
#### n-1 Relationen
Schauen wir uns das Beispiel aus dem letzten Abschnitt etwas genauer an. Die Entität *Match* enthält zwei Relationen auf die Entität *Team*, eine für die Heimmannschaft und eine für die Gastmannschaft. Wenn das Spiel gerendert wird, dann sollte sich der Marker für das Spiel sich wirklich nur um die Daten des Spiels kümmern. Natürlich will man aber trotzdem im HTML-Template auf die Attribute der Mannschaften zugreifen. Also sollte der Match-Marker diese Relationen bereitstellen. In der Methode **parseTemplate()** findet man dazu im MatchMarker den folgenden Aufruf:
```php
    $teamMarker = tx_rnbase::makeInstance('tx_cfcleaguefe_util_TeamMarker');
    if($this->containsMarker($template, $marker.'_HOME'))
        $template = $teamMarker->parseTemplate($template, $match->getHome(), $formatter, $confId.'home.', $marker.'_HOME');
    if($this->containsMarker($template, $marker.'_GUEST'))
        $template = $teamMarker->parseTemplate($template, $match->getGuest(), $formatter, $confId.'guest.', $marker.'_GUEST');
```
Die Variable **$marker** enthält bei direktem Aufruf den Normalfall den Wert **'MATCH'**. Die Markerklasse prüft also zunächst, ob es im HTML-Template einen Marker gibt, der mit **###MATCH_HOME** beginnt. In diesem Fall wird das Rendering für die Heimmannschaft gestartet. Analog die selbe Abfrage für das Team des Gasts. Wir der Marker gefunden, dann wird der Aufruf direkt an die Markerklasse für die Teams übergeben. Dieser bekommt den passenden Marker-Prefix und den Typoscript-Pfad übergeben. Als Ergebnis liefert er den HTML-String mit den ersetzten Markern.

#### 1-n Relationen
Ein gutes Beispiel für die Ausgabe von 1-n-Relationen findet man im TeamMarker von T3sports. Dort werden die Spieler und Trainer des Teams angezeigt:
```php
    if($this->containsMarker($template, $marker.'_PLAYERS'))
        $template = $this->addProfiles($template, $team, $formatter, $confId.'player.', $marker.'_PLAYER','players');
```
Der Aufruf sieht zunächst ganz ähnlich aus, wie bei der n-1-Relation. Nur wird hier nicht der Aufruf an die Markerklasse der Profiles übergeben, sondern zunächst eine weitere private Methode **_addProfiles()** aufgerufen. 

```php
    private function addProfiles($template, $team, $formatter, $confId, $markerPrefix, $joinCol) {
        $srv = tx_cfcleaguefe_util_ServiceRegistry::getProfileService();
        $fields['PROFILE.UID'][OP_IN_INT] = $team->getProperty($joinCol);
        $options = array();
        tx_rnbase_util_SearchBase::setConfigFields($fields, $formatter->configurations, $confId.'fields.');
        tx_rnbase_util_SearchBase::setConfigOptions($options, $formatter->configurations, $confId.'options.');
        $children = $srv->search($fields, $options);
        if(!empty($children) && !array_key_exists('orderby', $options)) // Default sorting
            $children = $this->sortProfiles($children, $team->getProperty($joinCol));
    
        $options['team'] = $team;
        $listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
        return $listBuilder->render($children,
                        new ArrayObject(), $template, 'tx_cfcleaguefe_util_ProfileMarker',
                        $confId, $markerPrefix, $formatter, $options);
    }
```
In den ersten Zeilen wird zunächst die Datenbankabfrage vorbereitet, um die Personen zu laden. Die Entities stehen am Ende in der Variable *$children**. Da es sich um ein Array handelt, kann der Aufruf nicht direkt an den ProfileMarker übergeben werden. Statt dessen wird der ListBuilder genutzt. Diese implementiert die Ausgabe der Liste über Subparts:
```
 ###TEAM_PLAYERS###
 <ul>
 ###TEAM_PLAYER###
 <li>###TEAM_PLAYER_UID###
 ###TEAM_PLAYER###
 </ul>
 ###TEAM_PLAYERS###
```
Und auch hier noch einmal der Hinweis: Da sich die Markerklassen gegenseitig aufrufen, funktioniert diese Ausgabe immer. Also auch wenn man eigentlich ein Spiel anzeigt, so kann man auf die Spieler der Teams zugreifen. Man muss lediglich die Marker richtig benennen. Der Subpart für die Spieler des Heimteams lautet dann **###MATCH_HOME_PLAYERS###**.

### Der ListBuilder
Für die Ausgabe von Listen sollte der ListBuilder (**tx_rnbase_util_ListBuilder**) verwendet werden. Dieser bietet sehr umfangreiche Features beim Rendern von Listen.

* Zeilennummer aktuelle Seite
* Zeilenummer gesamte Liste
* Pagebrowser
* Character-Browser
* Anzeige Treffer gesamt
* Performace-Debug
* Aufruf per Callback für extrem große Listen
* Datenbankfilterung über Filterklassen
* Konfiguration per Typoscript

Ein komplettes HTML-Template hat folgendes Aussehen. (TODO: erweitern!)
```
 ###PLAYERS###
 Treffer gesamt:  ###PLAYERCOUNT###
 <ul>
 ###PLAYER###
 <li> ###PLAYER### ###PLAYER_UID###
 ###PLAYER###
 </ul>
 ###PLAYEREMPTYLIST###
 Es wurden leider keine Daten gefunden.
 ###PLAYEREMPTYLIST###

 <!-- ###PAGEBROWSER### -->
 <div class="pagebrowser">
 ###PAGEBROWSER_CURRENT_PAGE###
 Page ###PAGEBROWSER_CURRENT_PAGE_NUMBER###
 ###PAGEBROWSER_CURRENT_PAGE###

 ###PAGEBROWSER_NORMAL_PAGE###
 ###PAGEBROWSER_NORMAL_PAGE_LINK###Page ###PAGEBROWSER_NORMAL_PAGE_NUMBER###  ###PAGEBROWSER_NORMAL_PAGE_LINK###
 ###PAGEBROWSER_NORMAL_PAGE###

 ###PAGEBROWSER_PREV_PAGE###
 &nbsp;###PAGEBROWSER_PREV_PAGE_LINK###<###PAGEBROWSER_PREV_PAGE_LINK###&nbsp;
 ###PAGEBROWSER_PREV_PAGE###

 ###PAGEBROWSER_NEXT_PAGE###
 &nbsp;###PAGEBROWSER_NEXT_PAGE_LINK###>###PAGEBROWSER_NEXT_PAGE_LINK###&nbsp;
 ###PAGEBROWSER_NEXT_PAGE###

 ###PAGEBROWSER_FIRST_PAGE###
 ###PAGEBROWSER_FIRST_PAGE_LINK### |< ###PAGEBROWSER_FIRST_PAGE_LINK###
 ###PAGEBROWSER_FIRST_PAGE###

 ###PAGEBROWSER_LAST_PAGE###
 ###PAGEBROWSER_LAST_PAGE_LINK### >| ###PAGEBROWSER_LAST_PAGE_LINK###
 ###PAGEBROWSER_LAST_PAGE###
</div>
<!-- ###PAGEBROWSER### -->

 ###PLAYERS###
```

#### Der PageBrowser
In rn_base wird eine sehr leistungsfähige Implementierung eines PageBrowser mitgeliefert. Die Ausgabe des PageBrowsers basiert auf einem HTML-Template und kann dadurch leicht angepasst werden. Folgende Einstellungen stehen per Typoscript zur Verfügung:

```
plugin.tx_myplugin.myview.filter.pagebrowser {
  # pagesize 
  limit = 10
  # should 404 be send if page pointer is out of range 
  ignorePageNotFound = true
  # custom "what" clause for sql statement 
  what = COUNT(*)
}
```


### Link-Erzeugung
In TYPO3 werden Links normalerweise für Subpart-Marker gesetzt:
```
###ARENA_SHOWLINK###Zur Detailansicht###ARENA_SHOWLINK###
```
Um die Marker für diesen Link zu erstellen, kann die Markerklasse die vom BaseMarker bereitgestellte Methode **initLink()** aufrufen:
```php
    $linkId = 'show';
    if($item->isPersisted()) {
        $this->initLink($markerArray, $subpartArray, $wrappedSubpartArray, $formatter, $confId, $linkId, $marker, array('stadium' => $item->uid), $template);
    }
```
Da die Linkerzeugung recht performancelastig ist, wird die Erzeugung nur durchgeführt, wenn die Marker überhaupt im Template vorkommen. Außerdem wird bei Bedarf auch automatisch nur die URL des Links bereitgestellt. Dafür kann muss man den Namen des Markers nur um *URL* erweitern. Im Beispiel oben wäre das **###ARENA_SHOWLINKURL###**.

Die auf diesem Weg erzeugten Links lassen sich extrem umfangreich per Typoscript konfigurieren. Dazu muss man natürlich den richtigen Einstiegspfad kennen. Im Aufruf oben ist die Variable **$confId** entscheidend. Gehen wir davon aus, daß sie mit dem Wert 'arena.' befüllt ist. Dann wird die Link-Konfiguration per Konvention unter **arena.links.show.** erwartet. Hier ein 
```
arena.links.show {
    pid = 123 # PID der Zielseite
    qualifier = t3sports # Qualifier/Prefix für den Link
    target = _top # Target-Attribut des Links
    fixedUrl = http://www.google.com/ # Eine feste URL
    absurl = 1 # Erzeugung einer absoluten URL 
    section =  # Setzt eine anchor-Wert
    typolink {
        # die normale typolink-Konfiguration von TYPO3 
    }
    atagparams {
        # Es können weitere Attribute für das A-Tag konfiguriert werden.
        style = border: 1px solid blue
    }
    useKeepVars = 1 # vorhandene Parameter des aktuellen Seite in den neuen Link integrieren, Default ist 0
    useKeepVars {
        # Diese Parameter können nun noch genauer spezifiziert werden. Alle Angaben wirken nur, wenn useKeepVars aktiviert ist
        # Außerdem funktioniert das nur für Parameter des verwendeten Plugins mit dem selben Qualifier
        deny = param1,param2 # bestimmte Parameter deaktivieren, den Rest erlauben
        allow = param1,param2 # nur bestimmte Parameter erlauben, den Rest ignorieren
        add = tx_ttnews::ttnews, tx_ttnews::* # Bestimmte oder alle Parameter einer anderen Extension integrieren
        add = param1=123 # Einen zusätzlichen Parameter mit dem Qualifier des Plugins integrieren 
    }
    noCache = 1 # URL wird mit Parameter no_cache=1 erzeugt
    noHash = 1 # URL wird ohne cHash erzeugt
    applyHtmlSpecialChars = 1 # Sonderzeichen im Link escapen. Funktioniert nur bei der Ausgabe von URIs, nicht für Tags.
    disable = 0 # Zusätzliche Möglichkeit die Linkerzeugung per Typoscript zu unterdrücken.
    removeIfDisabled = 0 # Wenn 1 wird der Link komplett mit dem verlinkten Inhalt entfernt 
}
```
So ziemlich alle Angaben sind hier optional. Wir bspw. keine PID konfiguriert, so wird automatisch auf die aktuelle Seite verlinkt.

### SimpleMarker
Es handelt sich um eine generische Implementierung des BaseMarker, der für einfache Entities genutzt werden kann. Der Funktionsumfang ist inzwischen so umfangreich, daß es sich auch für eigene Markerklassen anbietet, diese Implementierung als Basis zu nutzen.

#### Links per Typoscript erzeugen
Der SimpleMarker stellt neben den normalen Fähigkeiten zu Linkerzeugung noch weitere Features bereit. So werden die Links komplett per Typoscript angelegt. Eine Integration im Code ist nicht mehr notwendig. Auch hat man Zugriff auf Daten der aktuellen Entity und kann deren Werte als Parameter in der URL nutzen.

Der SimpleMarker liest automatisch die konfigurierten Links unter **yourentity.links.** aus und stößt die Linkgenerierung an. Im Ergebnis können die zugehörigen Marker direkt im HTML-Template verwendet werden. 

Spezielle Konfigurationen werden unter **yourentity.links.***linkname.***_cfg.** angegeben:
```
arena.links.show._cfg {
    params {
        # ein Attribut aus der Entity als Parameter setzen
        paramname = column_name
        param2 {
            # Call static method to build parameter value
            class = tx_mkeasy_marker_EasyDoc
            # Method Signature createSecureLinkParam($paramName, $cfgArr, $item)
            method = createSecureLinkParam
        }
    }
    removeIfDisabled = 1 # Der Link wird komplett entfernt, wenn er nicht erzeugbar ist. Andernfalls bleibt der Inhalt unverlinkt stehen
    charbrowser {    # Konfiguriert einen Buchstaben-Pagebrowser
        # ID des Browsers, falls mehrere auf einer Seite benötigt werden
        cbid = mybrowser
        colname = uid # Attribut aus der Entity, die für den Parameter verwendet werden soll
    }
}
```

#### Eigene Subparts integrieren

Seit Version 0.13.2 können über TypoScript Subparts bereitgestellt werden.

Für diese Funktionalität ist es notwendig, das die genutzte Marker-Klasse von der Klasse **tx_rnbase_util_SimpleMarker** erbt. Falls die Methode **parseTemplate()** nicht aufgerufen wird, ist es zusätzlich notwendig, die Methode **prepareSubparts()** aufzurufen, welche die Subparts bearbeitet.

Das TypoScript dazu wird unter **subparts** des zu rendernden Records definiert.

Folgendes Beispiel stellt 2 weitere Marker für die Verarbeitung bereit, bzw. prüft das Template auf Vorkommen und ersetzt diese entsprechend:

```
lib.mksearch.hit.subparts {
    is_ttcontent {
        ### definiert die marker (optional).
        marker {
            ### Definiert den Marker für den Subpart, der angezeigt werden soll (optional). Default ist VISIBLE
            visible = YES
            ### Definiert den Marker für den Subpart, der ausgeblendet werden soll (optional). Default ist HIDDEN
            hidden = NO
        }
        ### Definiert, welcher marker gerendert werden soll, der visible (venn true) oder der hidden (wenn false)
        visible = TEXT
        visible.value = 1
        visible.if {
            value = tt_content
            equals.data = field:contentType
        }
    }
}
```

In diesem TypoScript ist der Wert in visible entscheidend dafür, ob der visible (SEARCHRESULT_IS_TTCONTENT_YES) oder der hidden (SEARCHRESULT_IS_TTCONTENT_NO) Subpart ausgegeben wird.

Der Marker selbst setzt sich aus dem Marker des Records, dem Konfigurationsschlüssel unter subparts und dem Konfiguriertem Markern für visible und hidden zusammen.

Das Template dazu könnte so aussehen:

```
<h2>###SEARCHRESULT_TITLE###</h2>
<!-- ###SEARCHRESULT_IS_TTCONTENT_YES### START -->
    <p>Ich bin ein tt_content Datensatz, weil im feld contentType tt_content steht.</p>
<!-- ###SEARCHRESULT_IS_TTCONTENT_YES### END -->
<!-- ###SEARCHRESULT_IS_TTCONTENT_NO### START -->
    <p>Ich bin kein tt_content, ich bin ###SEARCHRESULT_CONTENTTYPE###</p>
<!-- ###SEARCHRESULT_IS_TTCONTENT_NO### END -->
<p>###SEARCHRESULT_CONTENT###</p>
```

Wenn im Feld **contentType** des Datensatzes der Wert **tt_content** enthalten ist, wird der YES Subpart ausgegeben und der NO Subpart aus dem Template entfernt. Ist etwas ungleich tt_content enthalten, wird der NO Subpart ausgegeben und der YES Subpart aus dem Template entfernt.
 