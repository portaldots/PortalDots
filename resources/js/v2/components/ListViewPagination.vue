<template>
  <ListViewBaseItem v-if="prev || next" class="listview-pagination">
    <a :href="prev" v-if="prev" class="listview-pagination__link">
      前のページへ
    </a>
    <a :href="next" v-if="next" class="listview-pagination__link">
      次のページへ
    </a>
  </ListViewBaseItem>
</template>

<script>
import ListViewBaseItem from './ListViewBaseItem.vue'

export default {
  components: {
    ListViewBaseItem
  },
  props: {
    prev: {
      type: String,
      default: null
    },
    next: {
      type: String,
      default: null
    }
  }
}
</script>

<style lang="scss" scoped>
.listview-pagination {
  display: flex;
  justify-content: center;
  overflow: hidden;
  padding: 0;
  text-align: center;
  &__link {
    color: $color-primary;
    display: block;
    font-weight: bold;
    padding: $spacing-md;
    width: 100%;
    &:hover,
    &:active,
    &:focus {
      background: $color-bg-light;
      color: $color-primary;
      text-decoration: none;
    }
  }
}
</style>
