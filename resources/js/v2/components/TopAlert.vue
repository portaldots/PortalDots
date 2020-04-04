<template>
  <div
    class="top_alert"
    :class="{
      'is-success': type === 'success',
      'is-primary': type === 'primary',
      'is-secondary': type === 'secondary',
      'is-danger': type === 'danger',
      'is-hidden': hidden
    }"
    :style="{ height: height === Infinity ? undefined : `${height}px` }"
    ref="topAlert"
  >
    <AppContainer
      class="top_alert__container"
      :narrow="containerNarrow"
      :medium="containerMedium"
    >
      <div class="top_alert__body">
        <h2 class="top_alert__title" v-if="$slots.title">
          <slot name="title" />
        </h2>
        <p class="top_alert__message" v-if="$slots.default">
          <slot />
        </p>
      </div>
      <div class="top_alert__cta" v-if="$slots.cta">
        <slot name="cta" />
      </div>
    </AppContainer>
  </div>
</template>

<script>
import AppContainer from './AppContainer.vue'

export default {
  components: {
    AppContainer
  },
  data() {
    return {
      hidden: false,
      height: Infinity
    }
  },
  mounted() {
    if (!this.keepVisible) {
      this.height = this.$refs.topAlert.getBoundingClientRect().height
      window.setTimeout(() => {
        this.hidden = true
      }, 5000)
    }
  },
  props: {
    type: {
      type: String,
      default: 'primary'
    },
    containerNarrow: {
      type: Boolean,
      default: false
    },
    containerMedium: {
      type: Boolean,
      default: false
    },
    keepVisible: {
      // 5 秒後に自動で消えないようにする
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.top_alert {
  color: #fff;
  overflow: hidden;
  transition: 0.75s ease height, 0.75s ease color, 0.75s ease visibility;
  &.is-hidden {
    color: rgba(#fff, 0);
    height: 0 !important;
    visibility: hidden;
  }
  & + & {
    border-top: 1px solid rgba(#fff, 0.16);
  }
  &.is-success {
    background: $color-success;
  }
  &.is-primary {
    background: $color-primary;
  }
  &.is-secondary {
    background: #fff;
    color: $color-text;
    & + & {
      border-top: 1px solid rgba($color-primary, 0.16);
    }
  }
  &.is-danger {
    background: $color-danger;
  }
  &__container {
    align-items: center;
    display: flex;
    justify-content: space-between;
    padding: $spacing;

    @media screen and (max-width: $breakpoint-top-alert-col) {
      align-items: flex-start;
      flex-direction: column;
    }
  }
  &__body {
    padding-right: $spacing;
    @media screen and (max-width: $breakpoint-top-alert-col) {
      padding: 0;
    }
  }
  &__cta {
    @media screen and (max-width: $breakpoint-top-alert-col) {
      padding-top: $spacing-sm;
    }
  }
  &__title {
    font-size: 1rem;
    font-weight: bold;
    margin: 0;
  }
  &__message {
    font-size: 1rem;
    margin: $spacing-xs 0 0;
    opacity: 0.8;
  }
}
</style>
