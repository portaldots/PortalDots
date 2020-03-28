const mix = require('laravel-mix')

require('laravel-mix-polyfill')

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
  .js('resources/js/app.js', 'public/js') // メインスクリプト
  .extract([
    'axios',
    'bootstrap',
    'marked',
    'turbolinks',
    'vue',
    'vue-global-events',
    'vuex'
  ])
  .options({
    globalVueStyles: 'resources/sass/_variables.scss'
    // ↓フォームエディターのレイアウトが崩れてしまうため、
    // purifyCss: true の指定は、一時的にコメントアウトしています
    // purifyCss: true
  })
  .polyfill({
    enabled: true,
    useBuiltIns: 'usage',
    targets: { ie: 11 }
  })
  .sass('resources/sass/bootstrap.scss', 'public/css') // Bootstrap
  .sass('resources/sass/app.scss', 'public/css') // メインスタイル
  .options({
    // ↓スタイル適用順序に依存したCSSを書いているVueファイルが多く存在しており、
    // 場合によって表示が崩れてしまうことがあるため、一時的にコメントアウト
    // extractVueStyles: true
  })
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
