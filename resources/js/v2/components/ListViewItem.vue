<template>
  <ListViewBaseItem :href="href" :newtab="newtab" class="listview-item">
    <p class="listview-item__title" v-if="$slots.title">
      <slot name="title" />
    </p>
    <p class="listview-item__meta" v-if="$slots.meta">
      <slot name="meta" />
    </p>
    <div class="listview-item__body">
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
    }
  }
}
</script>

<style lang="scss" scoped>
.listview-item {
  &__title {
    font-size: 1rem;
    font-weight: bold;
    margin: 0;
  }
  &__meta {
    font-size: 1rem;
    margin: 0;
  }
  &__body {
    color: $color-muted;
    font-size: 1rem;
    margin: $spacing-xs 0 0;
  }
}
</style>
