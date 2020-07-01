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
          top: `${menuTop}px` || 'auto',
          left: `${menuLeft}px` || 'auto',
          bottom: `${menuBottom}px` || 'auto',
          right: `${menuRight}px` || 'auto'
        }"
      >
        <div v-for="(item, index) in items" :key="item.key" ref="menuItems">
          <template v-if="item.sublist && Array.isArray(item.sublist)">
            <AppDropdownItem
              component-is="button"
              @click.stop="() => openSubmenu(index)"
              @mouseover="() => onMouseoverItem(index)"
            >
              <div class="dropdown-menu__has-submenu">
                <div>{{ item.label }}</div>
                <i class="fas fa-caret-right"></i>
              </div>
            </AppDropdownItem>
          </template>
          <template v-else @mouseover="onMouseoutItem">
            <slot name="item" :item="item" />
          </template>
        </div>
      </div>
      <div
        class="dropdown-menu"
        v-if="openingSubmenuIndex !== null && isOpen"
        @click="close"
        ref="submenu"
        :style="{
          top: submenuTop,
          left: submenuLeft,
          width: submenuWidth || 'auto',
          height: submenuHeight || 'auto'
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
import AppDropdownItem from './AppDropdownItem.vue'

export default {
  components: {
    AppDropdownItem
  },
  data() {
    return {
      isOpen: false,
      menuTop: null,
      menuLeft: null,
      menuBottom: null,
      menuRight: null,
      openingSubmenuIndex: null,
      submenuTop: '0',
      submenuLeft: '0',
      submenuWidth: null,
      submenuHeight: null,
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
  methods: {
    async toggle() {
      this.isOpen = !this.isOpen
      this.openingSubmenuIndex = null

      if (!this.isOpen) {
        window.document.body.style.overflowY = 'visible'
        return
      }

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
    async openSubmenu(index) {
      if (this.timeoutIdForSubmenu) {
        window.clearTimeout(this.timeoutIdForSubmenu)
        this.timeoutIdForSubmenu = null
      }

      this.openingSubmenuIndex = index

      const refParentItem = this.$refs.menuItems[index]

      this.submenuTop = `${refParentItem.getBoundingClientRect().top - 3}px`
      this.submenuLeft = `${refParentItem.getBoundingClientRect().right}px`

      await this.$nextTick()

      const refSubmenu = this.$refs.submenu

      // メニューが画面のX方向からはみ出してしまうようであれば、表示する向きを逆にする
      if (
        refParentItem.getBoundingClientRect().right + refSubmenu.clientWidth >
        window.innerWidth
      ) {
        this.submenuLeft = `${refParentItem.getBoundingClientRect().left -
          refSubmenu.clientWidth}px`
      }

      // メニューが画面のY方向からはみ出してしまうようであれば、top の値を調整する

      // 画面の端とメニューがくっつかないよう、space ぶんの余裕をもたせる
      const space = 20

      if (
        refSubmenu.getBoundingClientRect().bottom + refSubmenu.clientHeight >
        window.innerHeight
      ) {
        this.submenuTop = `${window.innerHeight -
          refSubmenu.clientHeight -
          space}px`
      }
    },
    closeSubmenu() {
      if (this.timeoutIdForSubmenu) {
        window.clearTimeout(this.timeoutIdForSubmenu)
        this.timeoutIdForSubmenu = null
      }

      this.openingSubmenuIndex = null
    },
    onMouseoverItem(index) {
      this.timeoutIdForSubmenu = window.setTimeout(
        () => this.openSubmenu(index),
        500
      )
    },
    onMouseoutItem() {
      if (this.openingSubmenuIndex === null) return

      this.timeoutIdForSubmenu = window.setTimeout(
        () => this.closeSubmenu(),
        500
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
    &__has-submenu {
      display: flex;
      justify-content: space-between;
    }
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
