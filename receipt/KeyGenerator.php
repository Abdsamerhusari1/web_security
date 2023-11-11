<?php
if (isset($error_message) && !empty($error_message)) {
	echo '<p style="color: red;">' . $error_message . '</p>';
}
?>
<?php

class KeyGenerator {
	public static function generateKeys() {
		$config = [
			"digest_alg" => "sha256",
			"private_key_bits" => 2048,
			"private_key_type" => OPENSSL_KEYTYPE_RSA,
		];

		$res = openssl_pkey_new($config);
		openssl_pkey_export($res, $privKey);
		$pubKey = openssl_pkey_get_details($res);
		$pubKey = $pubKey["key"];

		return ['privateKey' => $privKey, 'publicKey' => $pubKey];
	}
}
?>
