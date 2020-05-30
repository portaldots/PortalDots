<template>
  <span
    class="badge"
    :class="{
      'is-outline': outline,
      'is-primary': primary,
      'is-danger': danger,
      'is-success': success,
      'is-muted': muted,
      'is-strong': strong,
      'is-small': small
    }"
  >
    <slot />
  </span>
</template>

<script>
export default {
  props: {
    outline: {
      type: Boolean,
      default: false
    },
    primary: {
      type: Boolean,
      default: false
    },
    danger: {
      type: Boolean,
      default: false
    },
    success: {
      type: Boolean,
      default: false
    },
    muted: {
      type: Boolean,
      default: false
    },
    strong: {
      type: Boolean,
      default: false
    },
    small: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.badge {
  border-radius: $border-radius;
  display: inline-block;
  font-size: 0.75em;
  line-height: 1.75;
  padding: 0 0.4em;
  text-align: center;
  @each $state, $color in $semantic-colors {
    &.is-outline.is-#{$state} {
      border: 1px solid $color;
      color: $color;
    }
    &:not(.is-outline).is-#{$state} {
      background: $color;
      color: #fff;
    }
  }
  &.is-strong {
    font-weight: bolder;
  }
  &.is-small {
    font-size: 80%;
  }
}
</style>
