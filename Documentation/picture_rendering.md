# Bilder im Frontend rendern
Mit rn_base hat man die Möglichkeit recht einfach Bilder über Marker-Templates zu rendern. Damit erhält man eine Flexibilität, die es ermöglicht, nahezu jede Javascript Gallerie in TYPO3 zu integrieren. Es wird sowohl DAM als auch FAL unterstützt. Bei der Verwendung von FAL erfolgt die Ausgabe über die Referenz-Datensätze, so daß man auch Zugriff auf alle gesetzten Meta-Daten hat. 

## Bildausgabe über printImages
Die Ausgabe der Bilder erfolgt über die Klassen **tx_rnbase_util_DAM** bzw. **tx_rnbase_util_FAL** mit Hilfe der Funktion **printImages**. Diese Funktion wird als userFunc per Typoscript gesetzt. Eine minimale Konfiguration für ein normales rn_base-Plugin hat folgendes Aussehen:

``` 
plugin.tx_myplugin.myview.myitem.dcpictures = USER
plugin.tx_myplugin.myview.myitem.dcpictures {
#    includeLibs = EXT:rn_base/util/class.tx_rnbase_util_TSDAM.php
#    userFunc = tx_rnbase_util_TSDAM->printImages
    includeLibs = EXT:rn_base/util/class.tx_rnbase_util_TSFAL.php
    userFunc = tx_rnbase_util_TSFAL->printImages
    refField = mypicturefield
    refTable = tx_myext_mytable
    template = EXT:myext/Resources/Private/lightboxpics.html
    subpartName = ###PICTURES###
    media =< lib.mediaBase
}
``` 
Es werden also mit **refField** und **refTable** die notwendigen Angaben für die Ermittlung der Referenzen aus der Datenbank gesetzt. Danach konfigiert man schon das HTML-Template und den gewünschten Subpart. Der **media**-Teil wird etwas weiter unten erklärt. Schauen wir uns zunächst das HTML-Template an:

``` 
###PICTURES###
###MEDIAS######MEDIA###
<a href="###MEDIA_FILE###" rel="lightbox[pictures]">###MEDIA_THUMBNAIL###</a>###MEDIA_TARGETLINK###Link###MEDIA_TARGETLINK###
###MEDIA######MEDIAS###
###PICTURES###
``` 
Wie man erkennen kann, erfolgt die Ausgabe der Bilder über den ListBuilder von rn_base. Der Subpart **MEDIA** enthält die Bilder und das MEDIA-Objekt rendert in diesem Beispiel die zwei Attribute FILE und THUMBNAIL. Damit wird zum einen ein Image-Pfad gerendert und zum anderen mit Thumbnail ein kleines Vorschaubild. Das erfolgt mit diese Typoscript-Konfiguration:
```
lib.mediaBase {
  file = IMG_RESOURCE
  file.file.import.field = file
  file.file.maxW = 1500
  file.file.maxH = 1000
  thumbnail < .file
  thumbnail = IMAGE
  thumbnail.file.maxW = 150
  thumbnail.file.maxH = 100
  thumbnail.params = border="0"
  thumbnail.titleText.field = title
}
```
### weitere Optionen
Man kann die Ausgabe der Bilder noch mit limit und offset beschränken:
```
plugin.tx_myplugin.myview.myitem.dcpictures = USER
plugin.tx_myplugin.myview.myitem.dcpictures {
    # Optional setting for limit
    limit = 1
    offset = 2
    # force another reference column (other than UID or _LOCALIZED_UID)
    forcedIdField = otheruidfield
}
```
Außerdem steht ein zusätzlicher Marker bereit, der die UID des referenzierten Objektes liefert: **###MEDIA_PARENTUID###**. Dieser kann genutzt werden, um einen eindeutigen Identifier für Bildgruppen zu setzen.

## Bilder aus Seiteneigenschaften auslesen (FAL only)
Wenn man eine Seite bearbeitet, hat man die Möglichkeit im Tab Resourcen der Seite Bilder zu zuordnen. Diese Bilder können dann als Kopfgrafiken verwendet werden. Folgendes Beispiel zeigt den Zugriff auf die Bilder. Dabei wird automatisch im übergeordneten Seiten nach Bildern gesucht, wenn auf der aktuellen Seite kein Bild gefunden wurde. Zusätzlich wird ein gesetzter Link gleich mit verarbeitet.

```
lib.page.headerimage = USER
lib.page.headerimage {
    userFunc = tx_rnbase_util_TSFAL->printImages
    # Die Referenzen direkt per stdWrap ermitteln
    references {
        uid.data = levelmedia:-1, slide
    }
    template = EXT:rn_base/res/simplegallery.html
    # die vorbereiteten IMAGE-Objekte zuweisen
    media =< lib.mediaBase
    media {
        # Link konfigurieren
        links.target {
            pid.field = link
            # Nur bei gesetztem Wert verlinken
            disable = TEXT
            disable.value = TRUE
            disable.if.isFalse.field = link
        }
  }
}
```
