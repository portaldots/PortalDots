<template>
  <div class="navbar" :class="{ 'is-no-drawer': noDrawer }">
    <slot />
  </div>
</template>

<script>
export default {
  props: {
    noDrawer: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.navbar {
  align-items: center;
  background: $color-bg-white;
  box-shadow: 0 0 0.5rem rgba($color-text, 0.15);
  display: flex;
  height: $navbar-height;
  left: $drawer-width;
  padding: 0 $spacing;
  position: fixed;
  top: 0;
  width: $content-width;
  z-index: $z-index-navbar;
  &.is-no-drawer {
    left: 0;
    width: 100%;
  }

  @media screen and (max-width: $breakpoint-drawer-hide) {
    left: 0;
    width: 100%;
  }
  &-brand {
    color: $color-text;
    // ↓ナビバーがもつpaddingを打ち消すネガティブマージン
    margin-left: -$spacing;
    padding: $spacing;
    &:hover,
    &:active,
    &:focus {
      color: $color-text;
      text-decoration: none;
    }
  }
}
</style>
