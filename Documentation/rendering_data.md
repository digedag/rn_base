# Ausgabe von Daten

Innerhalb von TYPO3 muss man des öfteren Information aus der Datenbank in eine HTML-Seite rendern. In TYPO3 ist dafür die Marker-Technik sehr verbreitet. Ein Marker ist ein Platzhalter, den man in aller Regel an den Rauten erkennt: ###MARKER###.

Das Vorgehen herkömmliche Vorgehen ist recht einfach. Im Plugin bereitet man ein Array mit allen Markern vor und weist diesen einen Wert zu:

```php
  $markers['###MARKER1###'] = 'content1';
  $markers['###FIELD###'] = $myRowFromDB['field'];
```
TYPO3-Entwickler kennen diesen Code. Man findet in vielen Plugin größere Blöcke davon. In rn_base bekommt man diese Arbeit abgenommen und zusätzlich noch weitere TYPO3-Features als Zugabe geschenkt!

TODO!
