# PortalDots 6.x in the browser

This PoC packages Laravel 13, PHP 8.3 WebAssembly, and a seeded SQLite database as a static site. Each landing-page tab owns one PHP Worker, virtual filesystem, and cookie jar. Reloading the application iframe keeps its data; reloading the landing page starts a new instance. There is no browser-storage persistence layer.

## Build and serve

Requirements: Docker, Node.js 22.13+ (22.23 tested), Yarn Classic 1.22, and localhost or HTTPS with Service Workers enabled. Run from a clean checkout with the application changes in this PoC applied:

```sh
cd wasm-demo
npm ci --cache .work/npm-cache
npm run build
npm run serve
```

Open `http://localhost:8787/`. Log in as `admin@example.test` with password `PortalDemo123!`. The participant is `user@example.test` with the same password. Student IDs `DEMO1` and `DEMO2` also work.

The build stages application files under `.work/app`, installs locked Composer dependencies in PHP 8.3, installs the locked Yarn dependencies, and runs Vite with `ASSET_URL=/app`. It runs the unmodified migrations in native-architecture MySQL 8.4, seeds fixtures, converts every table (including `migrations`) to SQLite using Laravel schema introspection, checks row counts and SQLite integrity/foreign keys, and boots Laravel against SQLite. It then creates `dist/portaldots.zip` and bundles the runtime. No vendor patch or emitted JS/CSS rewriting is performed. The root Composer, package, and Vite configuration files are unchanged.

MySQL runs with `--network none`, `--rm`, and a tmpfs data directory. PHP build commands share only that container's network namespace. Dependency installation uses the network. There are no Docker volumes or published database ports. Containers are removed in the build's `finally` block. Images remain: `portaldots-wasm-php83-poc`, `php:8.3-cli`, `composer:2`, and `mysql:8.4`. Docker's legacy builder is selected to avoid writing Buildx activity files outside the checkout.

## Subdirectory hosting

Choose the deployment path at build time:

```sh
SITE_BASE=/examples/portal/ npm run build
```

Deploy **the contents of `dist/`** at `/examples/portal/`. The landing page is `/examples/portal/`, the application is `/examples/portal/app/`, and Vite assets use `/examples/portal/app/build/`. `SITE_BASE` must have leading and trailing slashes; path segments may contain letters, digits, underscores, and hyphens.

Vite controls asset URLs. Legacy absolute navigation links (support, Markdown guide, editor preview, and the drawer home link) are adapted in the staged Vue source before compilation. This is separate from asset-base configuration and does not mutate the checkout sources or compiled bundles.

## Request routing and isolation

The landing page loads a module Worker with `@php-wasm/web` and `@php-wasm/universal` 3.1.x. The Worker expands the archive with fflate and creates a PHPRequestHandler at the fixed application path. It serializes requests so one interpreter owns the SQLite file and session state.

The Service Worker routes application requests by browser client ID. A query token on the initial iframe navigation establishes its owner, then the landing page removes that query from the visible URL. The staged editor view passes the same token to its nested iframe, which otherwise has no parent client ID on its first navigation. Subsequent navigation, assets, and editor API calls use the requesting/replaced client ID. The landing page retains IDs so an evicted Service Worker can rediscover ownership without guessing between tabs. Browser cookies and PHP Set-Cookie headers never cross into the instance; PHPRequestHandler keeps its private jar. Application responses are `no-store` and carry a same-origin CSP.

Open the demo through the landing page. An independent tab opened directly at an application URL has no owner and receives 503. This PoC does not implement sharing or adopting such tabs. Closing/reloading the landing page ends its instance.

## Runtime configuration

`APP_NOT_INSTALLED=false`; a random APP_KEY is generated at build time. File sessions/cache live in the virtual filesystem, mail uses the log driver, and the queue is synchronous. Demo mode remains enabled for staff access. The new `PORTAL_DEMO_ALLOW_WRITES=true` setting enables real form creation/question updates; its default is false and existing demo behavior is retained when false. Demo mode grants every signed-in user every permission, so never set it to true on a shared server such as the current PHP-hosted demo: any visitor could change everyone's data. It is only safe here because each visitor's data stays in their own browser.

No fonts, scripts, analytics, or APIs are loaded from a CDN. The staged MarkdownEditor disables optional CDN extensions (highlight.js, KaTeX, Mermaid, ECharts, Prettier and image cropping/upload); basic Markdown editing remains available. These changes are confined to the staged copy under `.work/app`. The release checker is only used by the About action; the checkout's release-version placeholder makes it return before its HTTP call. The accepted flow does not reach that action. SMTP and external queues are not configured. Browser-origin restrictions are also enforced with CSP.

## Verification

After building, run the existing tests in the touched areas against another throwaway MySQL 8.4 instance:

```sh
node scripts/verify-mysql.mjs
```

This installs development Composer dependencies in a separate staged copy and runs GridMakers, Page search/service/controller, DemoMode, form editor, and FormsService tests with `PORTAL_ENABLE_DEMO_MODE=false`. It does not run the full suite.

Browser acceptance is exercised using Playwright MCP with Brave: landing, admin login, dashboard, staff dashboard, forms/circles/users/pages lists, form creation, editor question addition, and iframe reload. Execute `scripts/verify-browser.js` with Playwright MCP's `browser_run_code_unsafe` file argument after navigating to the landing URL. It returns a JSON verification record and saves screenshots under `dist/verification/`; save the returned record as `requests.json` in that directory. `dist/build-info.json` records archive/wasm sizes and conversion row counts; browser verification records runtime extensions and one time to a usable login screen.

## SQLite differences

- Page keyword searches use a title/body LIKE group for each whitespace-separated word; MySQL retains its original full-text Boolean search. SQLite does not emulate Boolean search syntax, MySQL collations, or full-text ranking.
- Grid datetime comparisons use SQLite strftime with minute precision; MySQL retains date_format.
- Grid null-safe inequality uses SQLite IS NOT; MySQL retains NOT (... <=> ?).
- Checkbox answer aggregation uses SQLite's two-argument GROUP_CONCAT; MySQL retains SEPARATOR syntax.
- CirclesGridMaker in 6.x uses eager-loaded relations and no longer has the old GROUP_CONCAT expression. Its backtick identifiers also work in SQLite.
- The converter maps MySQL scalar types to SQLite affinities, preserves nullability/defaults, primary/unique/ordinary indexes and foreign keys, and omits full-text/spatial indexes. MySQL enum/check constraints, column collation, unsigned limits and decimal precision are not emulated. The numeric answer pivot retains `CAST(... AS DECIMAL(10, 0))`; SQLite treats this as numeric affinity and does not enforce MySQL rounding, precision or range. That difference remains outside the accepted flow. Original migrations remain MySQL-only and must not be run in the browser.

Features beyond the verified flow, including uploads, spreadsheet exports, email delivery and direct application tabs, are outside this PoC's acceptance scope.

## Generated files

`dist/`, `.work/`, `.scratch/`, and `node_modules/` are ignored. Keep `dist/verification/` when retaining acceptance evidence. Stop the static server after verification. The staged builds and dependency directories may be removed after the static output has been delivered.
