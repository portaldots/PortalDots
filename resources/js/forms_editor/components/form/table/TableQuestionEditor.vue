<template>
  <draggable
    v-model="items"
    draggable=".table-question-editor-item"
    @end="save"
  >
    <table-question-editor-item
      v-for="question in items"
      :question="question"
      class="table-question-editor-item"
      :key="question.id"
      @saveQuestion="save"
      @removeQuestion="remove"
    ></table-question-editor-item>
  </draggable>
</template>

<script>
import draggable from 'vuedraggable'
import TableQuestionEditorItem from './TableQuestionEditorItem.vue'

export default {
  props: {
    questions: {
      required: true,
      type: Array
    }
  },
  data() {
    return {
      items: this.questions
    }
  },
  components: {
    draggable,
    TableQuestionEditorItem
  },
  methods: {
    remove(id) {
      const index = this.items.findIndex((item) => item.id === id)
      this.items.splice(index, 1)
      this.save()
    },
    save() {
      this.$emit('save', this.items)
    }
  }
}
</script>
