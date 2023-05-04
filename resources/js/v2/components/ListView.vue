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
    <div
      class="listview-body"
      :class="{ 'is-no-card-style': props.noCardStyle }"
    >
      <slot />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  /** スロット部分においてカード風のスタイルを適用しない */
  noCardStyle: {
    type: Boolean,
    default: false,
  },
});
</script>

<style lang="scss" scoped>
.listview {
  padding: $spacing-md 0 $spacing-sm;
  &:last-child {
    padding-bottom: $spacing-lg;
  }
  &-header {
    padding: $spacing-md 0 $spacing-sm;
    width: 100%;
    &__title {
      font-size: $font-size-lg;
      font-weight: $font-bold;
      line-height: $line-height-heading;
      margin: 0;
    }
    &__description {
      margin: $spacing-xxs 0 0;
    }
  }
  &-body:not(.is-no-card-style) {
    background: $color-bg-surface;
    border-radius: $border-radius;
    box-shadow: $box-shadow-lv1;
  }
}
</style>
