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
.listview {
  padding: $spacing 0 $spacing-sm;
  &:last-child {
    padding-bottom: $spacing-lg;
  }
  &-header {
    padding: 0 0 $spacing-sm;
    width: 100%;
    &__title {
      font-size: $font-size-lg;
      font-weight: bold;
      margin: 0;
    }
    &__description {
      color: $color-muted;
      margin: $spacing-xxs 0 0;
    }
  }
}
</style>
