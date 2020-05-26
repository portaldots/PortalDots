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
  margin: 0 #{-$steps-list-item-space};
  padding: 0;
}
</style>
