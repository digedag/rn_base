<?php

use Sys25\RnBase\Utility\TYPO3;
use TYPO3\CMS\Core\Resource\FileReference;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013-2016 Rene Nitzsche
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

define('DEFAULT_LOCAL_FIELD', '_LOCALIZED_UID');

tx_rnbase::load('Tx_Rnbase_Backend_Utility');
tx_rnbase::load('tx_rnbase_util_Strings');

/**
 * Contains utility functions for FAL.
 */
class tx_rnbase_util_TSFAL
{
    /**
     * Typoscript USER function for rendering DAM images.
     * This is a minimal Setup:
     * <pre>
     * yourObject.imagecol = USER
     * yourObject.imagecol {
     *   userFunc=tx_rnbase_util_TSFAL->printImages
     *   includeLibs = EXT:rn_base/util/class.tx_rnbase_util_TSFAL.php
     *   refField=imagecol
     *   refTable=tx_yourextkey_tablename
     *   template = EXT:rn_base/res/simplegallery.html
     *   # media is the fal reference record
     *   media {
     *     # field file contains the complete image path
     *     file = IMAGE
     *     file.file.import.field = file
     *   }
     *   # Optional setting for limit
     *   # limit = 1
     * }
     * </pre>
     * There are three additional fields in media record: file, file1 and thumbnail containing the complete
     * image path.
     * The output is rendered via HTML template with ListBuilder. Have a look at EXT:rn_base/res/simplegallery.html
     * Possible Typoscript options:
     * refField: DAM reference field of the media records (defined in TCA and used to locate the record in MM-Table)
     * refTable: should be the tablename where the DAM record is referenced to
     * template: Full path to HTML template file.
     * media: Formatting options of the DAM record. Have a look at tx_dam to find all column names
     * limit: Limits the number of medias
     * offset: Start media output with an offset
     * forcedIdField: force another reference column (other than UID or _LOCALIZED_UID).
     *
     * @param string $content
     * @param array  $tsConf
     *
     * @return string
     */
    public function printImages($content, $tsConf)
    {
        tx_rnbase::load('tx_rnbase_util_Templates');
        $conf = $this->createConf($tsConf);
        $file = $conf->get('template');
        $file = $file ? $file : 'EXT:rn_base/res/simplegallery.html';
        $subpartName = $conf->get('subpartName');
        $subpartName = $subpartName ? $subpartName : '###DAM_IMAGES###';
        $templateCode = tx_rnbase_util_Templates::getSubpartFromFile($file, $subpartName);

        if (!$templateCode) {
            return '<!-- NO TEMPLATE OR SUBPART '.$subpartName.' FOUND -->';
        }

        // Is there a customized language field configured
        $langField = DEFAULT_LOCAL_FIELD;
        $locUid = $conf->getCObj()->data[$langField]; // Save original uid
        if ($conf->get('forcedIdField')) {
            $langField = $conf->get('forcedIdField');
            // Copy localized UID
            $conf->getCObj()->data[DEFAULT_LOCAL_FIELD] = $conf->getCObj()->data[$langField];
        }
        // Check if there is a valid uid given.
        $parentUid = intval($conf->getCObj()->data[DEFAULT_LOCAL_FIELD] ? $conf->getCObj()->data[DEFAULT_LOCAL_FIELD] : $conf->getCObj()->data['uid']);
        if (!$parentUid) {
            return '<!-- Invalid data record given -->';
        }

        $medias = self::fetchFilesByTS($conf, $conf->getCObj());
        $conf->getCObj()->data[DEFAULT_LOCAL_FIELD] = $locUid; // Reset UID

        $listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
        $out = $listBuilder->render(
            $medias,
            false,
            $templateCode,
            'tx_rnbase_util_MediaMarker',
            'media.',
            'MEDIA',
            $conf->getFormatter()
        );

        // Now set the identifier
        $markerArray = ['###MEDIA_PARENTUID###' => $parentUid];
        $out = tx_rnbase_util_BaseMarker::substituteMarkerArrayCached($out, $markerArray);

        return $out;
    }

    /**
     * returns the filelist comma seperated.
     * this is equivalent to tx_dam_tsfe->fetchFileList.
     *
     * @param string $content
     * @param array  $tsConf
     *
     * @return string
     */
    public function fetchFileList($content, $tsConf)
    {
        $conf = $this->createConf($tsConf);
        $filelist = self::fetchFilesByTS($conf, $conf->getCObj());
        $files = [];
        foreach ($filelist as $fileModel) {
            $files[] = $fileModel->getFilePath();
        }

        return implode(',', $files);
    }

    /**
     * This method is taken from TYPO3\CMS\Frontend\ContentObject\FileContentObject.
     * It is a good tradition in TYPO3 that code can not be re-used. TYPO3 6.x makes
     * no difference...
     *
     * @param Tx_Rnbase_Configuration_ProcessorInterface $conf
     * @param $cObj
     * @param string $confId
     *
     * @return array
     */
    public static function fetchFilesByTS($conf, $cObj, $confId = '')
    {
        /* @var $fileRepository \TYPO3\CMS\Core\Resource\FileRepository */
        $fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
        $pics = [];
        tx_rnbase::load('tx_rnbase_util_Strings');
        // Getting the files
        // Try DAM style
        if ($conf->get($confId.'refTable')) {
            $referencesForeignTable = $conf->getCObj()->stdWrap($conf->get($confId.'refTable'), $conf->get($confId.'refTable.'));
            $referencesFieldName = $conf->getCObj()->stdWrap($conf->get($confId.'refField'), $conf->get($confId.'refField.'));
            $referencesForeignUid = $conf->getCObj()->stdWrap($conf->get($confId.'refUid'), $conf->get($confId.'refUid.'));
            if (!$referencesForeignUid) {
                $referencesForeignUid = isset($cObj->data['_LOCALIZED_UID']) ?
                                        $cObj->data['_LOCALIZED_UID'] : $cObj->data['uid'];
            }
            $pics = $fileRepository->findByRelation($referencesForeignTable, $referencesFieldName, $referencesForeignUid);
        } elseif (is_array($conf->get($confId.'references.'))) {
            $refConfId = $confId.'references.';
            /*
            The TypoScript could look like this:# all items related to the page.media field:
            references {
            table = pages
            uid.data = page:uid
            fieldName = media
            }# or: sys_file_references with uid 27:
            references = 27
             */

            // It's important that this always stays "fieldName" and not be renamed to "field" as it would otherwise collide with the stdWrap key of that name
            $referencesFieldName = $conf->getCObj()->stdWrap($conf->get($refConfId.'fieldName'), $conf->get($refConfId.'fieldName.'));
            if ($referencesFieldName) {
                $table = $cObj->getCurrentTable();
                if ('pages' === $table && isset($cObj->data['_LOCALIZED_UID']) && intval($cObj->data['sys_language_uid']) > 0) {
                    $table = 'pages_language_overlay';
                }
                $referencesForeignTable = $conf->getCObj()->stdWrap($conf->get($refConfId.'table'), $conf->get($refConfId.'table.'));
                $referencesForeignTable = $referencesForeignTable ? $referencesForeignTable : $table;

                $referencesForeignUid = $conf->getCObj()->stdWrap($conf->get($refConfId.'uid'), $conf->get($refConfId.'uid.'));
                $referencesForeignUid = $referencesForeignUid ?
                        $referencesForeignUid :
                        (isset($cObj->data['_LOCALIZED_UID']) ? $cObj->data['_LOCALIZED_UID'] : $cObj->data['uid']);
                // Vermutlich kann hier auch nur ein Objekt geliefert werden...
                $pics = [];
                $referencesForeignUid = tx_rnbase_util_Strings::intExplode(',', $referencesForeignUid);
                foreach ($referencesForeignUid as $refForUid) {
                    if (!$conf->get($refConfId.'treatIdAsReference')) {
                        $pics[] = $fileRepository->findFileReferenceByUid($refForUid);
                    } else {
                        $pics[] = $fileRepository->findByRelation($referencesForeignTable, $referencesFieldName, $refForUid);
                    }
                }
            } elseif ($refUids = $conf->getCObj()->stdWrap($conf->get($refConfId.'uid'), $conf->get($refConfId.'uid.'))) {
                if (!empty($refUids)) {
                    $refUids = tx_rnbase_util_Strings::intExplode(',', $refUids);
                    foreach ($refUids as $refUid) {
                        $pics[] = $fileRepository->findFileReferenceByUid($refUid);
                    }
                }
            }
        }
        // TODO: Hook
        tx_rnbase_util_Misc::callHook(
            'rn_base',
            'util_TSFal_fetchFilesByTS_appendMedia_hook',
            ['conf' => $conf, '$confId' => $confId, 'media' => &$pics],
            null
        );

        // gibt es ein Limit/offset
        $offset = intval($conf->get($confId.'offset'));
        $limit = intval($conf->get($confId.'limit'));
        if (!empty($pics) && $limit) {
            $pics = array_slice($pics, $offset, $limit);
        } elseif (!empty($pics) && $offset) {
            $pics = array_slice($pics, $offset);
        }
        // Die Bilder sollten jetzt noch in ein
        $fileObjects = self::convertRef2Media($pics);

        return $fileObjects;
    }

    /**
     * @param $pics
     *
     * @return array[tx_rnbase_model_media]
     */
    protected static function convertRef2Media($pics)
    {
        $fileObjects = [];
        if (is_array($pics)) {
            foreach ($pics as $pic) {
                // getProperties() liefert derzeit nicht zurück
                $fileObjects[] = tx_rnbase::makeInstance('tx_rnbase_model_media', $pic);
            }
        } elseif (is_object($pics)) {
            $fileObjects[] = tx_rnbase::makeInstance('tx_rnbase_model_media', $pics);
        }

        return $fileObjects;
    }

    /**
     * Erstellt eine Instanz von Tx_Rnbase_Configuration_ProcessorInterface.
     *
     * @param array $conf
     *
     * @return Tx_Rnbase_Configuration_ProcessorInterface
     */
    public function createConf($conf)
    {
        $configurations = tx_rnbase::makeInstance('Tx_Rnbase_Configuration_Processor');
        $configurations->init($conf, $this->cObj, $conf['qualifier'], $conf['qualifier']);

        return $configurations;
    }

    /**
     * Returns the first reference of a file. Usage by typoscript:.
     *
     * lib.logo = IMAGE
     * lib.logo {
     *   file.maxH = 30
     *   file.maxW = 30
     *   file.treatIdAsReference = 1
     *   file.import.cObject = USER
     *   file.import.cObject {
     *     userFunc=tx_rnbase_util_TSFAL->fetchFirstReference
     *     refField=t3logo
     *     refTable=tx_cfcleague_teams
     *     ### default is the uid of the cObject
     *     refUid.field = my_uid_field_like_pid
     *   }
     * }
     *
     * @param string $content
     * @param array  $conf
     *
     * @return string || int
     */
    public function fetchFirstReference($content, $configuration)
    {
        $contentObject = $this->cObj;

        if ($configuration['refUid'] || $configuration['refUid.']) {
            $uid = intval($contentObject->stdWrap($configuration['refUid'], $configuration['refUid.']));
        } else {
            $uid = $contentObject->data['_LOCALIZED_UID'] ? $contentObject->data['_LOCALIZED_UID'] : $contentObject->data['uid'];
        }
        $refTable = ($configuration['refTable'] && is_array($GLOBALS['TCA'][$configuration['refTable']])) ?
                    $configuration['refTable'] : 'tt_content';
        $refField = trim($contentObject->stdWrap($configuration['refField'], $configuration['refField.']));

        if (isset($GLOBALS['BE_USER']->workspace) && 0 !== $GLOBALS['BE_USER']->workspace) {
            $workspaceRecord = Tx_Rnbase_Backend_Utility::getWorkspaceVersionOfRecord(
                $GLOBALS['BE_USER']->workspace,
                'tt_content',
                $uid,
                'uid'
            );

            if ($workspaceRecord) {
                $uid = $workspaceRecord['uid'];
            }
        }
        $files = $this->getFileRepository()->findByRelation($refTable, $refField, $uid);

        $fileUid = '';
        if (!empty($files)) {
            $fileUid = $files[0]->getUid();
        }

        return $fileUid;
    }

    /**
     * @return TYPO3\CMS\Core\Resource\FileRepository
     */
    protected function getFileRepository()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
    }

    /**
     * Fetches FAL records.
     *
     * @param string $tablename
     * @param int    $uid
     * @param string $refField
     *
     * @return array[tx_rnbase_model_media]
     */
    public static function fetchFiles($tablename, $uid, $refField)
    {
        $pics = self::fetchReferences($tablename, $uid, $refField);
        $fileObjects = self::convertRef2Media($pics);

        return $fileObjects;
    }

    /**
     * Fetch FAL references.
     *
     * @param string $tablename
     * @param int    $uid
     * @param string $refField
     *
     * @return array[\TYPO3\CMS\Core\Resource\FileReference]
     */
    public static function fetchReferences($tablename, $uid, $refField)
    {
        /**
         * @var \TYPO3\CMS\Core\Resource\FileRepository
         */
        $fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
        $refs = $fileRepository->findByRelation($tablename, $refField, $uid);

        return $refs;
    }

    /**
     * Render thumbnails for references in backend.
     *
     * @param $references
     * @param $size
     * @param $addAttr
     */
    public static function createThumbnails($references, $sizeArr = false)
    {
        $ret = [];
        foreach ($references as $fileRef) {
            /* @var $fileRef \TYPO3\CMS\Core\Resource\FileReference */
            if (!is_object($fileRef)) {
                continue;
            }
            $thumbnail = false;
            /* @var $fileObject \TYPO3\CMS\Core\Resource\File */
            $fileObject = $fileRef->getOriginalFile();
            if ($fileObject) {
                $imageSetup = [];
                unset($imageSetup['field']);
                $sizeArr = $sizeArr ? $sizeArr : ['width' => 64, 'height' => 64];
                $imageSetup = array_merge($sizeArr, $imageSetup);
                $imageUrl = $fileObject->process(\TYPO3\CMS\Core\Resource\ProcessedFile::CONTEXT_IMAGEPREVIEW, $imageSetup)->getPublicUrl(true);
                $thumbnail = '<img src="'.$imageUrl.'" alt="'.htmlspecialchars($fileRef->getTitle()).'">';
                // TODO: Das geht bestimmt besser...
            }
            if ($thumbnail) {
                $ret[] = $thumbnail;
            }
        }

        return $ret;
    }

    /**
     * Returns the TCA description for a DAM media field.
     *
     *  $options = array(
     *          'label' => 'Ein Bild',
     *          'config' => array(
     *                  'maxitems' => 2,
     *                  'size' => 2,
     *              ),
     *      )
     *
     * @param array $ref
     * @param array $options These options are merged into the resulting TCA
     *
     * @return array
     */
    public static function getMediaTCA($ref, $options = [])
    {
        // $options war früher ein String. Daher muss auf String getestet werden.
        $type = 'image';
        if (is_string($options)) {
            $type = $options;
        }
        if (is_array($options)) {
            $type = isset($options['type']) ? $options['type'] : $type;
            unset($options['type']);
        }
        $customSettingOverride = (
            empty($options['config']['customSettingOverride'])
                || !is_array($options['config']['customSettingOverride'])
        ) ? [] : $options['config']['customSettingOverride'];
        $allowedFileExtensions = (string) $options['config']['allowedFileExtensions'];
        $disallowedFileExtensions = (string) $options['config']['disallowedFileExtensions'];
        if ('image' == $type) {
            $ttContentLocallang = \tx_rnbase_util_TYPO3::isTYPO87OrHigher() ? 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf' :
                'LLL:EXT:cms/locallang_ttc.xlf';
            $types = self::buildMediaPalette();
            $customSettingOverride = array_merge(
                [
                    'appearance' => [
                        'createNewRelationLinkTitle' => $ttContentLocallang.':images.addFileReference',
                    ],
                    // custom configuration for displaying fields in the overlay/reference table
                    // to use the imageoverlayPalette instead of the basicoverlayPalette
                    'foreign_types' => $types,
                    'overrideChildTca' => [
                        'types' => $types,
                    ],
                ],
                $customSettingOverride
            );
            if (TYPO3::isTYPO95OrHigher()) {
                unset($customSettingOverride['foreign_types']);
            } else {
                unset($customSettingOverride['overrideChildTca']['types']);
            }
            if (empty($allowedFileExtensions)) {
                $allowedFileExtensions = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
            }
        }

        $tca = [
            'label' => \Sys25\RnBase\Backend\Utility\TcaTool::buildGeneralLabel('images'),
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                $ref,
                $customSettingOverride,
                $allowedFileExtensions,
                $disallowedFileExtensions
            ),
        ];

        if (!empty($tca) && is_array($options)) {
            foreach ($options as $key => $option) {
                if (is_array($option)) {
                    if (!isset($tca[$key])) {
                        $tca[$key] = [];
                    }
                    foreach ($option as $subkey => $suboption) {
                        $tca[$key][$subkey] = $suboption;
                    }
                } else {
                    $tca[$key] = $option;
                }
            }
        }

        return $tca;
    }

    private static function buildMediaPalette()
    {
        $commonTcaLocallang = \tx_rnbase_util_TYPO3::isTYPO87OrHigher() ? 'LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf' :
            'LLL:EXT:lang/locallang_tca.xlf';
        $paletteLabel = TYPO3::isTYPO95OrHigher() ? '' : $commonTcaLocallang.':sys_file_reference.imageoverlayPalette';
        $typeDefs = [
            '0',
            \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT,
            \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE,
            \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO,
            \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO,
            \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION,
        ];
        $types = [];
        foreach ($typeDefs as $typeDef) {
            $types[$typeDef] = [
                'showitem' => '
                                --palette--;'.$paletteLabel.';imageoverlayPalette,
                                --palette--;;filePalette',
            ];
        }

        return $types;
    }

    /**
     * Add a reference to a DAM media file.
     *
     * @param string $tableName
     * @param string $fieldName
     * @param int    $itemId
     * @param int    $mediaUid
     * @param int    $pId
     * @param int    $sorting
     *
     * @return int
     */
    public static function addReference($tableName, $fieldName, $itemId, $mediaUid, $pId = 0, $sorting = 1)
    {
        $data = [];
        $data['pid'] = $pId;
        $data['uid_foreign'] = $itemId;
        $data['uid_local'] = $mediaUid;
        $data['tstamp'] = $data['crdate'] = $GLOBALS['EXEC_TIME'];
        $data['tablenames'] = $tableName;
        $data['fieldname'] = $fieldName;
        $data['sorting_foreign'] = $sorting;
        $data['table_local'] = 'sys_file';

        $id = tx_rnbase_util_DB::doInsert('sys_file_reference', $data);

        // Now count all items
        self::updateImageCount($tableName, $fieldName, $itemId);

        return $id;
    }

    /**
     * Removes sys_file_reference entries.
     *
     * @param string $tableName
     * @param string $fieldName
     * @param int    $itemId
     * @param string $uids      (list of sys_file_reference uids)
     */
    public static function deleteReferencesByReference($tableName, $fieldName, $itemId, $uids)
    {
        $where = 'tablenames = '.tx_rnbase_util_DB::fullQuoteStr($tableName, 'sys_file_reference');
        $where .= ' AND fieldname = '.tx_rnbase_util_DB::fullQuoteStr($fieldName, 'sys_file_reference');
        $where .= ' AND uid_foreign = '.(int) $itemId;
        $uids = is_array($uids) ? $uids : tx_rnbase_util_Strings::intExplode(',', $uids);
        if (!empty($uids)) {
            $uids = implode(',', $uids);
            $where .= ' AND uid IN ('.$uids.') ';
        }
        tx_rnbase_util_DB::doDelete('sys_file_reference', $where);
        // Jetzt die Bildanzahl aktualisieren
        self::updateImageCount($tableName, $fieldName, $itemId);
    }

    /**
     * Removes FAL references. If no parameter is given, all references will be removed.
     *
     * @param string $tableName
     * @param string $fieldName
     * @param int    $itemId
     * @param string $uids      (list of sys_file uids)
     */
    public static function deleteReferencesByFile($tableName, $fieldName, $itemId, $uids = '')
    {
        self::deleteReferences($tableName, $fieldName, $itemId, $uids);
    }

    /**
     * Removes FAL references. If no parameter is given, all references will be removed.
     *
     * @param string $tableName
     * @param string $fieldName
     * @param int    $itemId
     * @param string $uids      (list of sys_file uids)
     */
    public static function deleteReferences($tableName, $fieldName, $itemId, $uids = '')
    {
        $where = 'tablenames = '.tx_rnbase_util_DB::fullQuoteStr($tableName, 'sys_file_reference');
        $where .= ' AND fieldname = '.tx_rnbase_util_DB::fullQuoteStr($fieldName, 'sys_file_reference');
        $where .= ' AND uid_foreign = '.(int) $itemId;
        if (strlen(trim($uids))) {
            $uids = implode(',', tx_rnbase_util_Strings::intExplode(',', $uids));
            $where .= ' AND uid_local IN ('.$uids.') ';
        }
        tx_rnbase_util_DB::doDelete('sys_file_reference', $where);
        // Jetzt die Bildanzahl aktualisieren
        self::updateImageCount($tableName, $fieldName, $itemId);
    }

    /**
     * die Bildanzahl aktualisieren.
     */
    public static function updateImageCount($tableName, $fieldName, $itemId)
    {
        $values = [];
        $values[$fieldName] = self::getImageCount($tableName, $fieldName, $itemId);
        tx_rnbase_util_DB::doUpdate($tableName, 'uid='.$itemId, $values);
    }

    /**
     * Get picture count.
     *
     * @return int
     */
    public static function getImageCount($tableName, $fieldName, $itemId)
    {
        $options = [];
        $options['where'] = 'tablenames = '.tx_rnbase_util_DB::fullQuoteStr($tableName, 'sys_file_reference');
        $options['where'] .= ' AND fieldname = '.tx_rnbase_util_DB::fullQuoteStr($fieldName, 'sys_file_reference');
        $options['where'] .= ' AND uid_foreign = '.(int) $itemId;
        $options['count'] = 1;
        $options['enablefieldsoff'] = 1;
        $ret = tx_rnbase_util_DB::doSelect('count(*) AS \'cnt\'', 'sys_file_reference', $options);

        return empty($ret) ? 0 : (int) $ret[0]['cnt'];
    }

    /**
     * Get picture usage count.
     *
     * @param int $mediaUid
     *
     * @return int
     */
    public static function getReferencesCount($mediaUid)
    {
        $options = [];
        $options['where'] = 'uid_local = '.(int) $mediaUid;
        $options['count'] = 1;
        $options['enablefieldsoff'] = 1;
        $ret = tx_rnbase_util_DB::doSelect('count(*) AS \'cnt\'', 'sys_file_reference', $options, 0);
        $cnt = count($ret) ? intval($ret[0]['cnt']) : 0;

        return $cnt;
    }

    /**
     * Return all references for the given reference data.
     *
     * @param string $refTable
     * @param string $refField
     *
     * @return array
     */
    public static function getReferences($refTable, $refUid, $refField)
    {
        return static::fetchReferences($refTable, $refUid, $refField);
    }

    protected static function getReferenceFileInfo(
        FileReference $reference
    ) {
        // getProperties gets merged values from reference and the orig file
        $info = $reference->getProperties();
        // add some fileinfo
        $info['file_path_name'] = $reference->getOriginalFile()->getPublicUrl();
        $info['file_abs_url'] = tx_rnbase_util_Misc::getIndpEnv('TYPO3_SITE_URL').$info['file_path_name'];
        $info['file_name'] = $info['name'];

        return $info;
    }

    /**
     * Return file info for all references for the given reference data.
     *
     * @param string $refTable
     * @param string $refField
     *
     * @return array
     */
    public static function getReferencesFileInfo($refTable, $refUid, $refField)
    {
        $infos = [];
        foreach (self::getReferences($refTable, $refUid, $refField) as $reference) {
            $infos[$reference->getUid()] = static::getReferenceFileInfo($reference);
        }

        return $infos;
    }

    /**
     * Return first reference for the given reference data.
     *
     * @param string $refTable
     * @param int    $refUid
     * @param string $refField
     *
     * @return false|\TYPO3\CMS\Core\Resource\FileReference
     */
    public static function getFirstReference($refTable, $refUid, $refField)
    {
        $refs = self::fetchReferences($refTable, $refUid, $refField);

        return reset($refs);
    }

    /**
     * Return file info of first reference for the given reference data.
     *
     * @param string $refTable
     * @param int    $refUid
     * @param string $refField
     *
     * @return array
     */
    public static function getFirstReferenceFileInfo($refTable, $refUid, $refField)
    {
        $reference = self::getFirstReference($refTable, $refUid, $refField);

        return !$reference ? [] : static::getReferenceFileInfo($reference);
    }

    /**
     * Returns a single FAL file reference by uid.
     *
     * @param int $uid uid of reference
     *
     * @return \TYPO3\CMS\Core\Resource\FileReference
     */
    public static function getFileReferenceById($uid)
    {
        return \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->getFileReferenceObject($uid);
    }

    /**
     * @param string                                                $target
     * @param int|\TYPO3\CMS\Core\Resource\ResourceStorageInterface $storage
     *
     * @return \TYPO3\CMS\Core\Resource\File
     */
    public static function indexProcess($target, $storage)
    {
        // get the storage
        if (is_scalar($storage)) {
            $storage = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->getStorageObject(
                $storage,
                [],
                $target
            );
        }
        if (!$storage instanceof \TYPO3\CMS\Core\Resource\ResourceStorageInterface) {
            throw new \InvalidArgumentException('Storage missed for indexing process.');
        }

        // build the relativeStorage Path
        $storageConfig = $storage->getConfiguration();
        if ('relative' === $storageConfig['pathType']) {
            $relativeBasePath = $storageConfig['basePath'];
        } else {
            if (0 !== strpos($storageConfig['basePath'], \Sys25\RnBase\Utility\Environment::getPublicPath())) {
                throw new \LogicException('Could not determine relative storage path.');
            }
            $relativeBasePath = substr($storageConfig['basePath'], strlen(\Sys25\RnBase\Utility\Environment::getPublicPath()));
        }

        // build the identifier, trim the storage path from the target
        if (0 !== strpos($target, $relativeBasePath)) {
            throw new \LogicException('Could not determine identifier path.');
        }
        $identifier = ltrim(substr($target, strlen($relativeBasePath)), '/');

        /* @var $indexer \TYPO3\CMS\Core\Resource\Index\Indexer */
        $indexer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Core\\Resource\\Index\\Indexer',
            $storage
        );
        $fileObject = $indexer->createIndexEntry($identifier);

        return $fileObject;
    }
}
