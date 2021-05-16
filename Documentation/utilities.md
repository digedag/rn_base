Utilities
=========


Crypt
-----

Mit der Crypt Utility können beliebige Daten anhand eines Schlüssels 
ver- und entschlüsselt werden.

Beispiel mit möglichen Optionen (alle optional):
```php
    $crypt = Sys25\RnBase\Utility\Crypt::getInstance(
        array(
            'cipher' => MCRYPT_BLOWFISH,
            'mode' => MCRYPT_MODE_ECB,
            'key' => 'th3S3cr3t',
            'urlencode' => TRUE,
            'base64' => TRUE,
        )
    );
    
    // sensible, zu verschlüsselnde Daten
    $data = array('uid' => 5, 'body' => 'Lorem Ipsum');
    // daten verschlüsseln
    $crypted = $crypt->encrypt($data);
    /* do something with encrypted data */
    // daten wieder entschlüsseln
    $data = $crypt->decrypt($crypted);
```
