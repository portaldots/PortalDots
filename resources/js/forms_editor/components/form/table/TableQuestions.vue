<template>
  <div class="table-questions">
    <template v-if="questions.length === 0">
      <div class="empty-option">
        <p class="empty-option-text">
          <i class="fa fa-exclamation-triangle mr-1"></i>
          <b>設問がありません。</b>
        </p>
        <p class="empty-option-text">設問を1つ以上入力してください。</p>
      </div>
    </template>
    <div
      v-else
      class="form-group"
      v-for="question in questions"
      :key="question.id"
    >
      <table-question-item :question="question" />
    </div>
  </div>
</template>

<script>
import TableQuestionItem from './TableQuestionItem.vue'

export default {
  props: {
    table: {
      required: true,
      type: Array
    }
  },
  components: {
    TableQuestionItem
  },
  computed: {
    questions: {
      // cache: false,
      get() {
        return this.table || []
      }
    }
  }
}
</script>
