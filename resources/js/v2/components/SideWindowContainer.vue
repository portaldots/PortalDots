<template>
  <div class="side_window_container" :class="{ 'is-open': isOpen }">
    <slot
      name="default"
      :isSideWindowOpen="isOpen"
      :toggleSideWindow="toggle"
    />
  </div>
</template>

<script>
export default {
  data() {
    return {
      isOpen: false,
    }
  },
  methods: {
    toggle() {
      this.isOpen = !this.isOpen
    },
  },
}
</script>

<style lang="scss" scoped>
.side_window_container {
  &.is-open {
    padding: 0 $side-window-width 0 0;
    @media screen and (max-width: $breakpoint-side-window-fluid) {
      padding: 0;
    }
  }
}
</style>
