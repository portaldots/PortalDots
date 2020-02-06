<template>
  <component
    :is="href ? 'a' : 'div'"
    class="listview-base-item"
    v-bind="href ? { href } : {}"
    :target="newtab ? '_blank' : undefined"
    :rel="newtab ? 'noopener' : undefined"
  >
    <slot />
  </component>
</template>

<script>
export default {
  props: {
    href: {
      type: String,
      default: null
    },
    newtab: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.listview-base-item {
  background: $color-bg-white;
  border-bottom: 1px solid $color-border;
  box-shadow: 0 0.5rem 0.5rem rgba($color-text, 0.05);
  color: $color-text;
  display: block;
  margin: 0;
  padding: $spacing-md $spacing;
  position: relative;
  width: 100%;
  @media screen and (max-width: $breakpoint-listview-sm) {
    box-shadow: none;
    &:first-child {
      border-top: 1px solid $color-border;
    }
  }
  @media screen and (min-width: $breakpoint-listview-sm) {
    &:first-child {
      border-top-left-radius: $border-radius;
      border-top-right-radius: $border-radius;
    }
    &:last-child {
      border-bottom: 0;
      border-bottom-left-radius: $border-radius;
      border-bottom-right-radius: $border-radius;
    }
  }
  &:hover,
  &:active,
  &:focus {
    background: $color-bg-light;
    color: $color-text;
    text-decoration: none;
  }
  &:not(a):hover,
  &:not(a):active,
  &:not(a):focus {
    background: $color-bg-white;
  }
  &.is-action-btn {
    align-items: center;
    color: $color-primary;
    display: flex;
    flex-direction: column;
    font-weight: bold;
    justify-content: center;
    padding-bottom: $spacing;
    padding-top: $spacing;
  }
  &__title {
    font-size: 1.1rem;
    font-weight: bold;
    margin: 0;
  }
  &__meta {
    font-size: 1rem;
    margin: 0;
  }
  &__body {
    color: $color-muted;
    font-size: 1rem;
    margin: $spacing-xs 0 0;
  }
}
</style>
