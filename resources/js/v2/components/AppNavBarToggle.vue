<template>
  <button class="navbar-toggle" @click="onClick" title="ドロワーを開閉">
    <svg width="20" height="16" viewBox="0 0 20 16" class="navbar-toggle__icon">
      <g transform="translate(-15 -24)">
        <path
          d="M4.5,18h18"
          transform="translate(11.5 14)"
          fill="none"
          stroke="currentColor"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
        />
        <path
          d="M4.5,9h18"
          transform="translate(11.5 16)"
          fill="none"
          stroke="currentColor"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
        />
        <path
          d="M4.5,27h18"
          transform="translate(11.5 12)"
          fill="none"
          stroke="currentColor"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
        />
      </g>
    </svg>
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
  color: currentcolor;
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
