const mix = require('laravel-mix')

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

const path = require('path')

mix
  .webpackConfig({
    resolve: {
      modules: [path.resolve('./node_modules')]
    }
  })
  .options({
    globalVueStyles: 'resources/sass/v2/_variables.scss'
    // ↓申請フォームエディターのレイアウトが崩れてしまうため、
    // purifyCss: true の指定は、一時的にコメントアウトしています
    // purifyCss: true
  })
  .js('resources/js/app.js', 'public/js') // メインスクリプト
  .sass('resources/sass/app.scss', 'public/css') // メインスタイル
  .sass('resources/sass/v2/app.scss', 'public/css/v2') // メインスタイル(v2)
  .js('resources/js/v2/app.js', 'public/js/v2') // メインスクリプト(v2)
  .js('resources/js/users_checker.js', 'public/js') // ユーザー登録チェッカー
  .js('resources/js/forms_editor/index.js', 'public/js/forms_editor') // フォームエディタJS
  .sass('resources/sass/forms_editor.scss', 'public/css') // フォームエディタCSS
  .browserSync({
    proxy: 'localhost',
    snippetOptions: {
      rule: {
        // これがないと Turbolinks が正常に動作しない
        match: /<\/head>/i,
        fn(snippet, match) {
          return snippet + match
        }
      }
    }
  })
  .sourceMaps()
  .version()
