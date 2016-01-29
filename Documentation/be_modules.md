#Modulentwicklung
##Entwicklung von BE-Modulen mit Unterstützung von rn_base

Mit Version 0.5.3 bietet rn_base auch Unterstützung für die Modulentwicklung an. Dabei werden eigentlich nur die Standard-Module von TYPO3 auf Basis der Klasse t3lib_scbase so bereitgestellt, daß man sie einfach verwenden kann.

Grundlegendes

Damit man in der Modul-Leiste einen neuen Eintrag bekommt, muss das Modul zunächst bei TYPO3 angemeldet werden. Dies geschieht in der ext_table.php:
```php
if (TYPO3_MODE == 'BE') {
 // Einbindung des eigentlichen BE-Moduls. Dieses bietet eine Hülle für die eigentlichen Modulfunktionen
 tx_rnbase_util_Extensions::addModule('user', 'txmkmailerM1', "", tx_rnbase_util_Extensions::extPath($_EXTKEY) . 'mod1/');
```
Diese Zeilen kann man sich auch vom Kickstarter erzeugen lassen. Wichtig: jedes TYPO3-Modul benötigt ein eigenes Verzeichnis. Darin sucht TYPO3 dann automatisch nach einer conf.php und index.php für den Aufruf des Modul.

Da die Modul-Leiste bei zuvielen Modulen schnell auch unübersichtlich wird, sollte man mit eigenes Modulen sparsam umgehen. Statt viele kleine Module zu schreiben, sollte man lieber die Module so gestalten, daß sie verschiedene Aufgaben übernehmen können. Mit dem Modul Web-Funktionen zeigt TYPO3 wie man daß mit einem Funktions-Umschalter im Modul realisieren kann. Die verfügbaren Funktionen können dann wieder dynamisch erweitert werden. Mit der Klasse t3lib_scbase liefert TYPO3 sogar eine Basisklasse in der die wichtigsten Dinge für so ein dynamisches Modul sogar schon implementiert sind. Wie so oft, wird es aber sehr umständlich gemacht. Und zu allem Überfluß erzeugt der Kickstarter sogar ein Dummy-Modul, das diese Erweiterbarkeit zerstört...

tx_rnbase_mod_BaseModule

Wenn man davon erbt, hat man 2 Möglichkeiten das Backend Modul zu rendern. Entweder der alte Weg mit der
DocumentTemplate Klasse von TYPO3, welche auf einem Haupttemplate basiert. (Das Template wird übber TS
konfiguriert) Oder man verwendet den Weg über die ModuleTemplate Klasse. Dabei muss man dann in seiner
Backend Modul Klasse die Methode useModuleTemplate überschreiben und dort TRUE liefern.

TODO!
