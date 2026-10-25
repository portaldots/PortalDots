<?php

return [
    'manifest_url' => env(
        'PORTALDOTS_UPDATER_MANIFEST_URL',
        'https://api.github.com/repos/portaldots/PortalDots/releases?per_page=50'
    ),
    'download_hosts' => env(
        'PORTALDOTS_UPDATER_DOWNLOAD_HOSTS',
        'api.github.com,releases.portaldots.com,github.com,objects.githubusercontent.com,release-assets.githubusercontent.com'
    ),
];
