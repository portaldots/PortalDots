<template>
  <component
    :is="componentIs"
    :type="submit ? 'submit' : undefined"
    :href="href"
    :title="title"
    :target="newtab ? '_blank' : undefined"
    :rel="newtab ? 'noopener noreferrer' : undefined"
    class="icon-button"
  >
    <slot />
  </component>
</template>

<script>
export default {
  props: {
    href: {
      type: String,
      default: undefined
    },
    submit: {
      type: Boolean,
      default: false
    },
    title: {
      type: Boolean,
      required: true
    },
    newtab: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    componentIs() {
      return this.href ? 'a' : 'button'
    }
  }
}
</script>

<style lang="scss" scoped>
.icon-button {
  align-items: center;
  appearance: none;
  background: none;
  border: none;
  border-radius: $border-radius;
  color: $color-muted;
  cursor: pointer;
  display: inline-flex;
  height: $icon-button-size;
  justify-content: center;
  text-decoration: none;
  transition: #{$transition-base-fast} background-color;
  width: $icon-button-size;
  &:hover,
  &:focus {
    background-color: $color-icon-button-hover;
    color: $color-primary;
  }
  &:focus {
    box-shadow: 0 0 0 3px $color-focus-primary;
    outline: none;
  }
  &:disabled {
    cursor: not-allowed;
    opacity: 0.5;
    pointer-events: none;
  }
}
</style>
