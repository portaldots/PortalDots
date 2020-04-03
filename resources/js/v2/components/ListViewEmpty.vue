<template>
  <div class="listview-empty">
    <i :class="`${iconClass} listview-empty__icon`"></i>
    <p class="listview-empty__text">{{ text }}</p>
    <div class="listview-empty__body" v-if="$slots.default">
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    iconClass: {
      type: String,
      required: true
    },
    text: {
      type: String,
      required: true
    }
  }
}
</script>

<style lang="scss" scoped>
.listview-empty {
  color: $color-muted;
  padding: $spacing-lg 0;
  text-align: center;
  width: 100%;
  &__icon {
    font-size: 2.5rem;
    margin: 0 0 $spacing-md;
  }
  &__text {
    font-weight: bold;
    margin: 0;
  }
  &__body {
    margin: 0.5rem 0 0;
  }
}
</style>
