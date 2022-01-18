<template>
  <component
    :is="componentIs"
    class="container"
    :class="{ 'is-narrow': narrow, 'is-medium': medium, 'is-fluid': fluid }"
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
    },
    narrow: {
      type: Boolean,
      default: false
    },
    medium: {
      type: Boolean,
      default: false
    },
    fluid: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  margin: 0 auto;
  max-width: $container-width;
  padding: $container-padding-y $container-padding-x;
  width: 100%;
  @media screen and (max-width: $breakpoint-container-padding-reduce) {
    padding: $container-padding-y $container-padding-x-reduce;
  }
  &.is-narrow {
    max-width: $container-width-narrow;
  }
  &.is-medium {
    max-width: $container-width-medium;
  }
  &.is-fluid {
    max-width: 100%;
  }
}
</style>
