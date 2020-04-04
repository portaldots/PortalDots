<template>
  <ol class="steps-list">
    <slot />
  </ol>
</template>

<style lang="scss" scoped>
.steps-list {
  $item-space: 0.3rem;

  counter-reset: steps-list;
  display: flex;
  justify-content: space-between;
  list-style: none;
  margin: 0 #{-$item-space};
  padding: 0;
}
</style>
