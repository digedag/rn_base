# Verwendung von fluid in Plugins

**Hinweis:** für diese Umsetzung besteht kein offizieller Support.

Es gibt den View Sys25\RnBase\ExtBaseFluid\View\Action, um die Templates mit fluid rendern zu können. Per default werden die fluid Dateien in den Ordner Templates, Layouts und Partials in 'EXT:' . $extensionKey . '/Resources/Private/' erwartet. Das kann auch überschrieben bzw. weitere hinzugefügt werden, ganz nach der Konvention von ExtBase/Fluid:

```
plugin.tx_myextension {
    view {
        templateRootPaths.0 = EXT:plugin_tx_mkextension/Resources/Private/MyOtherTemplates/
        partialRootPaths.0 = EXT:plugin_tx_mkextension/Resources/Private/MyOtherPartials/
        layoutRootPaths.0 = EXT:plugin_tx_mkextension/Resources/Private/MyOtherLayouts/
    }
    settings {
    }
}
```

Ist plugin.tx_myextension.templatePath konfiguriert, dann überschreibt das die Einstellungen in plugin.tx_myextension.view.templateRootPaths.0. Das direkte setzen eines Templates mit plugin.tx_myextension.myAction.template.file bzw. plugin.tx_myextension.myActionTemplate ist auch weiterhin möglich.

#### fluid standalone
Es gibt auch einen Standalone-View, um Templates unabhängig von einer Action zu rendern. Das kann so genutzt werden:

```php

/* @var $configurations Tx_Rnbase_Configuration_ProcessorInterface */
$view = \Sys25\RnBase\ExtBaseFluid\View\Factory::getViewInstance($configurations);
$view->setPartialRootPaths($partialsPaths);
$view->setLayoutRootPaths($layoutPaths);
$view->setTemplatePathAndFilename($absolutePathToTemplate);
$view->assignMultiple(array('someData' => 'test'));
return $view->render();

```

#### translate ViewHelper
Damit wird ein label über die rnbase configuration übersetzt.
In folgenden Beispielen wird das Label `label.description` übersetzt:

```html
{namespace rn=Sys25\RnBase\ExtBaseFluid\ViewHelper}

<!-- translate inline from variable -->
{varWithLabelDescriptionKey -> rn:translate()}

<!-- translate inlnie with connation -->
{f:format.raw(value: 'label.description') -> rn:translate()}

<!-- translate inlnie with key -->
{rn:translate(key: 'label_filter_reset')}

<!-- translate as fluid tag with key-->
<rn:translate key="label.description" />

<!-- translate as fluid tag with child content-->
<rn:translate>label.description</rn:translate>
```


#### configurations ViewHelper
Damit kann auf die get Methode der configurations zugegriffen werden:

```html

{namespace rn=Sys25\RnBase\ExtBaseFluid\ViewHelper}

{rn:configurations.get(confId: confId, typoscriptPath: 'my.path')}

```

#### pagebrowser ViewHelper
Damit kann auf die get Methode der configurations zugegriffen werden:

```html

{namespace rn=Sys25\RnBase\ExtBaseFluid\ViewHelper}

<div class="pagebrowser">
    <ul class="pagination flex-wrap">
        <rn:pageBrowser
            maxPages="5"
            hideIfSinglePage="1"
        >
            <rn:pageBrowser.firstPage
                data-wrap='<li class="page-item">|</li>'
                addQueryString="TRUE"
                argumentsToBeExcludedFromQueryString="{0: 'id', 1: 'L', 2: 'cHash'}"
                section="comments"
            >&lt;&lt;</rn:pageBrowser.firstPage>
            <rn:pageBrowser.prevPage
                data-wrap='<li class="page-item">|</li>'
                addQueryString="TRUE"
                argumentsToBeExcludedFromQueryString="{0: 'id', 1: 'L', 2: 'cHash'}"
                section="comments"
            >&lt;</rn:pageBrowser.prevPage>
            <rn:pageBrowser.currentPage
                data-wrap='<li class="page-item active">|</li>'
                addQueryString="TRUE"
                argumentsToBeExcludedFromQueryString="{0: 'id', 1: 'L', 2: 'cHash'}"
                class="active"
                usePageNumberAsLinkText="1"
                section="comments"
            />
            <rn:pageBrowser.normalPage
                data-wrap='<li class="page-item">|</li>'
                addQueryString="TRUE"
                argumentsToBeExcludedFromQueryString="{0: 'id', 1: 'L', 2: 'cHash'}"
                usePageNumberAsLinkText="1"
                section="comments"
            />
            <rn:pageBrowser.nextPage
                data-wrap='<li class="page-item">|</li>'
                addQueryString="TRUE"
                argumentsToBeExcludedFromQueryString="{0: 'id', 1: 'L', 2: 'cHash'}"
                section="comments"
            >&gt;</rn:pageBrowser.nextPage>
            <rn:pageBrowser.lastPage
                data-wrap='<li class="page-item">|</li>'
                addQueryString="TRUE"
                argumentsToBeExcludedFromQueryString="{0: 'id', 1: 'L', 2: 'cHash'}"
                section="comments"
            >&gt;&gt;</rn:pageBrowser.lastPage>
        </rn:pageBrowser>
    </ul>
</div>
```
Hinweis: Nicht alle fluid ViewHelper können ohne weiteres verwendet werden. Wenn z.B. ein Extbase Controller nötig ist,
wie bei Formularen, dann funktioniert das nicht out-of-the-box.
