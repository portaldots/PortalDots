<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title')</title>

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #304554;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .content {
                text-align: center;
            }

            .app-name {

            }

            .app-name a {
                color: #304554;
                text-decoration: none;
            }

            .app-name a:hover {
                text-decoration: underline;
            }

            .title {
                font-size: 36px;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <div class="app-name">
                    <a href="{{ url('/') }}">
                        {{ config('app.name') }}
                    </a>
                </div>
                <div class="title">
                    @yield('message')
                </div>
            </div>
        </div>
    </body>
</html>
