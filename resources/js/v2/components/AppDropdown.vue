<template>
  <div class="dropdown">
    <div class="dropdown-backdrop" v-if="isOpen" @click="close"></div>
    <div class="dropdown-button">
      <slot name="button" :toggle="toggle" :props="ariaButtonProps" />
    </div>
    <div
      class="dropdown-menu"
      :class="{ 'is-fluid': menuFluid }"
      v-if="isOpen"
      :aria-labelledby="`dropdown-button-${name}`"
      @click="close"
    >
      <div v-for="item in items" :key="item.key">
        <slot name="item" :item="item" />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isOpen: false
    }
  },
  props: {
    items: {
      // 各要素は {key: String} が必須
      type: Array,
      required: true
    },
    name: {
      // ドロップダウンを識別するユニークな名前
      type: String,
      required: true
    },
    menuFluid: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    toggle() {
      this.isOpen = !this.isOpen
    },
    close() {
      this.isOpen = false
    }
  },
  computed: {
    ariaButtonProps() {
      return {
        id: `dropdown-button-${this.name}`,
        'aria-haspopup': true,
        'aria-expanded': this.isOpen
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.dropdown {
  display: inline-block;
  position: relative;
  &-menu {
    background: $color-bg-white;
    border-radius: $border-radius;
    box-shadow: 0 0.4rem 0.8rem 0.1rem rgba($color-text, 0.25);
    left: 0;
    padding: $spacing-sm 0;
    position: absolute;
    top: calc(100% + 3px);
    z-index: $z-index-dropdown-menu;
    &.is-fluid {
      right: 0;
    }
  }
  &-backdrop {
    bottom: 0;
    left: 0;
    position: fixed;
    right: 0;
    top: 0;
    z-index: $z-index-dropdown-backdrop;
  }
}
</style>
