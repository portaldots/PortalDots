@inject('uiThemeService', 'App\Services\Utils\UIThemeService')

<meta name="color-scheme" content="{{ $uiThemeService->getCssColorScheme() }}">

<style>
    :root {
        color-scheme: {{ $uiThemeService->getCssColorScheme() }};
        --color-primary: {{ $uiThemeService->getCssPrimaryColor() }};
        --color-primary-light: {{ $uiThemeService->getCssPrimaryColor(0.1) }};
        --color-focus-primary: {{ $uiThemeService->getCssPrimaryColor(0.25) }};
        --color-primary-hover: {{ $uiThemeService->getCssPrimaryColor(0.8) }};
        --color-primary-inverse-hover: {{ $uiThemeService->getCssPrimaryColor(0.15) }};
    }

</style>

@if (in_array($uiThemeService->getCurrentTheme(), ['light', 'system'], true))
    <style>
        :root {
            --color-text: rgb(34, 41, 47);
            --color-danger: rgb(219, 60, 62);
            --color-danger-light: rgb(219, 60, 62, 0.1);
            --color-success: rgb(27, 162, 78);
            --color-muted: rgb(108, 117, 125);
            --color-muted-2: rgb(167, 182, 194);
            --color-muted-3: rgb(195, 207, 216);
            --color-border: rgb(218, 224, 230);
            --color-bg-base: rgb(243, 244, 250);
            --color-bg-light: rgb(239, 239, 239);
            --color-bg-surface: rgb(255, 255, 255);
            --color-bg-surface-2: rgb(255, 255, 255);
            --color-form-control: rgb(250, 250, 252);
            --color-form-control-readonly: rgb(255, 255, 255);
            --color-form-control-focus: rgb(255, 255, 255);
            --color-box-shadow: rgba(34, 41, 47, 0.5);
            --color-box-shadow-subdued: rgba(34, 41, 47, 0.03);
            --color-focus-danger: rgba(219, 60, 62, 0.25);
            --color-pre-background: rgba(255, 255, 255, 0.25);
            --color-code-background: rgba(27, 162, 78, 0.1);
            --color-drawer-backdrop: rgba(34, 41, 47, 0.75);
            --color-grid-table-stripe: rgba(239, 239, 239, 0.4);
            --color-top-alert-border: rgba(255, 255, 255, 0.16);
            --color-danger-hover: rgba(219, 60, 62, 0.8);
            --color-success-hover: rgba(27, 162, 78, 0.8);
        }

    </style>
@endif

@if (in_array($uiThemeService->getCurrentTheme(), ['dark', 'system'], true))
    <style>
        {{ $uiThemeService->getCurrentTheme() === 'system' ? '@media (prefers-color-scheme: dark) {' : '' }} :root {
            --color-text: rgb(255, 255, 255);
            --color-danger: rgb(226, 118, 120);
            --color-danger-light: rgb(226, 118, 120, 0.2);
            --color-success: rgb(75, 189, 119);
            --color-muted: rgb(150, 150, 150);
            --color-muted-2: rgb(130, 130, 130);
            /* --color-muted-3: rgb(195, 207, 216); */
            --color-border: rgba(255, 255, 255, 0.12);
            --color-bg-base: rgb(30, 30, 30);
            --color-bg-light: rgb(36, 36, 36);
            --color-bg-surface: rgb(39, 39, 39);
            --color-bg-surface-2: rgb(46, 46, 46);
            --color-form-control: rgba(0, 0, 0, 0.2);
            --color-form-control-readonly: rgb(53, 53, 53);
            --color-form-control-focus: rgba(0, 0, 0, 0.2);
            --color-box-shadow: rgba(0, 0, 0, 1);
            --color-box-shadow-subdued: rgba(0, 0, 0, 0.25);
            --color-focus-danger: rgba(226, 118, 120, 0.4);
            --color-pre-background: rgba(255, 255, 255, 0.02);
            --color-code-background: rgba(75, 189, 119, 0.15);
            --color-drawer-backdrop: rgba(0, 0, 0, 0.75);
            --color-grid-table-stripe: rgb(46, 46, 46);
            --color-top-alert-border: rgba(28, 28, 28, 0.16);
            --color-danger-hover: rgba(226, 118, 120, 0.8);
            --color-success-hover: rgba(75, 189, 119, 0.8);
        }

        {{ $uiThemeService->getCurrentTheme() === 'system' ? '}' : '' }}

    </style>
@endif
