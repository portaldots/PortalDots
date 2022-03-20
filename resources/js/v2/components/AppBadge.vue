<template>
  <span
    :title="title"
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
    title: {
      type: String,
      default: ''
    },
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
  align-items: center;
  border-radius: $border-radius-sm;
  display: inline-flex;
  font-size: 0.75em;
  font-weight: 500;
  height: 1.75em;
  justify-content: center;
  line-height: 1.2;
  padding: 0 0.4rem;
  text-align: center;
  @each $state, $color in $semantic-colors {
    &.is-outline.is-#{$state} {
      border: 1px solid $color;
      color: $color;
    }
    &:not(.is-outline).is-#{$state} {
      $bg-color: map-get($semantic-light-colors, $state);

      background: $bg-color;
      // outlineバッジに比べ一回り小さく見えてしまわないように、
      // outlineバッジと同様のborderをつける
      color: $color;
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
