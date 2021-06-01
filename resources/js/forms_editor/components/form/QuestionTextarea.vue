<template>
  <form-item :item_id="question_id" type_label="複数行入力">
    <template v-slot:content>
      <QuestionItem
        :required="is_required"
        type="textarea"
        :questionId="question_id"
        :name="name"
        :description="description"
        :numberMin="number_min"
        :numberMax="number_max"
        value="複数行入力"
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
      type: Number
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
    description() {
      return this.question.description
    },
    is_required() {
      return this.question.is_required
    }
  }
}
</script>
