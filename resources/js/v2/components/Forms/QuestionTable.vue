<template>
  <div>
    <details v-for="(item, index) in tableAnswers" :key="index">
      <summary>{{ index + 1 }} 行目</summary>
      <TableQuestionItem
        v-for="tableQuestion in tableQuestions"
        :key="tableQuestion.id"
        :table-question="tableQuestion"
        :question-id="inputId"
      ></TableQuestionItem>
    </details>
    <p @click="this.addTableAnswer">追加</p>
  </div>
</template>

<script>
import TableQuestionItem from './table/TableQuestionItem.vue'

export default {
  props: {
    inputId: {
      type: String,
      default: null
    },
    inputName: {
      type: String,
      default: null
    },
    required: {
      type: Boolean,
      default: false
    },
    invalid: {
      type: String,
      default: null
    },
    value: {
      type: Array,
      default: () => [[]]
    },
    numberMin: {
      type: Number,
      default: null
    },
    numberMax: {
      type: Number,
      default: null
    },
    disabled: {
      type: Boolean,
      default: false
    },
    tableQuestions: {
      type: [Array, Object],
      default: null
    }
  },
  components: {
    TableQuestionItem
  },
  data() {
    return {
      tableAnswers: this.value || [[]]
    }
  },
  methods: {
    addTableAnswer() {
      this.tableAnswers.push([])
    }
  }
}
</script>
