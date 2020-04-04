<template>
  <a :href="href" class="navbar-back" :class="{ 'is-inverse': inverse }">
    <i class="fas fa-chevron-left navbar-back__icon"></i>
    <slot />
  </a>
</template>

<script>
export default {
  props: {
    href: {
      type: String,
      required: true
    },
    inverse: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.navbar-back {
  align-items: center;
  display: flex;
  // ↓ナビバーがもつpaddingを打ち消すネガティブマージン
  margin-left: -$spacing;
  padding: $spacing;
  &__icon {
    font-size: 1.25rem;
    margin-right: $spacing-sm;
  }
  &:hover,
  &:active,
  &:focus {
    text-decoration: none;
  }
  &.is-inverse {
    color: $color-bg-light;
    &:hover,
    &:active,
    &:focus {
      opacity: 0.75;
    }
  }
}
</style>
