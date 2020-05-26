<template>
  <li class="steps-list-item" :class="{ 'is-active': active }">
    <slot />
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
  $circle-size: 1.4rem;

  align-items: center;
  background: $color-bg-white;
  border-radius: $border-radius;
  display: flex;
  flex: 1;
  flex-direction: column;
  justify-content: center;
  margin: 0 #{$steps-list-item-space};
  padding: #{$circle-size + $spacing-s} 0 $spacing-sm;
  position: relative;
  text-align: center;
  &::before {
    align-items: center;
    background: $color-bg-white;
    border: 1px solid $color-primary;
    border-radius: 50%;
    color: $color-primary;
    content: counter(steps-list);
    counter-increment: steps-list;
    display: flex;
    font-size: $circle-size * 0.75;
    font-weight: bold;
    height: $circle-size;
    justify-content: center;
    left: 50%;
    line-height: 1;
    position: absolute;
    text-align: center;
    top: $spacing-sm;
    transform: translateX(-50%);
    width: $circle-size;
  }
  &.is-active {
    font-weight: bold;
    &::before {
      background: $color-primary;
      color: $color-bg-white;
    }
  }
  &:not(:last-child)::after {
    background: $color-primary;
    content: '';
    display: block;
    height: 2px;
    left: 50%;
    position: absolute;
    right: 150%;
    top: 0;
  }
}
</style>
