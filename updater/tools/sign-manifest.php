#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use PortalDots\Updater\CanonicalJson;

if ($argc !== 3) {
    fwrite(STDERR, "Usage: sign-manifest.php SIGNED_JSON OUTPUT\n");
    exit(64);
}
[, $input, $output] = $argv;
$encodedSecret = getenv('UPDATER_SIGNING_KEY');
$signed = json_decode((string) file_get_contents($input), true, 128, JSON_THROW_ON_ERROR);
$secret = base64_decode($encodedSecret, true);
if (!is_string($secret)) {
    throw new RuntimeException('Signing key is not base64.');
}
if (strlen($secret) === SODIUM_CRYPTO_SIGN_SEEDBYTES) {
    $secret = sodium_crypto_sign_secretkey(sodium_crypto_sign_seed_keypair($secret));
}
if (strlen($secret) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
    throw new RuntimeException('Signing key has an invalid size.');
}
$public = sodium_crypto_sign_publickey_from_secretkey($secret);
$expectedPublic = getenv('UPDATER_PUBLIC_KEY');
if (is_string($expectedPublic) && $expectedPublic !== ''
    && !hash_equals($expectedPublic, base64_encode($public))) {
    throw new RuntimeException('Signing key does not match UPDATER_PUBLIC_KEY.');
}
$payload = CanonicalJson::encode($signed);
$envelope = [
    'signed' => $signed,
    'signatures' => [[
        'keyid' => hash('sha256', $public),
        'sig' => base64_encode(sodium_crypto_sign_detached($payload, $secret)),
    ]],
];
file_put_contents($output, CanonicalJson::encode($envelope) . "\n");
