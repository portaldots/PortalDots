@inject('uiThemeService', 'App\Services\Utils\UIThemeService')

<meta name="color-scheme" content="{{ $uiThemeService->getCssColorScheme() }}">

<style>
    html.theme-light,
    html.theme-system {
        @include('includes.head_ui_theme_light')
    }

    html.theme-dark {
        @include('includes.head_ui_theme_dark')
    }

    @media (prefers-color-scheme: dark) {
        html.theme-system {
            @include('includes.head_ui_theme_dark')
        }
    }

    html {
        accent-color: var(--color-primary);
    }

</style>
