<?php

declare(strict_types=1);

namespace {
    exit('Access denied');
}

namespace {
    /** @deprecated */
    interface tx_rnbase_cache_ICache extends \Sys25\RnBase\Cache\CacheInterface
    {
    }

    /** @deprecated */
    class Tx_Rnbase_Error_ErrorHandler extends \Sys25\RnBase\Typo3Wrapper\Core\Error\ErrorHandler
    {
    }

    /** @deprecated */
    class Tx_Rnbase_Error_ProductionExceptionHandler extends \Sys25\RnBase\Typo3Wrapper\Core\Error\ProductionExceptionHandler
    {
    }

    /** @deprecated */
    interface Tx_Rnbase_Configuration_ProcessorInterface extends \Sys25\RnBase\Configuration\ConfigurationInterface
    {
    }

    /** @deprecated */
    class Tx_Rnbase_Exception_Base extends \Sys25\RnBase\Exception\BaseException
    {
    }

    /** @deprecated */
    class Tx_Rnbase_Exception_PageNotFound404 extends \Sys25\RnBase\Exception\PageNotFound404
    {
    }

    /** @deprecated */
    class tx_rnbase_util_TYPO3 extends \Sys25\RnBase\Utility\TYPO3
    {
    }

    /** @deprecated */
    class Tx_Rnbase_Database_Connection extends \Sys25\RnBase\Database\Connection
    {
    }

    /** @deprecated */
    interface tx_rnbase_IParameters extends \Sys25\RnBase\Frontend\Request\ParametersInterface
    {
    }

    /** @deprecated */
    class tx_rnbase_parameters extends \Sys25\RnBase\Frontend\Request\Parameters
    {
    }

    /** @deprecated */
    interface Tx_Rnbase_Interface_Singleton extends \Sys25\RnBase\Typo3Wrapper\Core\SingletonInterface
    {
    }

    /** @deprecated */
    class Tx_Rnbase_CommandLine_Controller extends \Sys25\RnBase\Typo3Wrapper\Core\CommandLineController
    {
    }

    /** @deprecated */
    class tx_rnbase_util_Typo3Classes extends \Sys25\RnBase\Utility\Typo3Classes
    {
    }

    /** @deprecated */
    class tx_rnbase_util_Extensions extends \Sys25\RnBase\Utility\Extensions
    {
    }

    /** @deprecated */
    class Tx_Rnbase_Error_Exception extends \Sys25\RnBase\Typo3Wrapper\Core\Error\Exception
    {
    }

    /** @deprecated */
    class Tx_Rnbase_RecordList_DatabaseRecordList extends \Sys25\RnBase\Typo3Wrapper\RecordList\DatabaseRecordList
    {
    }

    /** @deprecated */
    class tx_rnbase_util_Network extends \Sys25\RnBase\Utility\Network
    {
    }

    /** @deprecated */
    class tx_rnbase_configurations extends \Sys25\RnBase\Configuration\Processor
    {
    }

    /** @deprecated */
    class Tx_Rnbase_Configuration_Processor extends \Sys25\RnBase\Configuration\Processor
    {
    }

    /** @deprecated */
    class tx_rnbase_model_media extends \Sys25\RnBase\Domain\Model\MediaModel
    {
    }

    /** @deprecated */
    class tx_rnbase_util_Arrays extends \Sys25\RnBase\Utility\Arrays
    {
    }

    /** @deprecated */
    class tx_rnbase_util_Debug extends \Sys25\RnBase\Utility\Debug
    {
    }

    /** @deprecated */
    class Tx_Rnbase_Service_Authentication extends \Sys25\RnBase\Typo3Wrapper\Service\AuthenticationService
    {
    }

    /** @deprecated */
    class Tx_Rnbase_Service_Base extends \Sys25\RnBase\Typo3Wrapper\Service\AbstractService
    {
    }

    /** @deprecated */
    interface Tx_Rnbase_Backend_Decorator_InterfaceDecorator extends \Sys25\RnBase\Backend\Decorator\InterfaceDecorator
    {
    }
    /** @deprecated */
    interface tx_rnbase_mod_IDecorator extends \Sys25\RnBase\Backend\Decorator\InterfaceDecorator
    {
    }

    /** @deprecated */
    class Tx_Rnbase_Backend_Decorator_BaseDecorator extends \Sys25\RnBase\Backend\Decorator\BaseDecorator
    {
    }

    /** @deprecated */
    abstract class Tx_Rnbase_Backend_Handler_SearchHandler extends \Sys25\RnBase\Backend\Handler\SearchHandler
    {
    }

    /** @deprecated */
    abstract class Tx_Rnbase_Backend_Handler_DetailHandler extends \Sys25\RnBase\Backend\Handler\DetailHandler
    {
    }

    /** @deprecated */
    abstract class Tx_Rnbase_Backend_Lister_AbstractLister extends \Sys25\RnBase\Backend\Lister\AbstractLister
    {
    }
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
    /** @deprecated */
    class Category
    {
    }

    /** @deprecated */
    class SearchCategory
    {
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
