<template>
  <ListViewBaseItem :href="href" :newtab="newtab" class="action-btn">
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
    }
  }
}
</script>

<style lang="scss" scoped>
.action-btn {
  align-items: center;
  color: $color-primary;
  display: flex;
  flex-direction: column;
  font-weight: bold;
  justify-content: center;
  padding-bottom: $spacing-md;
  padding-top: $spacing-md;
  &:hover,
  &:active,
  &:focus {
    color: $color-primary;
  }
}
</style>
