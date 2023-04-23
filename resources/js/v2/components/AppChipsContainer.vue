<template>
  <div class="chips-container">
    <slot />
  </div>
</template>

<script>
export default {};
</script>

<style lang="scss" scoped>
.chips-container {
  display: flex;
  flex-wrap: wrap;
  gap: $spacing-sm;
}
</style>
