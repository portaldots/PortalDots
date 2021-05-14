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
  .options({
    hmrOptions: {
      host: 'localhost',
      port: 8181
    }
  })
  .webpackConfig({
    resolve: {
      modules: [path.resolve('./node_modules')]
    }
  })
  .js('resources/js/app.js', 'public/js') // メインスクリプト
  .vue({
    globalStyles: 'resources/sass/_variables.scss'
    // ↓スタイル適用順序に依存したCSSを書いているVueファイルが多く存在しており、
    //   場合によって表示が崩れてしまうことがあるため、一時的にコメントアウト
    // extractStyles: true,
  })
  .extract([
    'axios',
    'bootstrap',
    'marked',
    'turbolinks',
    'vue',
    'vue-global-events',
    'vuex'
  ])
  .sass('resources/sass/bootstrap.scss', 'public/css') // Bootstrap
  .sass('resources/sass/fontawesome.scss', 'public/css') // Font Awesome
  // ↓ CodeIgniterを廃止したらこの行は削除する
  .sass('resources/sass/fontawesome.v4.scss', 'public/css') // Font Awesome Ver4
  .sass('resources/sass/app.scss', 'public/css') // メインスタイル
  // .browserSync({
  //   proxy: 'localhost',
  //   snippetOptions: {
  //     rule: {
  //       // これがないと Turbolinks が正常に動作しない
  //       match: /<\/head>/i,
  //       fn(snippet, match) {
  //         return snippet + match
  //       }
  //     }
  //   }
  // })
  .sourceMaps()
  .version()
