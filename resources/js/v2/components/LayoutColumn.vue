<template>
  <div class="column" :style="{ '--column-span': columnSpan }">
    <slot />
  </div>
</template>

<script>
export default {
  props: {
    columnSpan: {
      type: Number,
      default: 1
    }
  }
}
</script>

<style lang="scss" scoped>
.column {
  grid-column: span var(--column-span) / span var(--column-span);
}
</style>
