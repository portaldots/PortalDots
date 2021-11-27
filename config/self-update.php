<?php

use App\ReleaseInfo;
use App\Services\Utils\ValueObjects\Version;

$version = Version::parse(ReleaseInfo::VERSION) ?? new Version(1, 0, 0);

return [

    /*
    |--------------------------------------------------------------------------
    | Default source repository type
    |--------------------------------------------------------------------------
    |
    | The default source repository type you want to pull your updates from.
    |
    */

    'default' => 'http',

    /*
    |--------------------------------------------------------------------------
    | Version installed
    |--------------------------------------------------------------------------
    |
    | Set this to the version of your software installed on your system.
    |
    */

    'version_installed' => $version->getFullVersion(),

    /*
    |--------------------------------------------------------------------------
    | Repository types
    |--------------------------------------------------------------------------
    |
    | A repository can be of different types, which can be specified here.
    | Current options:
    | - github
    | - http
    |
    */

    'repository_types' => [
        'github' => [
            'type' => 'github',
            'repository_vendor' => '',
            'repository_name' => '',
            'repository_url' => '',
            'download_path' => '',
            'private_access_token' => '',
            'use_branch' => '',
        ],
        'http' => [
            'type' => 'http',
            'repository_url' => "http://releases.portaldots.com/releases?current_major_version={$version->getMajor()}",
            'pkg_filename_format' => 'PortalDots-_VERSION_',
            'pkg_url_format' => 'http://releases.portaldots.com/downloads/PortalDots-_VERSION_.zip',
            'download_path' => env('SELF_UPDATER_DOWNLOAD_PATH', '/tmp'),
            'private_access_token' => '',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Exclude folders from update
    |--------------------------------------------------------------------------
    |
    | Specific folders which should not be updated and will be skipped during the
    | update process.
    |
    | Here's already a list of good examples to skip. You may want to keep those.
    |
    */

    'exclude_folders' => [
        '__MACOSX',
        'node_modules',
        'bootstrap/cache',
        'bower',
        'storage/app',
        'storage/framework',
        'storage/logs',
        'storage/self-update',
        '.env',
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Logging
    |--------------------------------------------------------------------------
    |
    | Configure if fired events should be logged
    |
    */

    'log_events' => env('SELF_UPDATER_LOG_EVENTS', false),

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Specify for which events you want to get notifications. Out of the box you can use 'mail'.
    |
    */

    'notifications' => [
        'notifications' => [
            \Codedge\Updater\Notifications\Notifications\UpdateSucceeded::class => ['mail'],
            \Codedge\Updater\Notifications\Notifications\UpdateFailed::class => ['mail'],
            \Codedge\Updater\Notifications\Notifications\UpdateAvailable::class => ['mail'],
        ],

        /*
         * Here you can specify the notifiable to which the notifications should be sent. The default
         * notifiable will use the variables specified in this config file.
         */
        'notifiable' => \Codedge\Updater\Notifications\Notifiable::class,

        'mail' => [
            'to' => [
                // 'address' => env('SELF_UPDATER_MAILTO_ADDRESS', 'notifications@example.com'),
                // 'name' => env('SELF_UPDATER_MAILTO_NAME', ''),
            ],

            'from' => [
                // 'address' => env('SELF_UPDATER_MAIL_FROM_ADDRESS', 'updater@example.com'),
                // 'name' => env('SELF_UPDATER_MAIL_FROM_NAME', 'Update'),
            ],
        ],
    ],

    /*
    |---------------------------------------------------------------------------
    | Register custom artisan commands
    |---------------------------------------------------------------------------
    */

    'artisan_commands' => [
        'pre_update' => [
            //'command:signature' => [
            //    'class' => Command class
            //    'params' => []
            //]
        ],
        'post_update' => [

        ],
    ],

];
