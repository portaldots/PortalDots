<template>
  <form-item
    :item_id="question_id"
    :hide_handle="is_permanent"
    :disable_edit="is_permanent"
    type_label="テーブル"
  >
    <template v-slot:content>
      <div class="form-group mb-0">
        <label class="mb-1">
          {{ name }}
          <span class="badge badge-danger" v-if="is_required">必須</span>
        </label>
        <p class="form-text text-muted mb-2">
          {{ description }}
        </p>
        <table-question-editor :table="question.table"></table-question-editor>
      </div>
    </template>
    <template v-slot:edit-panel>
      <edit-panel
        :question="question"
        label_number_min="最小回答数"
        label_number_max="最大回答数"
        :show_table_questions="true"
      />
    </template>
  </form-item>
</template>

<script>
import FormItem from './FormItem.vue'
import EditPanel from './EditPanel.vue'
import { GET_QUESTION_BY_ID } from '../../store/editor'
import TableQuestionEditor from './table/TableQuestionEditor.vue'

export default {
  props: {
    question_id: {
      required: true,
      type: [Number, String]
    },
    is_permanent: {
      required: false,
      type: Boolean
    }
  },
  components: {
    FormItem,
    EditPanel,
    TableQuestionEditor
  },
  computed: {
    question() {
      return this.$store.getters[`editor/${GET_QUESTION_BY_ID}`](
        this.question_id
      )
    },
    name() {
      return this.question.name || '(無題の設問)'
    },
    description() {
      return this.question.description
    },
    is_required() {
      return this.question.is_required
    }
  }
}
</script>
