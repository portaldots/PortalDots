<template>
  <div class="listview-empty">
    <i v-if="iconClass" :class="`${iconClass} listview-empty__icon`" />
    <p class="listview-empty__text">{{ text }}</p>
    <div v-if="$slots.default" class="listview-empty__body">
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    iconClass: {
      type: String,
      required: false,
      default: null,
    },
    text: {
      type: String,
      required: true,
    },
  },
};
</script>

<style lang="scss" scoped>
.listview-empty {
  padding: $spacing-lg 0;
  text-align: center;
  width: 100%;
  &__icon {
    font-size: 2.5rem;
    margin: 0 0 $spacing-md;
    color: $color-primary;
    opacity: 0.5;
  }
  &__text {
    font-size: 1.25rem;
    font-weight: $font-bold;
    margin: 0;
  }
  &__body {
    margin: 0.5rem 0 0;
  }
}
</style>
