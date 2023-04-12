<?php

namespace App\Common;

class  Rsa
{
    const KEY = "-----BEGIN PRIVATE KEY-----
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAMKFWAN88Fa2Jx1C8gamslK5
8TCOep0M2nkJnezFiGajx2sGn1EtuPdCK8BMtpSifG46tVLoy+s0X8R5pWNQZWkix135ulj
ZFVRBeetUYo2jLv+nfY352jBZWEghQANlEsU5RJAMh8I9YETFzNIoZu+Rao2w65R4T1R8SUQ
U/h7PAgMBAAECgYAuiLO7ceIU/FJzH5bCnJJAVBcs5IjUlUSWfAR5pe+xjjCgm971DpkXHY9
V24Q6Hnzh6c2C3goaZFccq3UzQwJfLwrCN31/kFUaeZYIYYXYS81DHnGtMI4ZWGbph8TQkf3
2KUjEU2XYYPHHvqNok2w7Rp/MyDe76R0KBtbTWASqIQJBAPdfG9k4DUfEDKYRBIAho8ATLUL
+kWh1CxXprnQuyfshfhDg/8phs6L4eOXa2tVrbxBkvbHBuA65M50kVsPFZ+kCQQDJTk7Tmxj
zozQZdVg45kKXBkkjl9g/1Dbj326faTKCjq7sDk1GllYTsRnBZE/YJTn7qmojeE0yWPm3toro
QtX3AkByuCfZF/aItrHK/g9hQLiJJhuSey6CC+2lLucZuG0xSroFJ+NYPvEo/iRLLLDZ0uYB6
0ZRvm4WXetC0Axw5AapAkAxmDtPYryo+aJSS1iq2/+32XKXdEdwokXLqjZEy9QH2kM6IOPk6h
DW1SD1RlNteu5oFDoF9xN9vfH30t8yDZVNAkEAwGAv2tDMK+lq7kiuPQZBqOT6ib/+mgBiCSJ
YZ58ARTjHNVHJ5YrkJadGB5IvWANRb8r9ecnyDX+x1Cb/ZcUlhg==
-----END PRIVATE KEY-----";

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
}
