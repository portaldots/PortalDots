<template>
  <form-item
    :item_id="question_id"
    :hide_handle="is_permanent"
    :disable_edit="is_permanent"
    type_label="一行入力"
  >
    <template v-slot:content>
      <QuestionItem
        :required="is_required"
        type="text"
        :questionId="question_id"
        :name="name"
        :description="description"
        :numberMin="number_min"
        :numberMax="number_max"
        value="一行入力"
        disabled
      />
    </template>
    <template v-slot:edit-panel>
      <edit-panel :question="question" />
    </template>
  </form-item>
</template>

<script>
import FormItem from './FormItem.vue'
import EditPanel from './EditPanel.vue'
import { GET_QUESTION_BY_ID } from '../../store/editor'
import QuestionItem from '../../../v2/components/Forms/QuestionItem.vue'

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
    QuestionItem
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
    number_min() {
      return this.question.number_min
        ? parseInt(this.question.number_min, 10)
        : undefined
    },
    number_max() {
      return this.question.number_max
        ? parseInt(this.question.number_max, 10)
        : undefined
    },
    is_required() {
      return this.question.is_required
    }
  }
}
</script>
