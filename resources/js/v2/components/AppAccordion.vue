<template>
  <details class="accordion">
    <summary class="accordion-summary">
      <slot name="summary" />
    </summary>
    <div class="accordion-content">
      <slot />
    </div>
  </details>
</template>

<script setup></script>

<style lang="scss" scoped>
.accordion {
  margin: $spacing-lg 0 $spacing-sm;
  border-radius: $border-radius;
  border: 1px solid $color-border;
  background-color: $color-bg-light;
  &-summary {
    padding: $spacing-md;
    font-weight: bold;
    position: sticky;
    top: $app-navbar-height;
    background-color: $color-bg-light;
    border-radius: $border-radius;
    z-index: 2;
  }
  &-content {
    padding: 0 $spacing-md;
    position: relative;
    z-index: 1;
  }
}
</style>
