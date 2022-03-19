<template>
  <div
    class="row"
    :class="{ 'is-repeated-width': !gridTemplateColumns }"
    :style="{ '--row-columns': columns, gridTemplateColumns }"
  >
    <slot />
  </div>
</template>

<script>
export default {
  props: {
    columns: {
      type: Number,
      default: 1
    },
    gridTemplateColumns: {
      type: String,
      default: null
    }
  }
}
</script>

<style lang="scss" scoped>
.row {
  display: grid;
  gap: $spacing;
  &.is-repeated-width {
    grid-template-columns: repeat(var(--row-columns), minmax(0, 1fr));
  }

  @media screen and (max-width: $breakpoint-layout-row-single-column) {
    display: block;
  }
}
</style>
