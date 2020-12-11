<template>
  <div class="dropdown" ref="container">
    <GlobalEvents @keyup.esc="close" v-if="isOpen" />
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
          top: menuTop !== null ? `${menuTop}px` : 'auto',
          left: menuLeft !== null ? `${menuLeft}px` : 'auto',
          right: menuRight !== null ? `${menuRight}px` : 'auto',
          bottom: menuBottom !== null ? `${menuBottom}px` : 'auto'
        }"
        :key="name"
      >
        <div v-for="(item, index) in items" :key="item.key" ref="menuItems">
          <template v-if="item.sublist && Array.isArray(item.sublist)">
            <AppDropdownItem
              component-is="button"
              @click.stop="() => openSubmenu(index)"
              @mouseover="() => onMouseoverItemToOpenSubmenu(index)"
              class="dropdown-menu__submenu-opener"
              :class="{ 'is-open-submenu': index === openingSubmenuIndex }"
            >
              <div>{{ item.label }}</div>
              <i class="fas fa-caret-right"></i>
            </AppDropdownItem>
          </template>
          <div v-else @mouseover="onMouseoutItemToCloseSubmenu">
            <slot name="item" :item="item" />
          </div>
        </div>
      </div>
      <div
        class="dropdown-menu"
        v-if="openingSubmenuIndex !== null && isOpen"
        @click="close"
        @mouseover="onMouseoverSubmenu"
        ref="submenu"
        :style="{
          top: submenuTop !== null ? `${submenuTop}px` : 'auto',
          left: submenuLeft !== null ? `${submenuLeft}px` : 'auto',
          right: submenuRight !== null ? `${submenuRight}px` : 'auto',
          bottom: submenuBottom !== null ? `${submenuBottom}px` : 'auto'
        }"
      >
        <div v-for="item in items[openingSubmenuIndex].sublist" :key="item.key">
          <slot name="item" :item="item" />
        </div>
      </div>
    </portal>
  </div>
</template>

<script>
import GlobalEvents from 'vue-global-events'
import AppDropdownItem from './AppDropdownItem.vue'

export default {
  components: {
    GlobalEvents,
    AppDropdownItem
  },
  data() {
    return {
      isOpen: false,
      menuTop: null,
      menuLeft: null,
      menuRight: null,
      menuBottom: null,
      openingSubmenuIndex: null,
      submenuTop: null,
      submenuLeft: null,
      submenuRight: null,
      submenuBottom: null,
      timeoutIdForSubmenu: null,
      isMouseoverSubmenu: false
    }
  },
  props: {
    items: {
      // 各要素は {key: String} が必須
      // 各要素中に {sublist: Array, label: String} を含めると、サブメニュー付きになる
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
  mounted() {
    window.addEventListener('click', this.onClickOutside)
  },
  destroyed() {
    window.removeEventListener('click', this.onClickOutside)
  },
  methods: {
    async toggle() {
      if (this.isOpen) {
        this.close()
      } else {
        await this.open()
      }
    },
    async open() {
      this.isOpen = true
      this.openingSubmenuIndex = null

      window.document.body.style.overflowY = 'hidden'

      this.menuTop = null
      this.menuLeft = null
      this.menuBottom = null
      this.menuRight = null

      // メニュー本体部分の DOM を取得するため、まずメニュー本体を DOM 上に描画する
      await this.$nextTick()

      const refButton = this.$refs.button
      const refMenu = this.$refs.menu

      // fluid の場合、ボタンの幅にメニュー幅を揃える
      if (this.menuFluid) {
        this.menuRight =
          window.innerWidth - refButton.getBoundingClientRect().right
      }

      // とりあえずボタン下にメニューを表示

      this.menuLeft = refButton.getBoundingClientRect().left
      this.menuTop = refButton.getBoundingClientRect().bottom + 3

      await this.$nextTick()

      // メニューが画面からはみ出してしまうようであれば、メニュー内でスクロールできるようにする

      const space = 20 // 画面の端とメニューがくっつかないよう、space ぶんの余裕をもたせる

      const normalMenuHeight = refMenu.getBoundingClientRect().height

      this.menuBottom = Math.max(
        space,
        window.innerHeight - refMenu.getBoundingClientRect().bottom
      )

      const compressedMenuHeight =
        window.innerHeight - this.menuBottom - this.menuTop

      if (
        this.menuBottom === space &&
        compressedMenuHeight < 0.2 * window.innerHeight
      ) {
        // メニューの高さがギリギリになってしまう場合、メニューはボタンより上に表示する
        this.menuBottom =
          window.innerHeight - refButton.getBoundingClientRect().top - 3
        this.menuTop = Math.max(
          space,
          window.innerHeight - this.menuBottom - normalMenuHeight
        )
      }
    },
    close() {
      window.document.body.style.overflowY = 'visible'
      this.isOpen = false
    },
    onClickOutside(e) {
      if (this.isOpen && !this.$refs.container.contains(e.target)) {
        this.close()
      }
    },
    async openSubmenu(index) {
      if (this.timeoutIdForSubmenu) {
        window.clearTimeout(this.timeoutIdForSubmenu)
        this.timeoutIdForSubmenu = null
      }

      if (this.openingSubmenuIndex === index) {
        return
      }

      this.openingSubmenuIndex = index

      const refParentItem = this.$refs.menuItems[index]

      this.submenuTop = refParentItem.getBoundingClientRect().top - 10
      this.submenuLeft = refParentItem.getBoundingClientRect().right
      this.submenuRight = null
      this.submenuBottom = null

      await this.$nextTick()

      const refSubmenu = this.$refs.submenu

      // メニューが画面のX方向からはみ出してしまうようであれば、表示する向きを逆にする
      if (refSubmenu.getBoundingClientRect().right > window.innerWidth) {
        this.submenuLeft = null
        this.submenuRight =
          window.innerWidth - refParentItem.getBoundingClientRect().left
      }

      await this.$nextTick()

      // メニューが画面のY方向からはみ出してしまうようであれば、メニューの高さを調整する

      const space = 20 // 画面の端とメニューがくっつかないよう、space ぶんの余裕をもたせる

      const normalMenuHeight = refSubmenu.getBoundingClientRect().height

      this.submenuBottom = Math.max(
        space,
        window.innerHeight - refSubmenu.getBoundingClientRect().bottom
      )

      const compressedMenuHeight =
        window.innerHeight - this.submenuBottom - this.submenuTop

      if (
        this.submenuBottom === space &&
        compressedMenuHeight < 0.2 * window.innerHeight
      ) {
        // メニューの高さがギリギリになってしまう場合、メニューは上方向に表示する
        this.submenuBottom =
          window.innerHeight - refParentItem.getBoundingClientRect().bottom - 10
        this.submenuTop = Math.max(
          space,
          window.innerHeight - this.submenuBottom - normalMenuHeight
        )
      }
    },
    closeSubmenu() {
      if (this.timeoutIdForSubmenu) {
        window.clearTimeout(this.timeoutIdForSubmenu)
        this.timeoutIdForSubmenu = null
      }

      this.openingSubmenuIndex = null
    },
    onMouseoverItemToOpenSubmenu(index) {
      if (this.openingSubmenuIndex === index) {
        return
      }

      if (this.timeoutIdForSubmenu) {
        window.clearTimeout(this.timeoutIdForSubmenu)
      }

      this.timeoutIdForSubmenu = window.setTimeout(
        () => this.openSubmenu(index),
        300
      )
    },
    onMouseoverSubmenu() {
      if (this.timeoutIdForSubmenu) {
        window.clearTimeout(this.timeoutIdForSubmenu)
      }
      this.timeoutIdForSubmenu = null
    },
    onMouseoutItemToCloseSubmenu() {
      if (this.timeoutIdForSubmenu) {
        window.clearTimeout(this.timeoutIdForSubmenu)
      }

      if (this.openingSubmenuIndex === null) return

      this.timeoutIdForSubmenu = window.setTimeout(
        () => this.closeSubmenu(),
        300
      )
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
    overflow: auto;
    overflow-x: hidden;
    padding: $spacing-sm 0;
    position: fixed;
    z-index: $z-index-dropdown-menu;
    &__submenu-opener {
      display: flex;
      justify-content: space-between;
      &.is-open-submenu {
        background: $color-primary;
        color: $color-bg-white;
      }
    }
  }
}
</style>
