<?php

class KeyGenerator {
    public static function generateKeys() {
        $config = array(
            "digest_alg" => "sha256",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        // Create the private and public key
        $res = openssl_pkey_new($config);

        // Extract the private key into $privateKey
        openssl_pkey_export($res, $privateKey);

        // Extract the public key into $publicKey
        $publicKey = openssl_pkey_get_details($res);
        $publicKey = $publicKey["key"];

        return array('privateKey' => $privateKey, 'publicKey' => $publicKey);
    }
}

?>
