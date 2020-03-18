<template>
  <div class="app-header">
    <AppContainer>
      <h1 class="app-header__title">
        <slot name="title" />
      </h1>
      <div class="app-header__body">
        <slot />
      </div>
    </AppContainer>
  </div>
</template>

<script>
import AppContainer from './AppContainer.vue'

export default {
  components: {
    AppContainer
  }
}
</script>

<style lang="scss" scoped>
.app-header {
  background: #fff;
  border-bottom: 1px solid $color-border;
  margin: 0;
  padding: $spacing 0;
  &__title {
    font-size: $font-size-lg;
    margin: 0 0 $spacing-sm;
  }
  &__body {
    margin: 0;
  }
}
</style>
