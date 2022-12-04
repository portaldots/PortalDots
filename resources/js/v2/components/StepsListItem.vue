<template>
  <li class="steps-list-item" :class="{ 'is-active': active }">
    <span class="steps-list-item__content">
      <slot />
    </span>
  </li>
</template>

<script>
export default {
  props: {
    active: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.steps-list-item {
  $circle-size: 1.5rem;

  margin: 0;
  position: relative;
  &__content {
    background: $color-bg-surface;
    display: inline-block;
    margin-left: -#{$spacing-md};
    padding:
      $spacing-md $spacing-md $spacing-md #{$circle-size +
      ($spacing-md * 1.75)};
    text-align: left;
  }
  &::before {
    align-items: center;
    background: $color-bg-surface;
    border: 1px solid $color-muted-2;
    border-radius: 50%;
    color: $color-muted-2;
    content: counter(steps-list);
    counter-increment: steps-list;
    display: flex;
    font-size: $circle-size * 0.6;
    font-weight: $font-bold;
    height: $circle-size;
    justify-content: center;
    left: 0;
    line-height: 1;
    position: absolute;
    text-align: center;
    top: 50%;
    transform: translateY(-50%);
    width: $circle-size;
  }
  &.is-active {
    font-weight: $font-bold;
    &::before {
      background: $color-primary;
      border: 1px solid $color-primary;
      color: $color-bg-surface;
    }
  }
  &:not(:last-child)::after {
    background: $color-primary;
    content: "";
    display: block;
    height: 2px;
    left: 50%;
    position: absolute;
    right: 150%;
    top: 0;
  }
}
</style>
