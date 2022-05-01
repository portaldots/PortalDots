<template>
  <div class="table-question-editor">
    {{ table }}
  </div>
</template>

<script>
export default {
  props: {
    table: {
      required: true,
      default: () => ({ questions: [] }),
      type: Object
    }
  }
}
</script>

<style lang="scss" scoped>
//
</style>
