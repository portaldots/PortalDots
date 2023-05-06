<template>
  <details class="accordion">
    <summary class="accordion-summary">
      <slot name="summary" />
    </summary>
    <slot />
  </details>
</template>

<script setup></script>

<style lang="scss" scoped>
.accordion {
  margin: $spacing-lg 0 $spacing-sm;
  border-radius: $border-radius;
  border: 1px solid $color-border;
  background-color: $color-bg-light;
  padding: 0 $spacing-md;
  &-summary {
    padding: $spacing-md 0;
    font-weight: bold;
  }
}
</style>
