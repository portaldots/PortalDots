<template>
  <ListViewBaseItem
    :href="href"
    :newtab="newtab"
    :button="button"
    :submit="submit"
    class="action-btn"
    @click="onClick"
  >
    <i :class="iconClass" v-if="iconClass" class="action-btn__icon"></i>
    <slot />
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
  &:disabled {
    color: rgba($color-primary, 0.4);
    cursor: auto;
    &:hover,
    &:active,
    &:focus {
      background: #fff;
    }
  }
  &:not(:disabled):hover,
  &:not(:disabled):active,
  &:not(:disabled):focus {
    color: $color-primary;
  }
  &__icon {
    font-size: 1.25rem;
    margin-right: $spacing-sm;
  }
}
</style>
