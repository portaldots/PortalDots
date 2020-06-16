<template>
  <span
    class="badge"
    :class="{
      'is-outline': outline,
      'is-primary': primary,
      'is-danger': danger,
      'is-success': success,
      'is-warning': warning,
      'is-muted': muted,
      'is-strong': strong,
      'is-small': small,
      'is-pill': pill
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
    warning: {
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
    },
    pill: {
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
      // outlineバッジに比べ一回り小さく見えてしまわないように、
      // outlineバッジと同様のborderをつける
      border: 1px solid $color;
      color: #fff;
    }
  }
  &.is-strong {
    font-weight: bolder;
  }
  &.is-small {
    font-size: 80%;
  }
  &.is-pill {
    border-radius: 9999px;
    padding: 0 0.6em;
  }
}
</style>
