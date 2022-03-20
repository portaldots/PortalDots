<template>
  <div
    class="toast"
    :class="{
      'is-hidden': hidden
    }"
    ref="toast"
  >
    <button v-tooltip="'閉じる'" @click="close" class="toast-close">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <div class="toast-icon" :class="{ 'is-danger': danger }">
      <i class="fa-solid fa-triangle-exclamation fa-fw" v-if="danger"></i>
      <i class="fa-solid fa-circle-info fa-fw" v-else></i>
    </div>
    <div class="toast-body">
      <slot />
    </div>
  </div>
</template>

<script>
const toastTimeoutMs = 5000

export default {
  data() {
    return {
      hidden: false,
      height: Infinity,
      countToHidden: toastTimeoutMs,
      intervalId: null
    }
  },
  mounted() {
    if (!this.danger) {
      this.intervalId = window.setInterval(() => {
        this.countToHidden -= 10
        if (this.countToHidden < 0) {
          this.hidden = true
          window.clearInterval(this.intervalId)
        }
      }, 10)
    }
  },
  props: {
    danger: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    close() {
      this.hidden = true
      window.clearInterval(this.intervalId)
    }
  }
}
</script>

<style lang="scss" scoped>
.toast {
  $inset: $spacing-lg;

  align-items: stretch;
  background-color: $color-bg-surface-3;
  border-radius: $border-radius;
  box-shadow: $box-shadow-lv2;
  display: flex;
  max-width: 400px;
  overflow: hidden;
  padding-right: $spacing-lg;
  position: fixed;
  right: $inset;
  top: $inset;
  transition: 0.3s ease visibility, 0.3s ease-out transform;
  z-index: $z-index-toast;
  &.is-hidden {
    transform: translateX(100%) translateX(#{$inset});
    visibility: hidden;
  }
  &-close {
    align-items: center;
    appearance: none;
    background: none;
    border: none;
    border-radius: 9999px;
    cursor: pointer;
    display: flex;
    font-size: 0.75rem;
    height: 1.5rem;
    justify-content: center;
    position: absolute;
    right: $spacing-sm;
    top: $spacing-sm;
    width: 1.5rem;
    &:hover {
      background-color: $color-bg-light;
    }
  }
  &-icon {
    align-items: center;
    background-color: $color-primary-light;
    color: $color-primary;
    display: flex;
    font-size: 1.25rem;
    justify-content: center;
    text-align: center;
    width: 3rem;
    &.is-danger {
      background-color: $color-danger-light;
      color: $color-danger;
    }
  }
  &-body {
    flex: 1;
    font-size: 1rem;
    font-weight: 500;
    padding: $spacing-md;
  }
}
</style>
