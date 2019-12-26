<template>
  <div class="global-wrap">
    <global-navbar />
    <slot />
  </div>
</template>

<style lang="scss" scoped>
@import '@/sass/vue_app/_variables.scss';

.global-wrap {
  padding: $global-navbar-height 0 0 $global-sidebar-width;

  @media screen and (max-width: $breakpoint-lg) {
    padding-left: 0;
  }
}
</style>
