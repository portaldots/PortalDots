<template>
  <div>
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
    <button class="btn btn-primary" @click="addQuestion">
      <i class="fas fa-plus"></i> 設問を追加
    </button>
  </div>
</template>

<script>
import draggable from 'vuedraggable'
import { v4 as uuid } from 'uuid'
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
    addQuestion() {
      this.items.push({
        id: uuid(),
        type: 'text',
        name: '',
        is_required: false
      })
      this.save()
    },
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
