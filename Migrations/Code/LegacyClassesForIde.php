<?php

declare(strict_types=1);

namespace {
    exit('Access denied');
}

namespace Sys25\RnBase\Controller\Extbase {
    /**
     * @deprecated
     */
    trait CacheTagsTrait
    {
        use \Sys25\RnBase\ExtBaseFluid\Controller\CacheTagsTrait;
    }
}

namespace Sys25\RnBase\Fluid\View {
    /** @deprecated */
    class Action extends \Sys25\RnBase\ExtBaseFluid\View\Action
    {
    }

    /** @deprecated */
    class Factory extends \Sys25\RnBase\ExtBaseFluid\View\Factory
    {
    }

    /** @deprecated */
    class Standalone extends \Sys25\RnBase\ExtBaseFluid\View\Standalone
    {
    }
}

namespace Sys25\RnBase\Fluid\ViewHelper {
    /** @deprecated */
    class PageBrowserViewHelper extends \Sys25\RnBase\ExtBaseFluid\ViewHelper\PageBrowserViewHelper
    {
    }

    /** @deprecated */
    class TranslateViewHelper extends \Sys25\RnBase\ExtBaseFluid\ViewHelper\TranslateViewHelper
    {
    }
}

namespace Sys25\RnBase\Fluid\ViewHelper\Configurations {
    /** @deprecated */
    class GetViewHelper extends \Sys25\RnBase\ExtBaseFluid\ViewHelper\Configurations\GetViewHelper
    {
    }
}

namespace Sys25\RnBase\Fluid\ViewHelper\PageBrowser {
    /** @deprecated */
    class CurrentPageViewHelper extends \Sys25\RnBase\ExtBaseFluid\ViewHelper\PageBrowser\CurrentPageViewHelper
    {
    }

    /** @deprecated */
    class FirstPageViewHelper extends \Sys25\RnBase\ExtBaseFluid\ViewHelper\PageBrowser\FirstPageViewHelper
    {
    }

    /** @deprecated */
    class LastPageViewHelper extends \Sys25\RnBase\ExtBaseFluid\ViewHelper\PageBrowser\LastPageViewHelper
    {
    }

    /** @deprecated */
    class NextPageViewHelper extends \Sys25\RnBase\ExtBaseFluid\ViewHelper\PageBrowser\NextPageViewHelper
    {
    }

    /** @deprecated */
    class NormalPageViewHelper extends \Sys25\RnBase\ExtBaseFluid\ViewHelper\PageBrowser\NormalPageViewHelper
    {
    }

    /** @deprecated */
    class PageBaseViewHelper extends \Sys25\RnBase\ExtBaseFluid\ViewHelper\PageBrowser\PageBaseViewHelper
    {
    }

    /** @deprecated */
    class PrevPageViewHelper extends \Sys25\RnBase\ExtBaseFluid\ViewHelper\PageBrowser\PrevPageViewHelper
    {
    }
}

namespace Sys25\RnBase\Fluid\ViewHelper\Parameters {
    /** @deprecated */
    class PostOrGetViewHelper extends \Sys25\RnBase\ExtBaseFluid\ViewHelper\Parameters\PostOrGetViewHelper
    {
    }
}

namespace Sys25\RnBase\Search {
    if (false) {
        /** @deprecated */
        class Category
        {
        }

        /** @deprecated */
        class SearchCategory
        {
        }
    }
}

namespace Sys25\RnBase\Controller {
    /**
     * @deprecated
     */
    abstract class AbstractController extends \tx_rnbase_action_BaseIOC
    {
        protected function handleRequest(&$parameters, &$configurations, &$viewdata)
        {
            return $this->doRequest();
        }

        abstract protected function doRequest();

        protected function getViewClassName()
        {
            return \Sys25\RnBase\Fluid\View\Action::class;
        }

        protected function assignToView($name, $data)
        {
            $this->getViewData()->offsetSet($name, $data);

            return $this;
        }

        protected function getConfigurationValue($confId)
        {
            return $this->getConfigurations()->get($this->getConfId().$confId);
        }
    }
}
