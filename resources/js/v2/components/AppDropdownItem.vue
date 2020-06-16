<template>
  <component
    :is="componentIs"
    class="dropdown-item"
    :tabindex="['a', 'button'].includes(componentIs) ? 0 : undefined"
  >
    <slot />
  </component>
</template>

<script>
export default {
  props: {
    componentIs: {
      type: String,
      default: 'div'
    }
  }
}
</script>

<style lang="scss" scoped>
.dropdown-item {
  appearance: none;
  color: $color-text;
  cursor: pointer;
  display: block;
  line-height: 1.15;
  max-width: 100%;
  min-width: 15rem;
  padding: 0.75rem $spacing-md;
  transition: #{$transition-base} background-color, #{$transition-base} color;
  &:not(a):not(button) {
    cursor: auto;
  }
  &:hover,
  &:focus,
  &:active {
    color: $color-text;
    text-decoration: none;
  }
  &:hover {
    background: $color-bg-light;
  }
  &:focus {
    background: $color-primary;
    color: $color-bg-white;
    outline: 0;
  }
}
</style>
