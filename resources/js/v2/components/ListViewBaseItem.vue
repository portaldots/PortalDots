<template>
  <component
    :is="componentIs"
    class="listview-base-item"
    :class="{ 'is-no-border': noBorder }"
    v-bind="href ? { href } : {}"
    :target="newtab ? '_blank' : undefined"
    :rel="newtab ? 'noopener' : undefined"
    :type="submit ? 'submit' : undefined"
    @click="onClick"
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
    },
    button: {
      type: Boolean,
      default: false
    },
    submit: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    componentIs() {
      if (this.button) return 'button'
      return this.href ? 'a' : 'div'
    }
  },
  methods: {
    onClick(e) {
      this.$emit('click', e)
    }
  }
}
</script>

<style lang="scss" scoped>
@use "sass:math";

.listview-base-item {
  $listview-border: 1px solid $color-border;

  background-color: transparent;
  border: 0;
  border-radius: $border-radius;
  color: $color-text;
  cursor: pointer;
  display: block;
  margin: $spacing-sm 0;
  outline-offset: -3px;
  padding: $spacing-sm math.div($spacing, 2);
  position: relative;
  transition: #{$transition-base-fast} background-color;
  width: 100%;
  &:not(a):not(button) {
    cursor: auto;
  }
  &:hover,
  &:active,
  &:focus {
    background: $color-bg-light;
    color: $color-text;
    text-decoration: none;
  }
  &:not(a):not(button):hover,
  &:not(a):not(button):active,
  &:not(a):not(button):focus {
    background: $color-bg-base;
  }
  &.is-action-btn {
    align-items: center;
    appearance: none;
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
