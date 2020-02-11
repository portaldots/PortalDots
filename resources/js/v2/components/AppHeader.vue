<template>
  <div class="app-header">
    <h1 class="app-header__title">
      <slot name="title" />
    </h1>
    <div class="app-header__body">
      <slot />
    </div>
  </div>
</template>

<style lang="scss" scoped>
.app-header {
  padding: $spacing 0;
  &__title {
    font-size: 1.75rem;
    margin: 0 0 $spacing-sm;
  }
  &__body {
    margin: 0;
  }
}
</style>
