<template>
  <div class="side_window" v-if="isOpen">
    <div class="side_window-header">
      <div class="side_window-header__title">
        <slot name="title" />
      </div>
      <button class="side_window-header__close" @click="onClickClose">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="side_window-body">
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    isOpen: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    onClickClose(e) {
      this.$emit('clickClose', e)
    }
  }
}
</script>

<style lang="scss" scoped>
.side_window {
  background: $color-bg-white;
  bottom: 0;
  box-shadow: -1px 0 2px $color-border;
  display: flex;
  flex-direction: column;
  position: fixed;
  right: 0;
  top: $navbar-height;
  width: $side-window-width;
  z-index: $z-index-side-window;
  &-header {
    align-items: center;
    display: flex;
    height: 4rem;
    position: relative;
    &__title {
      font-size: 1.1rem;
      font-weight: 600;
      padding: 0 $spacing;
    }
    &__close {
      align-items: center;
      appearance: none;
      background: transparent;
      border: 0;
      color: $color-muted;
      cursor: pointer;
      display: flex;
      height: 100%;
      justify-content: center;
      margin-left: auto;
      padding: 0 $spacing;
    }
    &::after {
      border-bottom: 1px solid $color-border;
      bottom: 0;
      content: '';
      display: block;
      left: $spacing;
      position: absolute;
      right: $spacing;
    }
  }
  &-body {
    flex: 1;
    overflow: auto;
    overflow-x: hidden;
    padding: 0 0 $spacing;
  }
}
</style>
