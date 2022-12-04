<template>
  <ol class="steps-list">
    <slot />
  </ol>
</template>

<style lang="scss" scoped>
.steps-list {
  counter-reset: steps-list;
  display: flex;
  justify-content: space-between;
  list-style: none;
  margin: 0;
  padding: 0;
  position: relative;
  &::before {
    border-top: 1px solid $color-border;
    content: "";
    display: block;
    left: 0;
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
  }
}
</style>
