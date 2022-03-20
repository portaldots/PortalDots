/* Primary */
color-scheme: dark;
--color-primary: {{ $uiThemeService->getCssPrimaryColor(1, true) }};
--color-primary-light: {{ $uiThemeService->getCssPrimaryColor(0.1, true) }};
--color-focus-primary: {{ $uiThemeService->getCssPrimaryColor(0.25, true) }};
--color-primary-hover: {{ $uiThemeService->getCssPrimaryColor(0.8, true) }};
--color-primary-inverse-hover: {{ $uiThemeService->getCssPrimaryColor(0.15, true) }};
/* Other */
--color-text: rgb(215, 215, 215);
--color-danger: rgb(226, 118, 120);
--color-danger-light: rgb(226, 118, 120, 0.2);
--color-success: rgb(75, 189, 119);
--color-muted: rgb(150, 150, 150);
--color-muted-2: rgb(130, 130, 130);
/* --color-muted-3: rgb(195, 207, 216); */
--color-border: rgba(255, 255, 255, 0.12);
--color-bg-base: rgb(0, 0, 0);
--color-bg-light: rgba(255, 255, 255, 0.075);
--color-bg-surface: rgb(30, 30, 30);
--color-bg-surface-2: rgb(46, 46, 46);
--color-bg-surface-3: rgb(51, 51, 51);
--color-form-control: rgba(0, 0, 0, 0.2);
--color-form-control-readonly: rgb(53, 53, 53);
--color-form-control-focus: rgba(0, 0, 0, 0.2);
--color-box-shadow: rgba(0, 0, 0, 1);
--color-box-shadow-subdued: rgba(0, 0, 0, 0.4);
--color-focus-danger: rgba(226, 118, 120, 0.4);
--color-pre-background: rgba(255, 255, 255, 0.02);
--color-code-background: rgba(75, 189, 119, 0.15);
--color-drawer-backdrop: rgba(0, 0, 0, 0.75);
--color-grid-table-stripe: rgb(40, 40, 40);
--color-top-alert-border: rgba(28, 28, 28, 0.16);
--color-danger-hover: rgba(226, 118, 120, 0.8);
--color-success-hover: rgba(75, 189, 119, 0.8);
--color-emphasis-gray: rgb(100, 100, 100);
--color-fg-emphasis-gray: rgb(255, 255, 255);
