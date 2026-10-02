#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use PortalDots\Updater\CanonicalJson;
use PortalDots\Updater\ReleaseMetadata;

if ($argc !== 6) {
    fwrite(STDERR, "Usage: sign-lease.php ROOT_MANIFEST OUTPUT SEQUENCE UPDATE_ZIP FULL_ZIP\n");
    exit(64);
}
[, $manifestPath, $output, $sequenceText, $updateArchive, $fullArchive] = $argv;
$sequence = filter_var($sequenceText, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($sequence === false) {
    throw new RuntimeException('Lease sequence is invalid.');
}
$envelope = json_decode((string) file_get_contents($manifestPath), true, 128, JSON_THROW_ON_ERROR);
if (!is_array($envelope)) {
    throw new RuntimeException('Root manifest is invalid.');
}
$rootPublic = (string) getenv('UPDATER_PUBLIC_KEY');
$verified = ReleaseMetadata::verifyRoot($envelope, $rootPublic);
$signed = $verified['signed'];
verifyArtifact($signed['artifact'], $updateArchive);
verifyArtifact($signed['full_artifact'], $fullArchive);

$encodedSecret = getenv('UPDATER_RENEWAL_SIGNING_KEY');
$secret = is_string($encodedSecret) ? base64_decode($encodedSecret, true) : false;
if (is_string($secret) && strlen($secret) === SODIUM_CRYPTO_SIGN_SEEDBYTES) {
    $secret = sodium_crypto_sign_secretkey(sodium_crypto_sign_seed_keypair($secret));
}
if (!is_string($secret) || strlen($secret) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
    throw new RuntimeException('Renewal signing key has an invalid size.');
}
$public = sodium_crypto_sign_publickey_from_secretkey($secret);
$freshness = $signed['freshness'];
if (!hash_equals((string) $freshness['public_key'], base64_encode($public))
    || !hash_equals((string) $freshness['keyid'], hash('sha256', $public))) {
    throw new RuntimeException('Renewal signing key is not approved by the root manifest.');
}
$previous = $envelope['lease']['signed']['sequence'] ?? 0;
if (!is_int($previous) || $sequence <= $previous || $sequence < (int) $freshness['sequence_floor']) {
    throw new RuntimeException('Lease sequence must increase.');
}
$issued = new DateTimeImmutable('now', new DateTimeZone('UTC'));
$leaseSeconds = min(2_592_000, (int) $freshness['max_lease_seconds']);
$leaseSigned = [
    'schema' => 1,
    'manifest_sha256' => $verified['digest'],
    'target_version' => $signed['target_version'],
    'artifact_sha256' => $signed['artifact']['sha256'],
    'full_artifact_sha256' => $signed['full_artifact']['sha256'],
    'from_versions_sha256' => hash('sha256', CanonicalJson::encode($signed['from_versions'])),
    'migrations_sha256' => hash('sha256', CanonicalJson::encode($signed['migrations'])),
    'sequence' => $sequence,
    'issued_at' => $issued->format(DATE_ATOM),
    'expires_at' => $issued->modify("+{$leaseSeconds} seconds")->format(DATE_ATOM),
];
$payload = CanonicalJson::encode($leaseSigned);
$envelope['lease'] = [
    'signed' => $leaseSigned,
    'signatures' => [[
        'keyid' => hash('sha256', $public),
        'sig' => base64_encode(sodium_crypto_sign_detached($payload, $secret)),
    ]],
];
if (file_put_contents($output, CanonicalJson::encode($envelope) . "\n") === false) {
    throw new RuntimeException('Cannot write leased manifest.');
}

/** @param array<string, mixed> $artifact */
function verifyArtifact(array $artifact, string $path): void
{
    if (!is_file($path) || (int) $artifact['size'] !== filesize($path)
        || !hash_equals((string) $artifact['sha256'], (string) hash_file('sha256', $path))) {
        throw new RuntimeException('Release artifact differs from root-signed metadata.');
    }
}
