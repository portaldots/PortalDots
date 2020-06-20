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
}
</style>
