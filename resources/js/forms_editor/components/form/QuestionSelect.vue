<template>
  <form-item :item_id="question_id" type_label="単一選択(ドロップダウン)">
    <template v-slot:content>
      <QuestionItem
        :required="is_required"
        type="select"
        :questionId="question_id"
        :name="name"
        :description="description"
        :options="options"
        disabled
      />
      <ListViewCard v-if="!options">
        <AppInfoBox danger>
          <b>選択肢がありません。</b>
          選択肢を1つ以上入力してください。
        </AppInfoBox>
      </ListViewCard>
      <ListViewCard>
        <div class="question-select__list">
          <div
            class="question-select__item"
            v-for="option in options"
            :key="option"
          >
            {{ option }}
          </div>
        </div>
      </ListViewCard>
    </template>
    <template v-slot:edit-panel>
      <edit-panel
        :question="question"
        :label_number_min="false"
        :label_number_max="false"
        :show_options="true"
      />
    </template>
  </form-item>
</template>

<script>
import FormItem from './FormItem.vue'
import EditPanel from './EditPanel.vue'
import { GET_QUESTION_BY_ID } from '../../store/editor'
import ListViewCard from '../../../v2/components/ListViewCard.vue'
import AppInfoBox from '../../../v2/components/AppInfoBox.vue'
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
    ListViewCard,
    AppInfoBox,
    QuestionItem
  },
  computed: {
    question() {
      return this.$store.getters[`editor/${GET_QUESTION_BY_ID}`](
        this.question_id
      )
    },
    name() {
      return this.question.name || '(無題の単一選択(ドロップダウン))'
    },
    description() {
      return this.question.description
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

<style lang="scss" scoped>
.question-select {
  &__list {
    border: 1px solid $color-border;
    border-radius: $border-radius;
    padding: 0 $spacing-sm;
  }
  &__item {
    border-bottom: 1px solid $color-border;
    padding: $spacing-xs 0;
    &:last-child {
      border: none;
    }
  }
}
</style>
