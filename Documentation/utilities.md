Utilities
=========


Crypt
-----

Mit der Crypt Utility können beliebige Daten anhand eines Schlüssels 
ver- und entschlüsselt werden.

Beispiel mit allen möglichen Optionen:
```php
	$crypt = Tx_Rnbase_Utility_Crypt::getInstance(
		array_merge(
			array(
				'cipher' => MCRYPT_BLOWFISH,
				'mode' => MCRYPT_MODE_ECB,
				'key' => 'th3S3cr3t',
				'urlencode' => TRUE,
				'base64' => TRUE,
			),
			$config
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
