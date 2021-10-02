<?php
/***************************************************************
 * Copyright notice
 *
 *  (c) 2015 René Nitzsche <rene@system25.de>
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
 * Mcrypt module.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Tx_Rnbase_Utility_Crypt
{
    /**
     * The confiog for the cryption.
     *
     * @var Tx_Rnbase_Domain_Model_Data
     */
    private $storage = null;

    /**
     * The confiog for the cryption.
     *
     * @var Tx_Rnbase_Domain_Model_Data
     */
    private $config = null;

    /**
     * Creates an crypt instance.
     *
     * @param array $config
     *
     * @return Tx_Rnbase_Utility_Crypt
     */
    public static function getInstance($config = null)
    {
        return tx_rnbase::makeInstance('Tx_Rnbase_Utility_Crypt', $config);
    }

    /**
     * The constructor.
     *
     * @param array $config
     */
    public function __construct($config = null)
    {
        $this->config = Tx_Rnbase_Domain_Model_Data::getInstance($config);
        $this->init();
    }

    /**
     * Desctruct cipher module.
     */
    public function __destruct()
    {
        $handler = $this->getStorage()->getHandler();
        if (is_resource($handler)) {
            mcrypt_generic_deinit($handler);
            mcrypt_module_close($handler);
            $this->getStorage()->unsHandler();
        }
    }

    /**
     * Returns the config.
     *
     * @return Tx_Rnbase_Domain_Model_Data
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns the internal storage.
     *
     * @return Tx_Rnbase_Domain_Model_Data
     */
    protected function getStorage()
    {
        if (null === $this->storage) {
            $this->storage = Tx_Rnbase_Domain_Model_Data::getInstance();
        }

        return $this->storage;
    }

    /**
     * initialize the crypt module.
     */
    public function init()
    {
        mt_srand();

        $storage = $this->getStorage();
        $config = $this->getConfig();

        $storage->setCipher(
            $config->hasCipher() ? $config->getCipher() : MCRYPT_BLOWFISH
        );

        $storage->setMode(
            $config->hasMode() ? $config->getMode() : MCRYPT_MODE_ECB
        );

        $handler = mcrypt_module_open(
            $storage->getCipher(),
            '',
            $storage->getMode(),
            ''
        );
        $storage->setHandler($handler);

        if ($config->hasInitVector()) {
            $storage->setInitVector($config->getInitVector());
        } else {
            if (MCRYPT_MODE_CBC == $storage->getMode()) {
                $storage->setInitVector(
                    substr(
                        md5(
                            mcrypt_create_iv(
                                mcrypt_enc_get_iv_size(
                                    $handler
                                ),
                                MCRYPT_RAND
                            )
                        ),
                        -mcrypt_enc_get_iv_size(
                            $handler
                        )
                    )
                );
            } else {
                $storage->setInitVector(
                    mcrypt_create_iv(
                        mcrypt_enc_get_iv_size(
                            $handler
                        ),
                        MCRYPT_RAND
                    )
                );
            }
        }

        mcrypt_generic_init(
            $handler,
            $this->buildKey(),
            $storage->getInitVector()
        );
    }

    /**
     * builds the key for cryption.
     *
     * dont set this key to the storage for security reason!
     *
     * @return string
     */
    protected function buildKey()
    {
        $key = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];

        if ($this->getConfig()->hasKey()) {
            $key .= $this->getConfig()->getKey();
            // remove the key for security reason!
            $this->getConfig()->unsKey();
        }

        $maxKeySize = mcrypt_enc_get_key_size(
            $this->getStorage()->getHandler()
        );

        // crop the key to allowed size!
        return substr(sha1($key), 0, $maxKeySize);
    }

    /**
     * Encrypts the given data using symmetric-key encryption.
     *
     * @param mixed $data
     *
     * @return string
     */
    public function encrypt($data)
    {
        if (!$this->getStorage()->getHandler()) {
            throw new Exception('Crypt module not initialized.');
        }

        if (0 === strlen($data)) {
            return $data;
        }

        $encrypted = serialize($data);

        $encrypted = mcrypt_generic(
            $this->getStorage()->getHandler(),
            $encrypted
        );

        if ($this->getConfig()->getBase64()) {
            $encrypted = base64_encode($encrypted);
        }

        if ($this->getConfig()->getUrlencode()) {
            $encrypted = urlencode($encrypted);
        }

        return $encrypted;
    }

    /**
     * Decrypts encrypted cipher using symmetric-key encryption.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function decrypt($data)
    {
        if (!$this->getStorage()->getHandler()) {
            throw new Exception('Crypt module not initialized.');
        }

        if (0 === strlen($data)) {
            return $data;
        }

        $decrypted = $data;

        if ($this->getConfig()->getUrlencode()) {
            $decrypted = urldecode($decrypted);
        }

        if ($this->getConfig()->getBase64()) {
            $decrypted = base64_decode($decrypted);
        }

        $decrypted = mdecrypt_generic(
            $this->getStorage()->getHandler(),
            $decrypted
        );

        $decrypted = unserialize($decrypted);

        return $decrypted;
    }
}
