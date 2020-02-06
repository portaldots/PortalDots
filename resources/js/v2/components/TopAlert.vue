<template>
  <div
    class="top_alert"
    :class="{
      'is-success': type === 'success',
      'is-primary': type === 'primary',
      'is-danger': type === 'danger'
    }"
  >
    <div class="top_alert__body">
      <h2 class="top_alert__title">
        <slot name="title" />
      </h2>
      <p class="top_alert__message">
        <slot />
      </p>
    </div>
    <div class="top_alert__cta">
      <slot name="cta" />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    type: {
      type: String,
      default: 'primary'
    }
  }
}
</script>

<style lang="scss" scoped>
.top_alert {
  align-items: center;
  color: #fff;
  display: flex;
  justify-content: space-between;
  padding: $spacing;
  @media screen and (max-width: $breakpoint-top-alert-col) {
    flex-direction: column;
    text-align: center;
  }
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
  }
  &__message {
    font-size: 1rem;
    margin: $spacing-sm 0 0;
  }
}
</style>
