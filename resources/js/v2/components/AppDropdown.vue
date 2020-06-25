<template>
  <div class="dropdown">
    <div class="dropdown-backdrop" v-if="isOpen" @click="close"></div>
    <div class="dropdown-button" ref="button">
      <slot name="button" :toggle="toggle" :props="ariaButtonProps" />
    </div>
    <portal to="portal-target">
      <!-- portal タグの中の HTML は、app.blade.php と no_drawer.blade.php にある portal-target タグ内にレンダリングされる-->
      <!-- ここでいう portal と PortalDots の portal は無関係 -->
      <div
        class="dropdown-menu"
        v-if="isOpen"
        :aria-labelledby="`dropdown-button-${name}`"
        @click="close"
        ref="menu"
        :style="{
          top: menuTop,
          left: menuLeft,
          width: menuWidth || 'auto',
          height: menuHeight || 'auto'
        }"
      >
        <div v-for="item in items" :key="item.key">
          <slot name="item" :item="item" />
        </div>
      </div>
    </portal>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isOpen: false,
      menuTop: '0',
      menuLeft: '0',
      menuWidth: null,
      menuHeight: null
    }
  },
  props: {
    items: {
      // 各要素は {key: String} が必須
      type: Array,
      required: true
    },
    name: {
      // ドロップダウンを識別するユニークな名前
      type: String,
      required: true
    },
    menuFluid: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    async toggle() {
      this.isOpen = !this.isOpen
      this.menuHeight = null

      if (!this.isOpen) {
        window.document.body.style.overflowY = 'visible'
        return
      }

      window.document.body.style.overflowY = 'hidden'

      await this.$nextTick()

      const refButton = this.$refs.button
      const refMenu = this.$refs.menu

      // fluid の場合、ボタンの幅にメニュー幅を揃える
      if (this.menuFluid) {
        this.menuWidth = `${refButton.clientWidth}px`
      }

      // 1) とりあえずボタン下にメニューを表示して様子見

      this.menuLeft = `${refButton.getBoundingClientRect().left}px`
      this.menuTop = `${refButton.getBoundingClientRect().bottom + 3}px`

      await this.$nextTick()

      // 2) メニューが画面からはみ出してしまうようであれば、メニュー内でスクロールできるようにする

      if (
        refMenu.getBoundingClientRect().top + refMenu.clientHeight >
        window.innerHeight
      ) {
        const menuHeight =
          window.innerHeight - refMenu.getBoundingClientRect().top
        if (menuHeight > window.innerHeight * 0.3) {
          this.menuHeight = `${menuHeight}px`
        } else {
          // メニューの高さがギリギリになってしまう場合、メニューはボタンより上に表示する
          const top = Math.max(
            0,
            refButton.getBoundingClientRect().top - refMenu.clientHeight - 3
          )
          this.menuTop = `${top}px`
          if (top === 0) {
            this.menuHeight = `${refButton.getBoundingClientRect().top - 3}px`
          }
        }
      }
    },
    close() {
      window.document.body.style.overflowY = 'visible'
      this.isOpen = false
    }
  },
  computed: {
    ariaButtonProps() {
      return {
        id: `dropdown-button-${this.name}`,
        'aria-haspopup': true,
        'aria-expanded': this.isOpen
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.dropdown {
  display: inline-block;
  position: relative;
  &-menu {
    background: $color-bg-white;
    border-radius: $border-radius;
    box-shadow: 0 0.4rem 0.8rem 0.1rem rgba($color-text, 0.25);
    left: 0;
    overflow: auto;
    overflow-x: hidden;
    padding: $spacing-sm 0;
    position: fixed;
    z-index: $z-index-dropdown-menu;
  }
  &-backdrop {
    bottom: 0;
    left: 0;
    position: fixed;
    right: 0;
    top: 0;
    z-index: $z-index-dropdown-backdrop;
  }
}
</style>
