<template>
  <div
    class="row"
    :class="[
      gridTemplateColumns ? 'is-customized' : 'is-repeated-width',
      noResponsive ? 'is-no-responsive' : '',
    ]"
    :style="{
      '--row-columns': columns,
      '--row-grid-template-columns': gridTemplateColumns,
    }"
  >
    <slot />
  </div>
</template>

<script>
export default {
  props: {
    columns: {
      type: Number,
      default: 1,
    },
    gridTemplateColumns: {
      type: String,
      default: null,
    },
    noResponsive: {
      type: Boolean,
      default: false,
    },
  },
};
</script>

<style lang="scss" scoped>
.row {
  display: grid;
  gap: $spacing;
  &.is-repeated-width {
    grid-template-columns: repeat(var(--row-columns), minmax(0, 1fr));
  }
  &.is-customized {
    grid-template-columns: var(--row-grid-template-columns);
  }

  @media screen and (max-width: $breakpoint-layout-row-single-column) {
    &:not(.is-no-responsive) {
      grid-template-columns: 1fr;
    }
  }
}
</style>
