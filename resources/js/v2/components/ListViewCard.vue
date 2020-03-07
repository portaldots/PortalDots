<template>
  <ListViewBaseItem>
    <slot />
  </ListViewBaseItem>
</template>

<script>
import ListViewBaseItem from './ListViewBaseItem.vue'

export default {
  components: {
    ListViewBaseItem
  }
}
</script>
