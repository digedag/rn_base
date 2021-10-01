<?php

if (class_exists('Tx_Rnbase_Error_ErrorHandler')) {
    return;
}

if (class_exists('Sys25\RnBase\Typo3Wrapper\Core\CommandLineController')) {
    \class_alias(\Sys25\RnBase\Typo3Wrapper\Core\CommandLineController::class, 'Tx_Rnbase_CommandLine_Controller');
}

\class_alias(\Sys25\RnBase\Typo3Wrapper\Core\Error\ErrorHandler::class, 'Tx_Rnbase_Error_ErrorHandler');
\class_alias(\Sys25\RnBase\Typo3Wrapper\Core\Error\ProductionExceptionHandler::class, 'Tx_Rnbase_Error_ProductionExceptionHandler');
\class_alias(\Sys25\RnBase\Configuration\ConfigurationInterface::class, 'Tx_Rnbase_Configuration_ProcessorInterface');
\class_alias(\Sys25\RnBase\Exception\BaseException::class, 'Tx_Rnbase_Exception_Base');
\class_alias(\Sys25\RnBase\Exception\PageNotFound404::class, 'Tx_Rnbase_Exception_PageNotFound404');
\class_alias(\Sys25\RnBase\Exception\PageNotFound404::class, 'tx_rnbase_exception_ItemNotFound404');
\class_alias(\Sys25\RnBase\Utility\TYPO3::class, 'tx_rnbase_util_TYPO3');
\class_alias(\Sys25\RnBase\Database\Connection::class, 'Tx_Rnbase_Database_Connection');
\class_alias(\Sys25\RnBase\Frontend\Request\ParametersInterface::class, 'tx_rnbase_IParameters');
\class_alias(\Sys25\RnBase\Frontend\Request\Parameters::class, 'tx_rnbase_parameters');
\class_alias(\Sys25\RnBase\Typo3Wrapper\Core\SingletonInterface::class, 'Tx_Rnbase_Interface_Singleton');
\class_alias(\Sys25\RnBase\Utility\Typo3Classes::class, 'tx_rnbase_util_Typo3Classes');
\class_alias(\Sys25\RnBase\Typo3Wrapper\Core\Error\Exception::class, 'Tx_Rnbase_Error_Exception');
\class_alias(\Sys25\RnBase\Typo3Wrapper\RecordList\DatabaseRecordList::class, 'Tx_Rnbase_RecordList_DatabaseRecordList');
\class_alias(\Sys25\RnBase\Utility\Network::class, 'tx_rnbase_util_Network');
\class_alias(\Sys25\RnBase\Configuration\Processor::class, 'Tx_Rnbase_Configuration_Processor');
\class_alias(\Sys25\RnBase\Configuration\Processor::class, 'tx_rnbase_configurations');
\class_alias(\Sys25\RnBase\Domain\Model\MediaModel::class, 'tx_rnbase_model_media');
\class_alias(\Sys25\RnBase\Utility\Arrays::class, 'tx_rnbase_util_Arrays');
\class_alias(\Sys25\RnBase\Utility\Debug::class, 'tx_rnbase_util_Debug');
\class_alias(\Sys25\RnBase\Typo3Wrapper\Service\AuthenticationService::class, 'Tx_Rnbase_Service_Authentication');
\class_alias(\Sys25\RnBase\Typo3Wrapper\Service\AbstractService::class, 'Tx_Rnbase_Service_Base');
\class_alias(\Sys25\RnBase\Backend\Handler\SearchHandler::class, 'Tx_Rnbase_Backend_Handler_SearchHandler');
\class_alias(\Sys25\RnBase\Backend\Handler\DetailHandler::class, 'Tx_Rnbase_Backend_Handler_DetailHandler');
\class_alias(\Sys25\RnBase\Backend\Lister\AbstractLister::class, 'Tx_Rnbase_Backend_Lister_AbstractLister');
\class_alias(\Sys25\RnBase\Backend\Module\ModuleBase::class, 'Tx_Rnbase_Backend_Module_Base');
\class_alias(\Sys25\RnBase\Backend\Utility\TcaTool::class, 'Tx_Rnbase_Utility_TcaTool');
\class_alias(\Sys25\RnBase\Backend\Utility\TcaTool::class, 'Tx_Rnbase_Util_TCATool');
\class_alias(\Sys25\RnBase\Backend\Template\Override\DocumentTemplate::class, 'Tx_Rnbase_Backend_Template_Override_DocumentTemplate');
\class_alias(\Sys25\RnBase\Backend\Template\ModuleParts::class, 'Tx_Rnbase_Backend_Template_ModuleParts');
\class_alias(\Sys25\RnBase\Backend\Template\ModuleTemplate::class, 'Tx_Rnbase_Backend_Template_ModuleTemplate');
\class_alias(\Sys25\RnBase\Backend\Utility\BackendUtility::class, 'Tx_Rnbase_Backend_Utility');
\class_alias(\Sys25\RnBase\Backend\Utility\DecoratorUtility::class, 'Tx_Rnbase_Backend_Utility_DecoratorUtility');
\class_alias(\Sys25\RnBase\Backend\Utility\SearcherUtility::class, 'Tx_Rnbase_Backend_Utility_SearcherUtility');
\class_alias(\Sys25\RnBase\Backend\Form\Element\InputText::class, 'Tx_Rnbase_Backend_Form_Element_InputText');
\class_alias(\Sys25\RnBase\Backend\Form\FormBuilder::class, 'Tx_Rnbase_Backend_Form_FormBuilder');
\class_alias(\Sys25\RnBase\Backend\Form\ToolBox::class, 'Tx_Rnbase_Backend_Form_ToolBox');
\class_alias(\Sys25\RnBase\Backend\Utility\Icons::class, 'Tx_Rnbase_Backend_Utility_Icons');
\class_alias(\Sys25\RnBase\Backend\Utility\Tables::class, 'Tx_Rnbase_Backend_Utility_Tables');
\class_alias(\Sys25\RnBase\Backend\ModuleRunner::class, 'Tx_Rnbase_Backend_ModuleRunner');
\class_alias(\Sys25\RnBase\Utility\Logger::class, 'tx_rnbase_util_Logger');
\class_alias(\Sys25\RnBase\Search\SearchBase::class, 'tx_rnbase_util_SearchBase');
\class_alias(\Sys25\RnBase\Configuration\ConfigurableTrait::class, 'Tx_Rnbase_Configuration_ConfigurableTrait');
\class_alias(\Sys25\RnBase\Frontend\Filter\IFilterItem::class, 'tx_rnbase_IFilterItem');
\class_alias(\Sys25\RnBase\Frontend\Filter\FilterItem::class, 'tx_rnbase_filter_FilterItem');
\class_alias(\Sys25\RnBase\Utility\Composer::class, 'Tx_Rnbase_Utility_Composer');
\class_alias(\Sys25\RnBase\Utility\Strings::class, 'Tx_Rnbase_Utility_Strings');
\class_alias(\Sys25\RnBase\Utility\Strings::class, 'tx_rnbase_util_Strings');
\class_alias(\Sys25\RnBase\Utility\Math::class, 'tx_rnbase_util_Math');
\class_alias(\Sys25\RnBase\Utility\T3General::class, 'Tx_Rnbase_Utility_T3General');
\class_alias(\Sys25\RnBase\Utility\Misc::class, 'tx_rnbase_util_Misc');
\class_alias(\Sys25\RnBase\Domain\Collection\BaseCollection::class, 'Tx_Rnbase_Domain_Collection_Base');
\class_alias(\Sys25\RnBase\Domain\Model\DataInterface::class, 'Tx_Rnbase_Domain_Model_DataInterface');
\class_alias(\Sys25\RnBase\Domain\Model\DomainInterface::class, 'Tx_Rnbase_Domain_Model_DomainInterface');
\class_alias(\Sys25\RnBase\Domain\Model\DynamicTableInterface::class, 'Tx_Rnbase_Domain_Model_DynamicTableInterface');
\class_alias(\Sys25\RnBase\Domain\Model\RecordInterface::class, 'Tx_Rnbase_Domain_Model_RecordInterface');
\class_alias(\Sys25\RnBase\Domain\Model\DataModel::class, 'Tx_Rnbase_Domain_Model_Data');
\class_alias(\Sys25\RnBase\Domain\Model\BaseModel::class, 'Tx_Rnbase_Domain_Model_Base');
\class_alias(\Sys25\RnBase\Domain\Model\StorageTrait::class, 'Tx_Rnbase_Domain_Model_StorageTrait');
\class_alias(\Sys25\RnBase\Domain\Repository\SearchInterface::class, 'Tx_Rnbase_Domain_Repository_InterfaceSearch');
\class_alias(\Sys25\RnBase\Domain\Repository\PersistenceInterface::class, 'Tx_Rnbase_Domain_Repository_InterfacePersistence');
\class_alias(\Sys25\RnBase\Domain\Repository\AbstractRepository::class, 'Tx_Rnbase_Domain_Repository_AbstractRepository');
\class_alias(\Sys25\RnBase\Domain\Repository\AbstractRepository::class, 'Tx_Rnbase_Repository_AbstractRepository');
\class_alias(\Sys25\RnBase\Domain\Repository\PersistenceRepository::class, 'Tx_Rnbase_Domain_Repository_PersistenceRepository');
\class_alias(\Sys25\RnBase\Backend\Decorator\InterfaceDecorator::class, 'Tx_Rnbase_Backend_Decorator_InterfaceDecorator');
\class_alias(\Sys25\RnBase\Backend\Decorator\BaseDecorator::class, 'Tx_Rnbase_Backend_Decorator_BaseDecorator');
\class_alias(\Sys25\RnBase\Database\TreeQueryBuilder::class, 'Tx_Rnbase_Database_TreeQueryBuilder');
\class_alias(\Sys25\RnBase\Frontend\Marker\BaseMarker::class, 'tx_rnbase_util_BaseMarker');
\class_alias(\Sys25\RnBase\Frontend\Marker\SimpleMarker::class, 'tx_rnbase_util_SimpleMarker');
\class_alias(\Sys25\RnBase\Frontend\Marker\MarkerUtility::class, 'Tx_Rnbase_Frontend_Marker_Utility');
\class_alias(\Sys25\RnBase\Utility\CHashUtility::class, 'Tx_Rnbase_Utility_Cache');
\class_alias(\Sys25\RnBase\Utility\Crypt::class, 'Tx_Rnbase_Utility_Crypt');
\class_alias(\Sys25\RnBase\Utility\Email::class, 'Tx_Rnbase_Utility_Mail');
\class_alias(\Sys25\RnBase\Utility\TypoScript::class, 'Tx_Rnbase_Utility_TypoScript');
\class_alias(\Sys25\RnBase\Utility\WizIcon::class, 'Tx_Rnbase_Utility_WizIcon');
\class_alias(\Sys25\RnBase\Exception\ExceptionHandlerInterface::class, 'tx_rnbase_exception_IHandler');
\class_alias(\Sys25\RnBase\Exception\ExceptionHandler::class, 'tx_rnbase_exception_Handler');

\class_alias(\Sys25\RnBase\Search\System\CategorySearchUtility::class, 'Tx_Rnbase_Category_SearchUtility');
\class_alias(\Sys25\RnBase\Search\System\CategorySearchUtility::class, 'Sys25\RnBase\Search\Category\SearchUtility');
\class_alias(\Sys25\RnBase\Search\System\CategorySearch::class, 'Sys25\RnBase\Search\Category');
\class_alias(\Sys25\RnBase\Search\System\CategorySearch::class, 'Sys25\RnBase\Search\Category\Category');

if (false) {
    /** @deprecated this is an alias for NewClass */
    class Tx_Rnbase_Error_ErrorHandler
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Error_ProductionExceptionHandler
    {
    }
    /** @deprecated */
    interface Tx_Rnbase_Configuration_ProcessorInterface
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Exception_Base
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Exception_PageNotFound404
    {
    }
    /** @deprecated */
    class tx_rnbase_exception_ItemNotFound404
    {
    }
    /** @deprecated */
    class tx_rnbase_util_TYPO3
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Database_Connection
    {
    }
    /** @deprecated */
    interface tx_rnbase_IParameters
    {
    }
    /** @deprecated */
    class tx_rnbase_parameters
    {
    }
    /** @deprecated */
    interface Tx_Rnbase_Interface_Singleton
    {
    }
    /** @deprecated */
    class Tx_Rnbase_CommandLine_Controller
    {
    }
    /** @deprecated */
    class tx_rnbase_util_Typo3Classes
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Error_Exception
    {
    }
    /** @deprecated */
    class Tx_Rnbase_RecordList_DatabaseRecordList
    {
    }
    /** @deprecated */
    class tx_rnbase_util_Network
    {
    }
    /** @deprecated */
    class tx_rnbase_configurations
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Configuration_Processor
    {
    }
    /** @deprecated */
    class tx_rnbase_model_media
    {
    }
    /** @deprecated */
    class tx_rnbase_util_Arrays
    {
    }
    /** @deprecated */
    class tx_rnbase_util_Debug
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Service_Authentication
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Service_Base
    {
    }
    /** @deprecated */
    abstract class Tx_Rnbase_Backend_Handler_SearchHandler
    {
    }
    /** @deprecated */
    abstract class Tx_Rnbase_Backend_Handler_DetailHandler
    {
    }
    /** @deprecated */
    abstract class Tx_Rnbase_Backend_Lister_AbstractLister
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Backend_Module_Base
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Utility_TcaTool
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Util_TCATool
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Backend_Template_Override_DocumentTemplate
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Backend_Template_ModuleParts
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Backend_Template_ModuleTemplate
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Backend_Utility
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Backend_Utility_DecoratorUtility
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Backend_Utility_SearcherUtility
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Backend_Form_Element_InputText
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Backend_Form_FormBuilder
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Backend_Form_ToolBox
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Backend_Utility_Icons
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Backend_Utility_Tables
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Backend_ModuleRunner
    {
    }
    /** @deprecated */
    class tx_rnbase_util_Logger
    {
    }
    /** @deprecated */
    abstract class tx_rnbase_util_SearchBase
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Category_SearchUtility
    {
    }
    /** @deprecated */
    trait Tx_Rnbase_Configuration_ConfigurableTrait
    {
    }
    /** @deprecated */
    interface tx_rnbase_IFilterItem
    {
    }
    /** @deprecated */
    class tx_rnbase_filter_FilterItem
    {
    }
    /** @deprecated */
    final class Tx_Rnbase_Utility_Composer
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Utility_Strings
    {
    }
    /** @deprecated */
    class tx_rnbase_util_Strings
    {
    }
    /** @deprecated */
    class tx_rnbase_util_Math
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Utility_T3General
    {
    }
    /** @deprecated */
    class tx_rnbase_util_Misc
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Domain_Collection_Base
    {
    }
    /** @deprecated */
    interface Tx_Rnbase_Domain_Model_DataInterface
    {
    }
    /** @deprecated */
    interface Tx_Rnbase_Domain_Model_DomainInterface
    {
    }
    /** @deprecated */
    interface Tx_Rnbase_Domain_Model_DynamicTableInterface
    {
    }
    /** @deprecated */
    interface Tx_Rnbase_Domain_Model_RecordInterface
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Domain_Model_Data
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Domain_Model_Base
    {
    }
    /** @deprecated */
    trait Tx_Rnbase_Domain_Model_StorageTrait
    {
    }
    /** @deprecated */
    interface Tx_Rnbase_Domain_Repository_InterfaceSearch
    {
    }
    /** @deprecated */
    interface Tx_Rnbase_Domain_Repository_InterfacePersistence
    {
    }
    /** @deprecated */
    abstract class Tx_Rnbase_Domain_Repository_AbstractRepository
    {
    }
    /** @deprecated */
    abstract class Tx_Rnbase_Domain_Repository_PersistenceRepository
    {
    }
    /** @deprecated */
    abstract class Tx_Rnbase_Repository_AbstractRepository
    {
    }
    /** @deprecated */
    interface Tx_Rnbase_Backend_Decorator_InterfaceDecorator
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Backend_Decorator_BaseDecorator
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Database_TreeQueryBuilder
    {
    }
    /** @deprecated */
    class tx_rnbase_util_BaseMarker
    {
    }
    /** @deprecated */
    class tx_rnbase_util_SimpleMarker
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Frontend_Marker_Utility
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Utility_Cache
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Utility_Crypt
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Utility_Mail
    {
    }
    /** @deprecated */
    class Tx_Rnbase_Utility_TypoScript
    {
    }
    /** @deprecated */
    abstract class Tx_Rnbase_Utility_WizIcon
    {
    }
    /** @deprecated */
    interface tx_rnbase_exception_IHandler
    {
    }
    /** @deprecated */
    class tx_rnbase_exception_Handler
    {
    }
}

/** @deprecated */
class tx_rnbase_model_data extends Sys25\RnBase\Domain\Model\DataModel implements Tx_Rnbase_Domain_Model_DataInterface
{
    public $record = [];
}

/**
 * @deprecated
 */
class tx_rnbase_model_base extends Sys25\RnBase\Domain\Model\BaseModel implements Tx_Rnbase_Domain_Model_RecordInterface, Tx_Rnbase_Domain_Model_DataInterface
{
    public $uid;
    public $record;

    /**
     * @deprecated
     */
    public function getColumnWrapped($formatter, $columnName, $baseConfId, $colConfId = '')
    {
        $colConfId = (strlen($colConfId)) ? $colConfId : $columnName.'.';

        return $formatter->wrap($this->record[$columnName], $baseConfId.$colConfId);
    }
}
