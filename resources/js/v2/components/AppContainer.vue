<template>
  <component
    :is="componentIs"
    class="container"
    :class="{ 'is-narrow': narrow, 'is-medium': medium }"
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
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  margin: 0 auto;
  max-width: $container-width;
  padding: 0 $spacing;
  width: 100%;
  &.is-narrow {
    max-width: $container-width-narrow;
  }
  &.is-medium {
    max-width: $container-width-medium;
  }
}
</style>
