<template>
  <component
    :is="componentIs"
    :type="type"
    :href="href"
    v-tooltip="title"
    :target="newtab ? '_blank' : undefined"
    :rel="newtab ? 'noopener noreferrer' : undefined"
    v-bind="disabledProps"
    class="icon-button"
    :class="{ 'is-active': active }"
    @click="handleClick"
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
    button: {
      type: Boolean,
      default: true
    },
    title: {
      type: String,
      required: true
    },
    newtab: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    active: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    handleClick(e) {
      this.$emit('click', e)
    }
  },
  computed: {
    componentIs() {
      return this.href ? 'a' : 'button'
    },
    type() {
      if (this.submit) {
        return 'submit'
      }
      if (this.button) {
        return 'button'
      }
      return undefined
    },
    disabledProps() {
      if (!this.disabled) return {}

      if (this.href) {
        return {
          class: 'is-disabled'
        }
      }
      return {
        disabled: true
      }
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
    background-color: $color-primary-light;
    color: $color-primary;
  }
  &:focus {
    box-shadow: $box-shadow-focus;
    outline: none;
  }
  &:disabled,
  &.is-disabled {
    cursor: not-allowed;
    opacity: 0.5;
    pointer-events: none;
  }
  &.is-active {
    background-color: $color-primary-light;
    color: $color-primary;
  }
}
</style>
