@inject('uiThemeService', 'App\Services\Utils\UIThemeService')

<meta name="color-scheme" content="{{ $uiThemeService->getCssColorScheme() }}">

<style>
    body.theme-light,
    body.theme-system {
        @include('includes.head_ui_theme_light')
    }

    body.theme-dark {
        @include('includes.head_ui_theme_dark')
    }

    @media (prefers-color-scheme: dark) {
        body.theme-system {
            @include('includes.head_ui_theme_dark')
        }
    }

</style>
