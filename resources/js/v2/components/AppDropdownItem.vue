<template>
  <component
    :is="componentIs"
    class="dropdown-item"
    :tabindex="['a', 'button'].includes(componentIs) ? 0 : undefined"
    @click="onClick"
    @mouseover="onMouseover"
    @mouseout="onMouseout"
  >
    <slot />
  </component>
</template>

<script>
export default {
  props: {
    componentIs: {
      type: String,
      default: "div",
    },
  },
  emits: ["click", "mouseover", "mouseout"],
  methods: {
    onClick(e) {
      this.$emit("click", e);
    },
    onMouseover(e) {
      this.$emit("mouseover", e);
    },
    onMouseout(e) {
      this.$emit("mouseout", e);
    },
  },
};
</script>

<style lang="scss" scoped>
.dropdown-item {
  appearance: none;
  background: transparent;
  border: 0;
  color: $color-text;
  cursor: pointer;
  display: block;
  line-height: 1.15;
  max-width: 100%;
  min-width: 15rem;
  padding: 0.75rem $spacing-md;
  text-align: left;
  width: 100%;
  &:not(a, button) {
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
    color: $color-bg-surface;
    outline: 0;
  }
}
</style>
