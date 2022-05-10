<?php

namespace Sys25\RnBase\Frontend\Filter;

use Sys25\RnBase\Configuration\ConfigurationInterface;
use Sys25\RnBase\Frontend\Filter\Utility\Category;
use Sys25\RnBase\Frontend\Request\ParametersInterface;
use Sys25\RnBase\Frontend\Request\RequestInterface;
use Sys25\RnBase\Search\SearchBase;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2019 Rene Nitzsche
 *  Contact: rene@system25.de
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

class BaseFilter implements FilterInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configurations;

    /**
     * @var ParametersInterface
     */
    private $parameters;

    /**
     * @var string
     */
    private $confId;

    protected $request;

    /**
     * @var array
     */
    protected $filterItems;

    /**
     * @var bool|null wenn auf null steht wird der return Wert von initFilter()
     *                in init() zurück gegeben. Ansonsten der Wert von $doSearch, dieser hat also Vorrang.
     */
    protected $doSearch = null;

    /**
     * @param RequestInterface $request
     * @param string           $confId
     */
    public function __construct($request, $confId)
    {
        $this->configurations = $request->getConfigurations();
        $this->parameters = $request->getParameters();
        $this->confId = $confId;
        $this->request = $request;
    }

    /**
     * Liefert das Config-Objekt.
     *
     * @return ConfigurationInterface
     */
    protected function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * Liefert die Parameter.
     *
     * @return ParametersInterface
     */
    protected function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Liefert die Basis-ConfigId. Diese sollte immer mit einem Punkt enden: myview.
     *
     * @return string
     */
    protected function getConfId()
    {
        return $this->confId;
    }

    /**
     * Abgeleitete Filter können diese Methode überschreiben und zusätzlich Filter setzen.
     *
     * @param array $fields
     * @param array $options
     *
     * @return bool if FALSE no search should be done
     */
    public function init(&$fields, &$options)
    {
        SearchBase::setConfigFields($fields, $this->getConfigurations(), $this->getConfId().'fields.');
        SearchBase::setConfigOptions($options, $this->getConfigurations(), $this->getConfId().'options.');

        $this->doSearch = $this->getCategoryFilterUtility()->handleSysCategoryFilter($fields, $this->doSearch);

        return $this->shouldSearchBeDone(
            $this->initFilter($fields, $options, $this->request)
        );
    }

    /**
     * @return Category
     */
    protected function getCategoryFilterUtility()
    {
        return \tx_rnbase::makeInstance(Category::class, $this->getConfigurations(), $this->getConfId());
    }

    /**
     * @param bool $fallback
     *
     * @return bool
     */
    protected function shouldSearchBeDone($fallback)
    {
        $doSearch = $this->doSearch;
        if (null === $doSearch) {
            $doSearch = $fallback;
        }

        return $doSearch;
    }

    /**
     * {@inheritdoc}
     */
    public function hideResult()
    {
        return false;
    }

    /**
     * Abgeleitete Filter können diese Methode überschreiben und zusätzlich Filter setzen.
     *
     * @param array            $fields
     * @param array            $options
     * @param RequestInterface $request
     *
     * @return bool
     */
    protected function initFilter(&$fields, &$options, RequestInterface $request)
    {
        return true;
    }

    /**
     * Hilfsmethode zum Setzen von Filtern aus den Parametern. Ein schon gesetzter Wert im Field-Array
     * wird nicht überschrieben. Die.
     *
     * @param string                                             $idstr
     * @param array                                              $fields
     * @param \Sys25\RnBase\Frontend\Request\Parameters          $parameters
     * @param \Sys25\RnBase\Configuration\ConfigurationInterface $configurations
     * @param string                                             $operator       Operator-Konstante
     */
    public function setField($idstr, &$fields, $parameters, $configurations, $operator = OP_LIKE)
    {
        // Wenn der Wert schon gesetzt ist, wird er nicht überschrieben
        if (!isset($fields[$idstr][$operator]) && $parameters->offsetExists($idstr) && $parameters->offsetGet($idstr)) {
            $fields[$idstr][$operator] = $parameters->offsetGet($idstr);
            // Parameter als KeepVar merken TODO: Ist das noch notwendig
            $configurations->addKeepVar($configurations->createParamName($idstr), $fields[$idstr]);
        }
    }

    /**
     * @param IFilterItem $item
     */
    public function addFilterItem(IFilterItem $item)
    {
        $this->filterItems[] = $item;
    }

    /**
     * Returns all filter items set.
     *
     * @return IFilterItem[]
     */
    public function getFilterItems()
    {
        return $this->filterItems;
    }

    /**
     * Fabrikmethode zur Erstellung von Filtern. Die Klasse des Filters kann entweder direkt angegeben werden oder
     * wird über die Config gelesen. Klappt beides nicht, wird der Standardfilter geliefert.
     *
     * @param RequestInterface $request
     * @param string           $confId
     * @param string           $filterClass Klassenname des Filters
     *
     * @return FilterInterface
     */
    public static function createFilter(RequestInterface $request, $confId, $filterClass = '')
    {
        $configurations = $request->getConfigurations();

        $filterClass = ($filterClass) ? $filterClass : $configurations->get($confId.'class');
        $filterClass = ($filterClass) ? $filterClass : $configurations->get($confId.'filter');
        $filterClass = ($filterClass) ? $filterClass : self::class;
        $filter = \tx_rnbase::makeInstance($filterClass, $request, $confId);
        $request->getViewContext()->offsetSet('filter', $filter);

        return $filter;
    }

    /**
     * Whether or not a charbrowser should be ignored.
     *
     * @return bool
     */
    public function isSpecialSearch()
    {
        // In den meisten Projekten liegen die Nutzerdaten im Array inputData
        return is_array($this->inputData) && count($this->inputData);
    }
}
