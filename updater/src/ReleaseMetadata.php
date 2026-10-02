<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class ReleaseMetadata
{
    /** @param array<string, mixed> $envelope @return array{signed: array<string, mixed>, digest: string} */
    public static function verifyRoot(array $envelope, string $encodedPublicKey, bool $requireSchemaTwo = true): array
    {
        $public = base64_decode($encodedPublicKey, true);
        if (!is_string($public) || strlen($public) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            throw new RuntimeException('Updater root public key is invalid.');
        }
        $signed = $envelope['signed'] ?? null;
        $signatures = $envelope['signatures'] ?? null;
        if (!is_array($signed) || !is_array($signatures) || !array_is_list($signatures)) {
            throw new RuntimeException('Published updater metadata is malformed.');
        }
        if ($requireSchemaTwo && ($signed['schema'] ?? null) !== 2) {
            throw new RuntimeException('Published updater metadata does not bind its full release ZIP.');
        }
        $payload = CanonicalJson::encode($signed);
        $keyId = hash('sha256', $public);
        foreach ($signatures as $signature) {
            $decoded = is_array($signature) ? base64_decode((string) ($signature['sig'] ?? ''), true) : false;
            if (is_array($signature) && hash_equals($keyId, (string) ($signature['keyid'] ?? ''))
                && is_string($decoded) && strlen($decoded) === SODIUM_CRYPTO_SIGN_BYTES
                && sodium_crypto_sign_verify_detached($decoded, $payload, $public)) {
                self::validateImmutableArtifacts($signed);
                return ['signed' => $signed, 'digest' => hash('sha256', $payload)];
            }
        }
        throw new RuntimeException('Published updater metadata root signature is invalid.');
    }

    /** @param array<string, mixed> $signed */
    private static function validateImmutableArtifacts(array $signed): void
    {
        if (!preg_match('/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/',
            (string) ($signed['target_version'] ?? ''))
            || !is_array($signed['from_versions'] ?? null)
            || !is_array($signed['migrations'] ?? null)
            || !is_array($signed['freshness'] ?? null)) {
            throw new RuntimeException('Published updater metadata has invalid immutable fields.');
        }
        foreach (['artifact', 'full_artifact'] as $field) {
            $artifact = $signed[$field] ?? null;
            $parts = is_array($artifact) ? parse_url((string) ($artifact['url'] ?? '')) : false;
            if (!is_array($artifact) || !is_array($parts) || ($parts['scheme'] ?? null) !== 'https'
                || strtolower((string) ($parts['host'] ?? '')) !== 'github.com'
                || !is_int($artifact['size'] ?? null) || $artifact['size'] < 1
                || !preg_match('/^[a-f0-9]{64}$/', (string) ($artifact['sha256'] ?? ''))) {
                throw new RuntimeException("Published updater metadata has invalid {$field}.");
            }
        }
    }
}
