<template>
  <ListViewBaseItem :noBorder="noBorder">
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
    noBorder: {
      type: Boolean,
      default: false
    }
  }
}
</script>
