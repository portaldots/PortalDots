<template>
  <div class="large-header">
    <slot />
  </div>
</template>

<style lang="scss" scoped>
@import '@/sass/vue_app/_variables.scss';

.large-header {
  padding: 4rem #{$base-padding};
}
</style>
