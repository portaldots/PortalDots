<template>
  <ListViewBaseItem class="question-heading" noBorder>
    <div class="question-heading__body">
      <h2 class="question-heading__name">{{ name }}</h2>
      <div class="question-heading__description">
        <slot />
      </div>
    </div>
  </ListViewBaseItem>
</template>

<script>
import ListViewBaseItem from '../ListViewBaseItem.vue'

export default {
  components: {
    ListViewBaseItem
  },
  props: {
    name: {
      type: String,
      default: null
    }
  }
}
</script>

<style lang="scss" scoped>
.question-heading {
  border: 0;
  padding: $spacing 0 1px; // コンテンツの margin-bottom が下にはみ出さないように、padding-bottom を設定
  &:first-child {
    padding-top: 0;
  }
  &__body {
    border-top: $listview-border;
    padding: $spacing $container-padding-x 0;
  }
  &:first-child &__body {
    border: 0;
  }
  &:last-child &__body {
    padding-bottom: $spacing;
  }
  &__name {
    font-size: 1.5rem;
    line-height: 1.4;
    margin: 0;
    padding: 0 0 $spacing-sm;
  }
}
</style>
