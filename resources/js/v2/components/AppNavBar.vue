<template>
  <div class="navbar" :class="{ 'is-no-drawer': noDrawer, 'is-staff': staff }">
    <slot />
  </div>
</template>

<script>
export default {
  props: {
    noDrawer: {
      type: Boolean,
      default: false,
    },
    staff: {
      type: Boolean,
      default: false,
    },
  },
};
</script>

<style lang="scss" scoped>
.navbar {
  align-items: center;
  background: $color-bg-surface-2;
  box-shadow: $box-shadow-lv1;
  display: flex;
  height: $navbar-height;
  left: $drawer-width;
  padding: 0 $spacing;
  position: fixed;
  right: 0;
  top: 0;
  z-index: $z-index-navbar;
  &.is-no-drawer {
    left: 0;
    width: 100%;
  }
  &.is-staff {
    &::after {
      border: 1px solid $color-muted;
      border-radius: $border-radius;
      color: $color-muted;
      content: "スタッフ";
      display: inline-block;
      font-size: 0.75em;
      line-height: 1.75;
      padding: 0 0.4em;
      position: absolute;
      right: $spacing;
      top: 50%;
      transform: translateY(-50%);
    }
  }

  @media screen and (max-width: $breakpoint-drawer-narrow) {
    left: $drawer-width-narrow;
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
