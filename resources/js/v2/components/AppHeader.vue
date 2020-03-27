<template>
  <div class="app-header" :class="{ 'is-text-center': textCenter }">
    <AppContainer :narrow="containerNarrow" :medium="containerMedium">
      <h1 class="app-header__title">
        <slot name="title" />
      </h1>
      <div class="app-header__body" v-if="$slots.default">
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
  },
  props: {
    containerNarrow: {
      type: Boolean,
      default: false
    },
    containerMedium: {
      type: Boolean,
      default: false
    },
    textCenter: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.app-header {
  background: #fff;
  border-bottom: 1px solid $color-border;
  margin: 0;
  padding: $spacing 0;
  &.is-text-center {
    text-align: center;
  }
  &__title {
    font-size: $font-size-xl;
    font-weight: normal;
    margin: 0;
  }
  &__body {
    margin: $spacing-sm 0 0;
  }
}
</style>
