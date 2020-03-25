<template>
  <component
    :is="href ? 'a' : 'div'"
    class="listview-base-item"
    :class="{ 'is-no-border': noBorder }"
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
    },
    noBorder: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.listview-base-item {
  background: $color-bg-white;
  color: $color-text;
  display: block;
  margin: 0;
  padding: $spacing-s $spacing;
  position: relative;
  width: 100%;
  &::after {
    border-bottom: $listview-border;
    bottom: 0;
    content: '';
    display: block;
    height: 1px;
    left: $spacing;
    position: absolute;
    right: 0;
  }
  &.is-no-border::after {
    display: none;
  }
  &:first-child {
    border-top-left-radius: $border-radius;
    border-top-right-radius: $border-radius;
  }
  &:last-child {
    border-bottom-left-radius: $border-radius;
    border-bottom-right-radius: $border-radius;
    &::after {
      display: none;
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
