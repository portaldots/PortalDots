<template>
  <form-item :item_id="question_id" type_label="複数選択(チェックボックス)">
    <template v-slot:content>
      <QuestionItem
        :required="is_required"
        type="checkbox"
        :questionId="question_id"
        :name="name"
        :description="description"
        :options="options"
        :numberMin="number_min"
        :numberMax="number_max"
        disabled
      />
      <ListViewCard v-if="!options">
        <AppInfoBox danger>
          <b>選択肢がありません。</b>
          選択肢を1つ以上入力してください。
        </AppInfoBox>
      </ListViewCard>
    </template>
    <template v-slot:edit-panel>
      <edit-panel
        :question="question"
        label_number_min="最低選択数"
        label_number_max="最大選択数"
        :show_options="true"
      />
    </template>
  </form-item>
</template>

<script>
import FormItem from './FormItem.vue'
import EditPanel from './EditPanel.vue'
import { GET_QUESTION_BY_ID } from '../../store/editor'
import QuestionItem from '../../../v2/components/Forms/QuestionItem.vue'
import AppInfoBox from '../../../v2/components/AppInfoBox.vue'
import ListViewCard from '../../../v2/components/ListViewCard.vue'

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
    QuestionItem,
    AppInfoBox,
    ListViewCard
  },
  computed: {
    question() {
      return this.$store.getters[`editor/${GET_QUESTION_BY_ID}`](
        this.question_id
      )
    },
    name() {
      return this.question.name || '(無題の複数選択(チェックボックス))'
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
    options() {
      if (this.question.options) {
        const options = new Set(this.question.options.trim().split(/\r\n|\n/))
        return Array.from(options)
      }
      return null
    },
    is_required() {
      return this.question.is_required
    }
  }
}
</script>
