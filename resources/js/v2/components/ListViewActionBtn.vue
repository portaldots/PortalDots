<template>
  <ListViewBaseItem
    :href="href"
    :newtab="newtab"
    :button="button"
    :submit="submit"
    class="action-btn"
    :class="{ 'is-danger': danger }"
    @click="onClick"
  >
    <div class="action-btn__inner">
      <i :class="iconClass" v-if="iconClass" class="action-btn__icon"></i>
      <slot />
    </div>
  </ListViewBaseItem>
</template>

<script>
import ListViewBaseItem from './ListViewBaseItem.vue'

export default {
  components: {
    ListViewBaseItem
  },
  props: {
    href: {
      type: String,
      default: null
    },
    newtab: {
      type: Boolean,
      default: false
    },
    button: {
      type: Boolean,
      default: false
    },
    submit: {
      type: Boolean,
      default: false
    },
    danger: {
      type: Boolean,
      default: false
    },
    iconClass: {
      type: String,
      default: null
    }
  },
  methods: {
    onClick(e) {
      this.$emit('click', e)
    }
  }
}
</script>

<style lang="scss" scoped>
.action-btn {
  align-items: center;
  color: $color-primary;
  display: flex;
  font-weight: bold;
  justify-content: center;
  padding-bottom: $spacing-md;
  padding-top: $spacing-md;
  &.is-danger {
    color: $color-danger;
  }
  &:disabled {
    cursor: auto;
    &:hover,
    &:active,
    &:focus {
      background: $color-bg-white;
    }
  }
  &:disabled &__inner {
    color: $color-text;
    opacity: 0.4;
  }
  &:not(:disabled):hover,
  &:not(:disabled):active,
  &:not(:disabled):focus {
    color: $color-primary;
    &.is-danger {
      color: $color-danger;
    }
  }
  &__icon {
    font-size: 1.25rem;
    margin-right: $spacing-sm;
  }
}
</style>
