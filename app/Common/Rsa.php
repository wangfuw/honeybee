<?php

namespace App\Common;

class  Rsa
{
    const KEY = "-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDO7a4gg3EBbdqWhmVfSr5iZeuZE/tj8tzaX5mySkSUk0NKSvSY
eml9GnBwVRQ4wGnGrOVSfphD6Sr7Bi87MUp4gA5SULzWkXvhqY+jqWKdFoRLPqjd
EZ9YcUu67HIQuXp6PV8hqvpS8pf2N9iqswh5UP3PRjclNcJVAoQ4hbxXNwIDAQAB
AoGAXtVYzvfOS3xgCEoxnTlxBUF2duJMfOLpyn6zvp0AzyKqXRr6/AJl4/rA+wpS
ySuNjorgUi1IdR8gHokYDkWpvbrAduLFGnFAjx0GxKqg6lciCHV7Lf9FvP7SQ9vS
NxNzxDz9g/jalAB9orU71mtxw6Xx7/eRJ670ayNifBYmUFECQQDeAPgXxIGVA0JK
aKIPAKDg17lD5MqHQBNA55ST9WaZhsqWSiRPsUHUa7QlMSZWEg6+KtNoFNsKdwZA
z4M7G7nJAkEA7p25hUNT1SCQdZQS/MWUzbGwEA93XB4zT4q/FYSgU2RTS5gSOirY
GrT651eltncRSyMwkENiR+XV/Y1ENNAI/wJAJ7+Yq/i9EscQmW3+hh0gsOEvBJ70
PB8W02ojShKIGjjuENaZhcNA/B2ElZwlNwfop9fXHi2NwmPpNLVy06R1MQJAa2HV
nHA5KN+XwyIZDWIJXiwJtKCRMYZxxukEpzVhRUYP6iQMh9rCF/q8MyIRdDTPNoYm
k7WPZBW9oHZTbIT2lwJBANzKRUU4KcaKQtVoheChjf5eA6y1khr0U90R+g3cnTw3
SDDPdTMLWs16tvP6Pz7QIlC274ipYj0fV+Sta0YICEY=
-----END RSA PRIVATE KEY-----";

    const P_KEY = "-----BEGIN PUBLIC KEY-----
    MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDO7a4gg3EBbdqWhmVfSr5iZeuZ
    E/tj8tzaX5mySkSUk0NKSvSYeml9GnBwVRQ4wGnGrOVSfphD6Sr7Bi87MUp4gA5S
    ULzWkXvhqY+jqWKdFoRLPqjdEZ9YcUu67HIQuXp6PV8hqvpS8pf2N9iqswh5UP3P
    RjclNcJVAoQ4hbxXNwIDAQAB
    -----END PUBLIC KEY-----";

    public static function decodeByPrivateKey($data)
    {
        $decrypted = '';
        $private_key = openssl_pkey_get_private(self::KEY);
        if ($private_key)
            openssl_private_decrypt(base64_decode($data), $decrypted, $private_key); //私钥解密
        return $decrypted;
    }

    public static function encryptPass($pass, $salt)
    {
        return md5(md5($pass) . $salt);
    }

    public static function publicKeyEncode($data)
    {
        $encrypted = '';
        $public_key = openssl_pkey_get_public(self::P_KEY);
        if ($public_key)
            openssl_public_encrypt($data, $encrypted, $public_key); //私钥加密
        return base64_encode($encrypted);
    }

}
