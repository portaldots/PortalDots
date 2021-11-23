<template>
  <div class="listview">
    <div class="listview-header" v-if="$slots.title || $slots.description">
      <h2 class="listview-header__title" v-if="$slots.title">
        <slot name="title" />
      </h2>
      <div class="listview-header__description" v-if="$slots.description">
        <slot name="description" />
      </div>
    </div>
    <div class="listview-body">
      <slot />
    </div>
  </div>
</template>

<script>
export default {}
</script>

<style lang="scss" scoped>
@use "sass:math";

.listview {
  border-top: 1px solid $color-border;
  padding: $spacing-lg 0 0;
  &:first-of-type {
    border-top: 0;
    margin-top: 0;
  }
  & + & {
    margin-top: 3rem;
  }
  &-header {
    width: 100%;
    &__title {
      font-size: $font-size-xl;
      font-weight: bold;
      margin: 0;
    }
    &__description {
      margin: $spacing-xxs 0 0;
    }
  }
  &-body {
    margin-left: math.div(-$spacing, 2);
    margin-right: math.div(-$spacing, 2);
  }
}
</style>
