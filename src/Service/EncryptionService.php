<?php

namespace App\Service;

class EncryptionService
{
    private string $cipherAlgo = 'AES-256-CBC';

    public function __construct(
        private readonly string $encryptionKey
    ) {}

    public function encrypt(string $plaintext): string
    {
        $iv = random_bytes(16);
        $ciphertext = openssl_encrypt($plaintext, $this->cipherAlgo, $this->encryptionKey, OPENSSL_RAW_DATA, $iv);

        return base64_encode($iv . $ciphertext);
    }

    public function decrypt(string $encrypted): string
    {
        $data = base64_decode($encrypted);
        $iv = substr($data, 0, 16);
        $ciphertext = substr($data, 16);

        return openssl_decrypt($ciphertext, $this->cipherAlgo, $this->encryptionKey, OPENSSL_RAW_DATA, $iv);
    }
}