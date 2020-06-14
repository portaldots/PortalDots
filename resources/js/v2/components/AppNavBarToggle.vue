<template>
  <button class="navbar-toggle" @click="onClick">
    <img
      src="../../../img/drawerToggle.svg"
      alt="ドロワーを開閉"
      class="navbar-toggle__icon"
    />
  </button>
</template>

<script>
export default {
  methods: {
    onClick() {
      this.$emit('click')
    }
  }
}
</script>

<style lang="scss" scoped>
.navbar-toggle {
  appearance: none;
  background: 0;
  border: 0;
  cursor: pointer;
  display: none;
  height: 100%;
  // ↓ナビバーがもつpaddingを打ち消すネガティブマージン
  margin-left: -$spacing;
  padding: $spacing;

  @media screen and (max-width: $breakpoint-drawer-hide) {
    display: block;
  }
  &:focus {
    outline: 0;
  }
  &:focus &__icon {
    outline: $focus-outline;
    outline-offset: $spacing-xs;
  }
}
</style>
