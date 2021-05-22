@inject('uiThemeService', 'App\Services\Utils\UIThemeService')

<meta name="color-scheme" content="{{ $uiThemeService->getCssColorScheme() }}">

<style>
    :root {
        color-scheme: {{ $uiThemeService->getCssColorScheme() }};
    }
</style>

@if (in_array($uiThemeService->getCurrentTheme(), ['light', 'system'], true))
<style>
    :root {
        --color-text: rgb(34, 41, 47);
        --color-primary: rgb(26, 121, 244);
        --color-danger: rgb(219, 60, 62);
        --color-success: rgb(27, 162, 78);
        --color-muted: rgb(108, 117, 125);
        --color-muted-2: rgb(167, 182, 194);
        --color-muted-3: rgb(195, 207, 216);
        --color-border: rgb(218, 224, 230);
        --color-bg-grey: rgb(243, 244, 250);
        --color-bg-light: rgb(239, 239, 239);
        --color-behind-text: rgb(255, 255, 255);
        --color-form-control: rgb(250, 250, 252);
        --color-form-control-readonly: rgb(255, 255, 255);
        --color-form-control-focus: rgb(255, 255, 255);
        --color-box-shadow: rgba(34, 41, 47, 0.5);
        --color-focus-primary: rgba(26, 121, 244, 0.25);
        --color-focus-danger: rgba(219, 60, 62, 0.25);
        --color-pre-background: rgba(255, 255, 255, 0.25);
        --color-code-background: rgba(27, 162, 78, 0.1);
        --color-drawer-backdrop: rgba(34, 41, 47, 0.75);
        --color-grid-table-stripe: rgba(239, 239, 239, 0.4);
        --color-top-alert-border: rgba(255, 255, 255, 0.16);
        --color-primary-hover: rgba(26, 121, 244, 0.8);
        --color-primary-inverse-hover: rgba(26, 121, 244, 0.15);
        --color-icon-button-hover: rgba(26, 121, 244, 0.1);
        --color-danger-hover: rgba(219, 60, 62, 0.8);
        --color-success-hover: rgba(27, 162, 78, 0.8);
    }
</style>
@endif

@if (in_array($uiThemeService->getCurrentTheme(), ['dark', 'system'], true))
<style>
    {{ $uiThemeService->getCurrentTheme() === 'system' ? '@media (prefers-color-scheme: dark) {' : '' }}
        :root {
            --color-text: rgb(196, 199, 202);
            --color-primary: rgb(117, 170, 240);
            --color-danger: rgb(226, 118, 120);
            --color-success: rgb(75, 189, 119);
            --color-muted: rgb(123, 130, 136);
            --color-muted-2: rgb(60, 65, 70);
            /* --color-muted-3: rgb(195, 207, 216); */
            --color-border: rgb(44, 44, 48);
            --color-bg-grey: rgb(5, 5, 5);
            --color-bg-light: rgb(18, 18, 18);
            --color-behind-text: rgb(22, 22, 22);
            --color-form-control: rgb(18, 18, 18);
            --color-form-control-readonly: rgb(40, 40, 40);
            --color-form-control-focus: rgb(18, 18, 18);
            --color-box-shadow: rgba(0, 0, 0, 1);
            --color-focus-primary: rgba(117, 170, 240, 0.3);
            --color-focus-danger: rgba(226, 118, 120, 0.4);
            --color-pre-background: rgba(255, 255, 255, 0.02);
            --color-code-background: rgba(75, 189, 119, 0.15);
            --color-drawer-backdrop: rgba(0, 0, 0, 0.75);
            --color-grid-table-stripe: rgba(12, 12, 12, 0.4);
            --color-top-alert-border: rgba(28, 28, 28, 0.16);
            --color-primary-hover: rgba(117, 170, 240, 0.8);
            --color-primary-inverse-hover: rgba(117, 170, 240, 0.15);
            --color-icon-button-hover: rgba(117, 170, 240, 0.2);
            --color-danger-hover: rgba(226, 118, 120, 0.8);
            --color-success-hover: rgba(75, 189, 119, 0.8);
        }
    {{ $uiThemeService->getCurrentTheme() === 'system' ? '}' : '' }}
</style>
@endif
