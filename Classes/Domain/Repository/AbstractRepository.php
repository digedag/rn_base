<?php

namespace Sys25\RnBase\Domain\Repository;

use Sys25\RnBase\Backend\Utility\TCA;
use Sys25\RnBase\Domain\Collection\BaseCollection;
use Sys25\RnBase\Domain\Model\DomainModelInterface as DomainInterface;
use Sys25\RnBase\Domain\Model\RecordInterface;
use Sys25\RnBase\Search\SearchBase;
use Sys25\RnBase\Typo3Wrapper\Core\SingletonInterface;
use Sys25\RnBase\Utility\Environment;
use Sys25\RnBase\Utility\Strings;
use Sys25\RnBase\Utility\TYPO3;

/***************************************************************
 * Copyright notice
 *
 * (c) 2015-2023 René Nitzsche <rene@system25.de>
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
 * Abstracte Repository Klasse.
 *
 * @author Michael Wagner
 */
abstract class AbstractRepository implements SearchInterface, SingletonInterface
{
    /**
     * Liefert den Namen der Suchklasse.
     *
     * @return string
     */
    abstract protected function getSearchClass();

    /**
     * Liefert den Searcher.
     *
     * @return SearchBase
     */
    protected function getSearcher()
    {
        $searcher = SearchBase::getInstance($this->getSearchClass());
        if (!$searcher instanceof SearchBase) {
            throw new \Exception(get_class($this).'->getSearchClass() has to return a classname of class which extends Sys25\RnBase\Search\SearchBase!');
        }

        return $searcher;
    }

    /**
     * Returns the Collection class.
     * Can be overriden by the child repository class.
     *
     * @return string
     */
    protected function getCollectionClass()
    {
        return BaseCollection::class;
    }

    /**
     * Liefert die Model Klasse.
     *
     * @return string
     */
    protected function getWrapperClass()
    {
        return $this->getSearcher()->getWrapperClass();
    }

    /**
     * Return an instantiated dummy model without any content.
     *
     * This is used only to access several model info methods like
     * getTableName(), getColumnNames() etc.
     *
     * @return DomainInterface
     */
    public function getEmptyModel()
    {
        return \tx_rnbase::makeInstance($this->getWrapperClass());
    }

    /**
     * Holt einen bestimmten Datensatz aus dem Repo.
     *
     * @param int|array $rowOrUid
     *
     * @return DomainInterface|null
     */
    public function findByUid($rowOrUid)
    {
        /* @var $model DomainInterface */
        $model = \tx_rnbase::makeInstance(
            $this->getWrapperClass(),
            $rowOrUid
        );

        if ($model->isPersisted() && $model->isValid()) {
            return $model;
        }

        return null;
    }

    /**
     * Returns all items.
     *
     * @return BaseCollection
     */
    public function findAll()
    {
        return $this->search([], []);
    }

    /**
     * Search database.
     *
     * @param array $fields
     * @param array $options
     *
     * @return BaseCollection
     */
    public function search(array $fields, array $options)
    {
        $this->prepareFieldsAndOptions($fields, $options);

        $items = $this->getSearcher()->search($fields, $options);

        return $this->prepareItems($items, $options);
    }

    /**
     * Search database.
     *
     * @param array $fields
     * @param array $options
     *
     * @return DomainInterface
     */
    public function searchSingle(
        array $fields = [],
        array $options = []
    ) {
        $options['limit'] = 1;

        $items = $this->search($fields, $options);

        if (!empty($items[0])) {
            return $items[0];
        }

        return null;
    }

    /**
     * On default, return hidden and deleted fields in backend.
     *
     * @param array $fields
     * @param array $options
     */
    protected function prepareFieldsAndOptions(
        array &$fields,
        array &$options
    ) {
        // force collection usage by default!
        if (!isset($options['collection']) && $this->getCollectionClass()) {
            $options['collection'] = $this->getCollectionClass();
        }

        $this->handleEnableFieldsOptions($fields, $options);
        $this->handleLanguageOptions($fields, $options);
    }

    /**
     * On default, return hidden and deleted fields in backend.
     *
     * @param array $fields
     * @param array $options
     */
    protected function handleEnableFieldsOptions(
        array &$fields,
        array &$options
    ) {
        if (
            Environment::isBackend()
            && !isset($options['enablefieldsoff'])
            && !isset($options['enablefieldsbe'])
            && !isset($options['enablefieldsfe'])
        ) {
            $options['enablefieldsbe'] = true;
        }
    }

    /**
     * Setzt eventuelle Sprachparameter,
     * damit nur valide Daten für die aktuelle Sprache ausgelesen werden.
     *
     * @param array $fields
     * @param array $options
     */
    protected function handleLanguageOptions(
        array &$fields,
        array &$options
    ) {
        if (
            !isset($options['i18n'])
            && !isset($options['ignorei18n'])
            && !isset($options['enablefieldsoff'])
        ) {
            $tableName = $this->getEmptyModel()->getTableName();
            $languageField = TCA::getLanguageFieldForTable($tableName);
            // Die Sprache prüfen wir nur, wenn ein Sprachfeld gesetzt ist.
            if (!empty($languageField)) {
                $tsfe = TYPO3::getTSFE();
                $languages = [];
                if (isset($options['additionali18n'])) {
                    $languages = Strings::trimExplode(
                        ',',
                        $options['additionali18n'],
                        true
                    );
                }
                // for all languages
                $languages[] = '-1';
                // Wenn eine bestimmte Sprache gesetzt ist, laden wir diese ebenfalls.
                // andernfalls nutzen wir die default sprache
                if (is_object($tsfe) && \Sys25\RnBase\Utility\FrontendControllerUtility::getLanguageContentId($tsfe)) {
                    $languages[] = \Sys25\RnBase\Utility\FrontendControllerUtility::getLanguageContentId($tsfe);
                } else {
                    // default language
                    $languages[] = '0';
                }
                $options['i18n'] = implode(',', array_unique($languages, SORT_NUMERIC));
            }
        }
    }

    /**
     * Modifiziert die Ergebisliste.
     *
     * @param \Traversable|array $items
     * @param array             $options
     *
     * @return array[DomainInterface]
     */
    protected function prepareItems(
        $items,
        array $options
    ) {
        if (empty($items[0])) {
            return $items;
        }

        return $this->uniqueItems($items, $options);
    }

    /**
     * Entfernt alle doppelten Datensatze, wenn die Option distinct gesetzt ist.
     * Dabei werden die Sprachoverlays bevorzugt.
     *
     * @param \Traversable|array $items
     * @param array             $options
     *
     * @return array[RecordInterface]
     */
    protected function uniqueItems(
        $items,
        array $options
    ) {
        // uniqueue, if there are models and the distinct option
        if (
            $items[0] instanceof RecordInterface
            && isset($options['distinct'])
            && $options['distinct']
        ) {
            // seperate master and overlays
            $master = $overlay = [];
            /* @var $item RecordInterface */
            foreach ($items as $item) {
                $uid = (int) $item->getUid();
                $realUid = (int) $item->getProperty('uid');
                if ($uid === $realUid) {
                    $master[$uid] = $item;
                } else {
                    $overlay[$uid] = $item;
                }
            }
            // merge master and overlays and keep the order!
            $new = [];
            // uniquemode can be master or overlay!
            $preferOverlay = empty($options['uniquemode']) || 'master' !== strtolower($options['uniquemode']);
            foreach ($items as $item) {
                $uid = (int) $item->getUid();
                $new[$uid] = !empty($overlay[$uid]) && $preferOverlay ? $overlay[$uid] : $master[$uid];
            }
            $new = array_values($new);
            if (is_object($items)) {
                $items->exchangeArray($new);
            } else {
                $items = $new;
            }
        }

        return $items;
    }
}
