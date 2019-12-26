<template>
  <header class="global-navbar"></header>
</template>

<style lang="scss" scoped>
@import '@/sass/vue_app/_variables.scss';

.global-navbar {
  box-shadow: $base-box-shadow;
  height: $global-navbar-height;
  left: $global-sidebar-width;
  position: fixed;
  top: 0;
  width: calc(100vw - #{$global-sidebar-width});
  z-index: $z-index-global-navbar;

  @media screen and (max-width: $breakpoint-lg) {
    left: 0;
    width: 100vw;
  }
}
</style>
