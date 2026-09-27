<?php

declare(strict_types=1);

namespace PortalDots\Updater;

final class Config
{
    public const RUNTIME_VERSION = 2;

    /** @param list<string> $downloadHosts */
    public function __construct(
        public readonly string $basePath,
        public readonly string $privatePath,
        public readonly string $manifestUrl,
        public readonly array $downloadHosts,
        public readonly string $publicKeyPath,
        public readonly int $maxArchiveBytes = 536_870_912,
        public readonly int $maxExtractedBytes = 1_073_741_824,
        public readonly int $maxArchiveFiles = 30_000,
        public readonly int $drainTimeoutSeconds = 300,
        public readonly ?string $applicationUrl = null,
        public readonly bool $allowInsecureLocalhost = false,
    ) {
    }

    public static function fromEnvironment(string $basePath): self
    {
        $basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $environment = self::environment($basePath);
        $manifestUrl = $environment['PORTALDOTS_UPDATER_MANIFEST_URL']
            ?? 'https://api.github.com/repos/portaldots/PortalDots/releases?per_page=50';
        $hosts = array_values(array_filter(array_map(
            'trim',
            explode(',', $environment['PORTALDOTS_UPDATER_DOWNLOAD_HOSTS']
                ?? 'api.github.com,releases.portaldots.com,github.com,objects.githubusercontent.com,release-assets.githubusercontent.com')
        )));

        return new self(
            $basePath,
            $environment['PORTALDOTS_UPDATER_PRIVATE_PATH']
                ?? $basePath . '/storage/app/updater/private',
            $manifestUrl,
            $hosts,
            $basePath . '/updater/keys/release.pub',
            applicationUrl: $environment['APP_URL'] ?? null,
            allowInsecureLocalhost: ($environment['PORTALDOTS_UPDATER_ALLOW_INSECURE_LOCALHOST'] ?? '') === '1',
        );
    }

    /** @return array<string, string> */
    public static function environment(string $basePath): array
    {
        $path = rtrim($basePath, DIRECTORY_SEPARATOR) . '/.env';
        if (!is_file($path) || !is_readable($path)) {
            return [];
        }

        $values = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $key)) {
                continue;
            }
            if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"'))
                || ($value[0] === "'" && str_ends_with($value, "'")))) {
                $value = substr($value, 1, -1);
            }
            $values[$key] = $value;
        }

        return $values;
    }
}
