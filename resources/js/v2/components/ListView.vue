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
  margin-left: -$container-padding-x;
  margin-right: -$container-padding-x;
  padding: $spacing 0 $spacing-md;
  @media screen and (max-width: $breakpoint-listview-sm) {
    padding: $spacing-md 0;
  }
  &-header {
    padding: 0 $spacing $spacing-md;
    width: 100%;
    &__title {
      font-size: $font-size-lg;
      font-weight: bold;
      margin: $spacing-sm 0 0;
    }
    &__description {
      color: $color-muted;
      margin: $spacing-xs 0 0;
    }
  }
  &-body {
    @media screen and (max-width: $breakpoint-listview-sm) {
      border-bottom: $listview-border;
      border-top: $listview-border;
    }
  }
}
</style>
