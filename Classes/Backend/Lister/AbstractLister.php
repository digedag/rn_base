<?php

namespace Sys25\RnBase\Backend\Lister;

use Sys25\RnBase\Backend\Decorator\BaseDecorator;
use Sys25\RnBase\Backend\Decorator\InterfaceDecorator;
use Sys25\RnBase\Backend\Utility\BackendUtility;
use Sys25\RnBase\Backend\Utility\DecoratorUtility;
use Sys25\RnBase\Backend\Utility\SearcherUtility;
use Sys25\RnBase\Backend\Utility\Tables;
use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Domain\Model\DataModel;
use Sys25\RnBase\Domain\Repository\AbstractRepository;
use Sys25\RnBase\Frontend\Request\Parameters;
use Sys25\RnBase\Utility\Strings;
use tx_rnbase;
use tx_rnbase_mod_BaseModule;
use tx_rnbase_mod_IModule;
use tx_rnbase_mod_Util;
use UnexpectedValueException;

/***************************************************************
 * Copyright notice
 *
 * (c) 2016-2021 René Nitzsche <rene@system25.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Abstract Lister.
 *
 * $lister = tx_rnbase::makeInstance('Sys25\RnBase\Backend\Lister\AbstractLister', $mod);
 * $markerArray = array_merge(
 *  $markerArray,
 *  $lister->renderListMarkers()
 * );
 *
 * @author Michael Wagner
 */
abstract class AbstractLister
{
    /**
     * The storage for this lister.
     *
     * @var DataModel
     */
    private $storage = null;

    /**
     * Returns the repository.
     *
     * @return \Sys25\RnBase\Domain\Repository\SearchInterface
     */
    abstract protected function getRepository();

    /**
     * The unique id for the lister.
     * It is recommended the childclass extends this method!
     *
     * @return string
     */
    protected function getListerId()
    {
        $confId = str_replace('\\', '_', get_class($this));
        $confId = Strings::underscoredToLowerCamelCase($confId);

        return $confId;
    }

    /**
     * Constructor.
     *
     * @param tx_rnbase_mod_BaseModule $module
     * @param array|DataModel $options
     */
    public function __construct(
        tx_rnbase_mod_BaseModule $module,
        $options = []
    ) {
        $this->storage = DataModel::getInstance(
            [
                'module' => $module,
                'options' => $options,
                'filter' => [],
            ]
        );

        $this->init();

        // set the baseTable for this lister, if not set.
        // required for some table operations, eq language column!
        if (!$this->getOptions()->hasBaseTableName()) {
            if ($this->getRepository() instanceof AbstractRepository) {
                $this->getOptions()->setBaseTableName(
                    $this->getRepository()->getEmptyModel()->getTableName()
                );
            }
        }
    }

    /**
     * Can be overridden to initialize the lister.
     */
    protected function init()
    {
    }

    /**
     * Returns the module.
     *
     * @return DataModel
     */
    protected function getStorage()
    {
        return $this->storage;
    }

    /**
     * Returns the module.
     *
     * @return tx_rnbase_mod_IModule
     */
    protected function getModule()
    {
        return $this->getStorage()->getModule();
    }

    /**
     * Returns the configurations.
     *
     * @return ConfigurationInterface
     */
    protected function getConfigurations()
    {
        return $this->getModule()->getConfigurations();
    }

    /**
     * Returns an instance of form tool from the module.
     *
     * @return \tx_rnbase_util_FormTool
     */
    protected function getFormTool()
    {
        return $this->getModule()->getFormTool();
    }

    /**
     * Returns the options or a specific property of the options.
     *
     * @return DataModel|mixed
     */
    protected function getOptions()
    {
        return $this->getStorage()->getOptions();
    }

    /**
     * The Pager instance for the current listing.
     *
     * @return \tx_rnbase_util_BEPager
     */
    protected function getPager()
    {
        if (!$this->getStorage()->hasPager()) {
            $this->getStorage()->setPager(
                tx_rnbase::makeInstance(
                    'tx_rnbase_util_BEPager',
                    $this->getListerId().'Pager',
                    $this->getModule()->getName(),
                    $this->getOptions()->getPid()
                )
            );
        }

        return $this->getStorage()->getPager();
    }

    /**
     * The filters the current listing.
     *
     * @return DataModel
     */
    protected function getFilter()
    {
        return $this->getStorage()->getFilter();
    }

    /**
     * The decorator instace.
     *
     * @return InterfaceDecorator
     */
    protected function getDecorator()
    {
        if (!$this->getStorage()->hasDecorator()) {
            $decorator = tx_rnbase::makeInstance(
                $this->getDecoratorClass(),
                $this->getModule(),
                $this->getOptions()
            );
            if (!$decorator instanceof InterfaceDecorator) {
                throw new UnexpectedValueException('The Decorator has to be an instance of'.' "Sys25\RnBase\Backend\Decorator\InterfaceDecorator"'.' but "'.get_class($decorator).'" given.');
            }
            $this->getStorage()->setDecorator($decorator);
        }

        return $this->getStorage()->getDecorator();
    }

    /**
     * The classname of the decorator to use.
     * Has to implement the interface "Sys25\RnBase\Backend\Decorator\InterfaceDecorator".
     *
     * @return string
     */
    protected function getDecoratorClass()
    {
        if ($this->getOptions()->hasDecoratorClass()) {
            return $this->getOptions()->getDecoratorClass();
        }

        return BaseDecorator::class;
    }

    /**
     * Returns the columns of the listing.
     * Dont override this in child class, use addDecoratorColumns instead!
     *
     * @internal
     *
     * @return array
     */
    protected function getDecoratorColumns()
    {
        if (!$this->getStorage()->hasDecoratorColumnsr()) {
            $columns = [];
            $this->addDecoratorColumns($columns);
            $this->getStorage()->setDecoratorColumns($columns);
        }

        return $this->getStorage()->getDecoratorColumns();
    }

    /**
     * Adds the columns of the listing.
     * A childclass can extend this method to add its own columns.
     *
     * @param array $columns
     *
     * @return AbstractLister
     */
    protected function addDecoratorColumns(array &$columns)
    {
        (
            $this->getDecoratorUtility()
            ->addDecoratorColumnLabel($columns)
            ->addDecoratorColumnLanguage($columns)
            ->addDecoratorColumnActions($columns)
        );

        return $this;
    }

    /**
     * Returns the decorator utility instance of the listing.
     *
     * @return DecoratorUtility
     */
    protected function getDecoratorUtility()
    {
        if (!$this->getStorage()->hasDecoratorUtility()) {
            $this->getStorage()->setDecoratorUtility(
                DecoratorUtility::getInstance(
                    $this->getDecorator(),
                    $this->getOptions()
                )
            );
        }

        return $this->getStorage()->getDecoratorUtility();
    }

    /**
     * Renders the List into the template.
     * It is we recommend to use renderListMarkers
     * and render the markery by your self, for performance reasons.
     *
     * @param string $template
     *
     * @return string
     */
    public function renderTemplate(
        $template
    ) {
        tx_rnbase::load('tx_rnbase_util_Templates');

        return Templates::substituteMarkerArrayCached(
            $template,
            $this->renderListMarkers()
        );
    }

    /**
     * Renders the form and list and returns the filled marker array.
     *
     * @return array
     */
    public function renderListMarkers()
    {
        $markerArray = [];

        $markerArray['###SEARCHFORM###'] = $this->renderSearchForm();

        $markerArray['###LIST###'] = $this->renderResultList();
        $markerArray['###SIZE###'] = $this->getListCount();
        $markerArray['###PAGER###'] = $this->renderPager();

        return $markerArray;
    }

    /**
     * Renders the Search Form.
     *
     * @return string
     */
    public function renderSearchForm()
    {
        $data = $this->getSearchFormData();
        $out = '';

        if (!empty($data)) {
            foreach ($data as $label => $filter) {
                if (isset($filter['label'])) {
                    $label = $filter['label'];
                    unset($filter['label']);
                }
                $out .= sprintf(
                    '<tr><td>%s</td><td>%s</td></tr>',
                    $label,
                    implode(' ', $filter)
                );
            }
            $out = '<table class="filters">'.$out.'</table>';
        }

        return $out;
    }

    /**
     * Renders the result table html.
     *
     * @return string
     */
    public function renderResultList()
    {
        $items = $this->getResultList();

        if (empty($items)) {
            return $this->getConfigurations()->getLL(
                'label_no_'.strtolower($this->getListerId()).'_found'
            );
        }

        $columns = $this->getDecoratorColumns();

        /* @var $tables Tables */
        $tables = tx_rnbase::makeInstance(Tables::class);
        list($tableData, $tableLayout) = $tables->prepareTable(
            $items,
            $columns,
            $this->getFormTool(),
            $this->getOptions()
        );

        return $tables->buildTable($tableData, $tableLayout);
    }

    /**
     * Renders the Pager.
     *
     * @return string
     */
    public function renderPager()
    {
        // render the pager, if there are items
        if (!$this->getListCount()) {
            return '';
        }

        $pagerData = $this->getPager()->render();

        return sprintf(
            '<div class="pager">%s - %s</div>',
            $pagerData['limits'],
            $pagerData['pages']
        );
    }

    /**
     * Returns the count of the complete list.
     *
     * @return int
     */
    public function getListCount()
    {
        if (!$this->getStorage()->hasListCount()) {
            list($fields, $options) = $this->getFieldsAndOptions();
            $options['count'] = 1;
            $this->getStorage()->setListCount(
                (int) $this->getRepository()->search($fields, $options)
            );
        }

        return $this->getStorage()->getListCount();
    }

    /**
     * Returns the list with the filtered rows.
     *
     * @return array|\Traversable
     */
    protected function getResultList()
    {
        list($fields, $options) = $this->getFieldsAndOptions();

        $pager = $this->getPager();
        $pager->setListSize($this->getListCount());
        $pager->setOptions($options);

        return $this->getSearcherUtility()->performSearch(
            $this->getRepository(),
            $fields,
            $options
        );
    }

    /**
     * Returns the searcher util.
     *
     * @return SearcherUtility
     */
    protected function getSearcherUtility()
    {
        return SearcherUtility::getInstance($this->getOptions());
    }

    /**
     * Creates the fields and options array for the search.
     *
     * @return array fields and options
     */
    protected function getFieldsAndOptions()
    {
        if ($this->getStorage()->hasFieldsAndOptions()) {
            return $this->getStorage()->getFieldsAndOptions();
        }

        $filter = $this->initFilter()->getFilter();

        $fields = $options = [];

        $options['distinct'] = 1;

        // build the free text search
        if ($filter->hasSearchword()) {
            $fields[SEARCH_FIELD_JOINED][] = [
                'value' => trim($filter->getSearchword()),
                'cols' => $this->getSearchColumns(),
                'operator' => OP_LIKE,
            ];
        }

        // check the disabled filter
        if ($filter->hasDisabled()) {
            if ($filter->getDisabled()) {
                $options['enablefieldsbe'] = 1;
            } else {
                $options['enablefieldsfe'] = 1;
            }
        }

        $this->prepareSorting($options);
        $this->prepareFieldsAndOptions($fields, $options);

        $this->getStorage()->setFieldsAndOptions([$fields, $options]);

        return [$fields, $options];
    }

    /**
     * Preper sorting of columns.
     *
     * @param array $options
     */
    protected function prepareSorting(
        array &$options
    ) {
        $sortField = Parameters::getPostOrGetParameter('sortField');
        $sortRev = Parameters::getPostOrGetParameter('sortRev');

        if (!empty($sortField)) {
            $cols = $this->getDecoratorColumns();

            if (!isset($cols[$sortField]['sortable'])) {
                return;
            }

            // das Label in die notwendige SQL-Anweisung umwandeln. Normalerweise ein Spaltenname.
            $sortCol = $cols[$sortField]['sortable'];
            // Wenn am Ende ein Punkt steht, muss die Spalte zusammengefügt werden.
            $sortCol = '.' === substr($sortCol, -1) ? $sortCol.$sortField : $sortCol;
            $options['orderby'][$sortCol] = ('asc' == strtolower($sortRev) ? 'asc' : 'desc');
        }
    }

    /**
     * Prepares the fields and options.
     * Childclasses should override this method to extend the filters!
     *
     * @param array $fields
     * @param array $options
     */
    protected function prepareFieldsAndOptions(
        array &$fields,
        array &$options
    ) {
    }

    /**
     * Returns the currend module value from get or session
     * and stores the current get to session.
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function getModuleValue($key)
    {
        return tx_rnbase_mod_Util::getModuleValue(
            $key,
            $this->getModule(),
            [
                'changed' => Parameters::getPostOrGetParameter('SET'),
            ]
        );
    }

    /**
     * Initializes the filter array.
     *
     * @return AbstractLister
     */
    public function initFilter()
    {
        $filters = $this->getFilter();

        // filter is allready initialized
        if (!$filters->isEmpty()) {
            return $this;
        }

        $filters->setProperty(
            'searchword',
            $this->getModuleValue($this->getListerId().'Searchword')
        );
        $filters->setProperty(
            'disabled',
            $this->getModuleValue($this->getListerId().'Disabled')
        );

        return $this;
    }

    /**
     * Returns the formdata and stores the filters.
     *
     * @return array
     */
    protected function getSearchFormData()
    {
        // use the storage, so this method can called multible!
        if ($this->getStorage()->hasSearchFormData()) {
            $this->getStorage()->getSearchFormData();
        }

        $filter = $this->initFilter()->getFilter();
        $data = [];

        if ($this->getSearchColumns()) {
            $data['searchword'] = [
                'field' => $this->getFormTool()->createTxtInput(
                    'SET['.$this->getListerId().'Searchword]',
                    $filter->getProperty('searchword'),
                    10
                ),
                'label' => '###LABEL_SEARCH###',
            ];
        }

        $data['disabled'] = [
            'field' => BackendUtility::getFuncMenu(
                $this->getOptions()->getPid(),
                'SET['.$this->getListerId().'Disabled]',
                $filter->getProperty('disabled'),
                [
                    0 => $this->getConfigurations()->getLL('label_select_hide_hidden'),
                    1 => $this->getConfigurations()->getLL('label_select_show_hidden'),
                ]
            ),
            'label' => '###LABEL_HIDDEN###',
        ];

        $data['updatebutton'] = [
            'field' => $this->getFormTool()->createSubmit(
                $this->getListerId().'Search',
                '###LABEL_BTN_SEARCH###'
            ),
            'label' => '',
        ];

        $this->getStorage()->setSearchFormData($data);

        return $data;
    }

    /**
     * Returns the Fields for the free text search.
     *
     * @return array
     */
    protected function getSearchColumns()
    {
        if ($this->getOptions()->hasSearchColumns()) {
            $columns = $this->getOptions()->getSearchColumns();
            if ($columns instanceof DataModel) {
                $columns = $columns->toArray();
            }
        }

        if (!is_array($columns)) {
            $columns = [
                'uid',
            ];
        }

        return $columns;
    }
}
