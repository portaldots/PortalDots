<template>
  <div class="row" :style="{ '--row-columns': columns }">
    <slot />
  </div>
</template>

<script>
export default {
  props: {
    columns: {
      type: Number,
      default: 1
    }
  }
}
</script>

<style lang="scss" scoped>
.row {
  display: grid;
  gap: $spacing;
  grid-template-columns: repeat(var(--row-columns), minmax(0, 1fr));

  @media screen and (max-width: $breakpoint-layout-row-single-column) {
    display: block;
  }
}
</style>
