/* eslint-disable eqeqeq */

/*!
 * jeffreyguenther/vue-turbolinks を一部改変したもの
 * https://github.com/jeffreyguenther/vue-turbolinks
 *
 * MIT License
 */

function handleVueDestructionOn(turbolinksEvent, vue) {
  document.addEventListener(turbolinksEvent, function teardown() {
    vue.$destroy()
    document.removeEventListener(turbolinksEvent, teardown)
  })
}

function plugin(Vue) {
  // Install a global mixin
  Vue.mixin({
    beforeMount() {
      // If this is the root component, we want to cache the original element contents to replace later
      // We don't care about sub-components, just the root
      if (this == this.$root && this.$el) {
        const destroyEvent =
          this.$options.turbolinksDestroyEvent || 'turbolinks:visit'
        handleVueDestructionOn(destroyEvent, this)
        this.$originalEl = this.$el.outerHTML
      }
    },

    destroyed() {
      // 以下のコメントアウト部分 : We only need to revert the html for the root component
      if (this == this.$root && this.$el) {
        // ページ移動時、レイアウトが崩れるのを防ぐため、コメントアウト
        // this.$el.outerHTML = this.$originalEl

        // Turbolinks のキャッシュが表示されてから、サーバーのコンテンツが表示されるまでの間に
        // input タグの内容が変わってしまうと、サーバーのコンテンツが表示された際に、
        // 入力された内容が消えてしまうため、キャッシュ表示時はユーザーに
        // 文字入力などをさせないようにする
        const inputs = document.querySelectorAll(
          'input, select, textarea, button'
        )
        /* eslint-disable no-restricted-syntax */
        for (const input of inputs) {
          input.disabled = 'disabled'
        }
        /* eslint-enable */
      }
    }
  })
}

export default plugin
