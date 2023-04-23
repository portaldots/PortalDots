<template>
  <div class="side_window" v-if="isOpen">
    <div class="side_window-header">
      <div class="side_window-header__title">
        <slot name="title" />
      </div>
      <IconButton
        title="新しいタブで開く"
        :href="popUpUrl"
        v-if="popUpUrl"
        newtab
      >
        <i class="fas fa-external-link-alt"></i>
      </IconButton>
      <IconButton title="閉じる" @click="onClickClose">
        <i class="fas fa-times"></i>
      </IconButton>
    </div>
    <div class="side_window-body">
      <slot />
    </div>
  </div>
</template>

<script>
import IconButton from "./IconButton.vue";

export default {
  components: { IconButton },
  props: {
    isOpen: {
      type: Boolean,
      default: false,
    },
    popUpUrl: {
      type: String,
      default: null,
    },
  },
  emits: ["clickClose"],
  methods: {
    onClickClose(e) {
      this.$emit("clickClose", e);
    },
  },
};
</script>

<style lang="scss" scoped>
.side_window {
  background: $color-bg-surface;
  bottom: 0;
  box-shadow: $box-shadow-lv1;
  display: flex;
  flex-direction: column;
  position: fixed;
  right: 0;
  top: $navbar-height;
  width: $side-window-width;
  z-index: $z-index-side-window;

  @media screen and (max-width: $breakpoint-side-window-fluid) {
    left: 0;
    width: 100%;
  }
  &-header {
    align-items: center;
    border-bottom: 1px solid $color-border;
    display: flex;
    flex-shrink: 0;
    height: 4rem;
    padding: 0 $spacing;
    padding-right: $spacing-sm;
    &__title {
      font-size: 1.1rem;
      font-weight: $font-bold;
      margin-right: auto;
    }
  }
  &-body {
    flex: 1;
    overflow: auto;
    overflow-x: hidden;
    padding: 0;
    position: relative;
  }
}
</style>
