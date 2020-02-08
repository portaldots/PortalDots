<template>
  <div
    class="top_alert"
    :class="{
      'is-success': type === 'success',
      'is-primary': type === 'primary',
      'is-danger': type === 'danger'
    }"
  >
    <AppContainer
      class="top_alert__container"
      :narrow="containerNarrow"
      :medium="containerMedium"
    >
      <div class="top_alert__body">
        <h2 class="top_alert__title" v-if="$slots.title">
          <slot name="title" />
        </h2>
        <p class="top_alert__message" v-if="$slots.default">
          <slot />
        </p>
      </div>
      <div class="top_alert__cta" v-if="$slots.cta">
        <slot name="cta" />
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
    type: {
      type: String,
      default: 'primary'
    },
    containerNarrow: {
      type: Boolean,
      default: false
    },
    containerMedium: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.top_alert {
  & + & {
    border-top: 1px solid rgba(#fff, 0.16);
  }
  &.is-success {
    background: $color-success;
  }
  &.is-primary {
    background: $color-primary;
  }
  &.is-danger {
    background: $color-danger;
  }
  &__container {
    align-items: center;
    color: #fff;
    display: flex;
    justify-content: space-between;
    padding: $spacing;

    @media screen and (max-width: $breakpoint-top-alert-col) {
      flex-direction: column;
    }
  }
  &__body {
    padding-right: $spacing;
    @media screen and (max-width: $breakpoint-top-alert-col) {
      padding: 0;
    }
  }
  &__cta {
    @media screen and (max-width: $breakpoint-top-alert-col) {
      padding-top: $spacing;
    }
  }
  &__title {
    font-size: 1rem;
    font-weight: bold;
    margin: 0;
    @media screen and (max-width: $breakpoint-top-alert-col) {
      text-align: center;
    }
  }
  &__message {
    font-size: 1rem;
    margin: $spacing-sm 0 0;
  }
}
</style>
