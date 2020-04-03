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
  $item-space: 0.3rem;
  $steps-list-color: $color-muted;
  $circle-size: 1.4rem;

  align-items: center;
  background: #fff;
  border: 1px solid #{$steps-list-color};
  border-radius: $border-radius;
  display: flex;
  flex: 1;
  flex-direction: column;
  justify-content: center;
  margin: 0 #{$item-space};
  padding: #{$circle-size + $spacing-sm + $spacing-xs} 0 $spacing-sm;
  position: relative;
  text-align: center;
  &::before {
    align-items: center;
    background: #fff;
    border: 1px solid $steps-list-color;
    border-radius: 50%;
    color: $steps-list-color;
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
  &::after {
    background: #fff;
    border: 1px solid $steps-list-color;
    border-bottom: 0;
    border-left: 0;
    content: '';
    display: block;
    height: 0.75rem;
    position: absolute;
    right: -0.5rem;
    top: 50%;
    transform: translateY(-50%) rotate(45deg);
    width: 0.75rem;
    z-index: 2;
  }
  &:last-child::after {
    display: none;
  }
  &.is-active {
    background: $steps-list-color;
    color: #fff;
    font-weight: bold;
    &::after {
      background: $steps-list-color;
    }
  }
}
</style>
