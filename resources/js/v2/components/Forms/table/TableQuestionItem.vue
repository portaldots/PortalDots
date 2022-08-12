<template>
  <ListViewFormGroup>
    {{ name }}
  </ListViewFormGroup>
</template>

<script>
import ListViewFormGroup from '../../ListViewFormGroup.vue'

export default {
  props: {
    tableQuestion: {
      type: Object
    },
    questionId: {
      type: String
    }
  },
  components: {
    ListViewFormGroup
  },
  computed: {
    name() {
      return this.tableQuestion.name
    },
    is_required() {
      return this.tableQuestion.is_required
    },
    type() {
      return this.tableQuestion.type
    },
    inputId() {
      return `${this.questionId}.${this.tableQuestion.id}`
    }
  }
}
</script>
