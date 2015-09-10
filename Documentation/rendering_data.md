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
	 * @param tx_rnbase_model_base $item
	 * @param tx_rnbase_util_FormatUtil $formatter der zu verwendente Formatter
	 * @param string $confId Pfad der TS-Config
	 * @param string $marker Name des Markers
	 * @return String das geparste Template
	 */
	public function parseTemplate($template, &$item, &$formatter, $confId, $marker) {
		// Es wird das MarkerArray mit den Daten des Records gefüllt.
		$ignore = self::findUnusedCols($item->record, $template, $marker);
		$markerArray = $formatter->getItemMarkerArrayWrapped($item->record, $confId , $ignore, $marker.'_', $item->getColumnNames());
		$wrappedSubpartArray = $subpartArray = array();
		// das Template rendern
		$out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);
		return $out;
	}
```
Das ist die kürzeste Variante für eine Markerklasse. Es werden nur die Marker erzeugt und im HTML-Template ersetzt. Darüber hinaus kümmern sich diese Klassen noch um die Erzeugung von Links und die Bereitstellung von Referenzen auf andere Klassen.

Im Beispielcode ist die wichtigste Zeile der Aufruf von **$formatter->getItemMarkerArrayWrapped()**. Das $item ist ein rn_base-Model. Und für jede Spalte aus der Datenbank wird automatisch ein passender TYPO3-Marker erstellt. Zusätzlich besteht aber automatisch die Möglichkeit die Werte per Typoscript zu manipulieren. Dafür wird die Variable $confId übergeben, die den aktuellen Typoscript-Pfad enthält. Dadurch, daß diese $confId, aber auch der Marker-Prefix $marker als Parameter übergeben werden, kann der Marker wiederverwendet werden.

In T3sports werden Teams gerendet. Das kann entweder in der Team-Liste oder Detailansicht geschehen. Oder aber das Team wird als Teil eines Spiels gerendert. Im ersten Fall wird der Marker mit **###TEAM_ ** beginnen. Im zweiten Fall startet der Marker mit **###MATCH_HOME_**. In beiden Fällen landet der Aufruf bei der selben Markerklasse für die Team. Nur die Parameter $confId und $marker unterscheiden sich. Jede Funktion die eine Markerklasse bereitstellt, ist somit in allen Views verfügbar, in denen die Entity gerendert wird. Und das ohne zusätzlichen Aufwand!

### Referenzen auf andere Entities

### Link-Erzeugung


