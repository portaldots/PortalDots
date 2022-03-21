<template>
  <div
    class="info-box"
    :class="{
      'is-primary': primary,
      'is-danger': danger
    }"
  >
    <div class="info-box-icon">
      <i
        class="fas"
        :class="{
          'fa-info-circle': primary,
          'fa-exclamation-triangle': danger
        }"
      ></i>
    </div>
    <div class="info-box-content">
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    primary: {
      type: Boolean,
      default: false
    },
    danger: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.info-box {
  border-radius: $border-radius;
  display: flex;
  padding: $spacing-md $spacing-s;
  &-icon {
    display: flex;
    flex: 0 0 1.5rem;
    opacity: 0.75;
    padding-top: 0.35rem;
  }
  &-content {
    flex: 1;
  }
  &.is-primary {
    background-color: $color-primary-light;
    i {
      color: $color-primary;
    }
  }
  &.is-danger {
    background-color: $color-danger-light;
    i {
      color: $color-danger;
    }
  }
}
</style>
