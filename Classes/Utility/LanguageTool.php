<?php

namespace Sys25\RnBase\Utility;

use RuntimeException;
use tx_rnbase;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;

class LanguageTool
{
    /**
     * @var Language
     */
    private $localLangUtil;

    /**
     * @var LanguageService
     */
    private $languageService;

    /**
     * @var TypoScriptService
     */
    private $typoScriptService;

    /**
     * @var array
     */
    private $langFiles = [];

    public function __construct(?Language $language = null, ?TypoScriptService $typoScriptService = null)
    {
        $this->localLangUtil = $language ?? tx_rnbase::makeInstance(Language::class);
        $this->typoScriptService = $typoScriptService ?? tx_rnbase::makeInstance(TypoScriptService::class);
    }

    public function setLanguageService(LanguageService $languageService): void
    {
        $this->languageService = $languageService;
    }

    public function registerLangFile($filename): void
    {
        if (empty($filename)) {
            return;
        }
        if (TYPO3::isTYPO121OrHigher()) {
            $this->langFiles[] = $filename;
            if (Environment::isBackend()) {
                // Das vermeidet nur einen BC. Aber T3 13 muss das weg.
                $this->getLanguageService()->includeLLFile($filename);
            }
        } else {
            $this->localLangUtil->loadLLFile($filename);
        }
    }

    /**
     * @param mixed $filename
     *
     * @deprecated use registerLangFile
     */
    public function includeLLFile($filename): void
    {
        $this->registerLangFile($filename);
    }

    public function registerTsLabels(?array $labels = []): void
    {
        if (empty($labels)) {
            return;
        }
        $labels = $this->typoScriptService->convertTypoScriptArrayToPlainArray($labels);

        if (TYPO3::isTYPO121OrHigher()) {
            foreach ($this->langFiles as $langFile) {
                $this->getLanguageService()->overrideLabels($langFile, $labels);
            }
        } else {
            $this->localLangUtil->loadLLTs($labels);
        }
    }

    public function sL($key): string
    {
        return $this->getLanguageService()->sL($key);
    }

    public function getLL($key, $alt = '', $hsc = false, $labelDebug = false): string
    {
        $result = $alt;
        if (empty($key)) {
            return $result;
        }

        if (TYPO3::isTYPO121OrHigher()) {
            // FIXME: wie gehen wir damit um?
            foreach ($this->langFiles as $langFile) {
                $langFile = $this->ensureFileRef($langFile);

                $label = $langFile.':'.$key;
                $result = $this->getLanguageService()->sL($label);

                if ($result !== $alt) {
                    break;
                }
            }
            if (empty($result)) {
                $result = $alt;
            }
        } else {
            $result = $this->localLangUtil->getLL($key, $alt, false, $labelDebug);
        }

        return $hsc ? htmlspecialchars($result) : $result;
    }

    public function getLanguageService(): LanguageService
    {
        if (null === $this->languageService && Environment::isBackend()) {
            $this->languageService = $GLOBALS['LANG'];
        }

        if (null === $this->languageService) {
            throw new RuntimeException('No language service available');
        }

        return $this->languageService;
    }

    private function ensureFileRef($langFile): string
    {
        if (!Strings::isFirstPartOfStr($langFile, 'LLL:')) {
            $langFile = 'LLL:'.$langFile;
        }

        return $langFile;
    }
}
