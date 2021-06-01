<template>
  <form-item
    :item_id="question_id"
    type_label="セクション見出し"
    class="form-item"
  >
    <template v-slot:content>
      <QuestionHeading :name="name">
        <div class="markdown" v-html="description_html" />
      </QuestionHeading>
    </template>
    <template v-slot:edit-panel>
      <edit-panel
        :question="question"
        label_name="見出し"
        :show_required_switch="false"
        :label_number_max="false"
        :label_number_min="false"
      />
    </template>
  </form-item>
</template>

<script>
import marked from 'marked'
import FormItem from './FormItem.vue'
import EditPanel from './EditPanel.vue'
import { GET_QUESTION_BY_ID } from '../../store/editor'
import QuestionHeading from '../../../v2/components/Forms/QuestionHeading.vue'

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
    QuestionHeading
  },
  computed: {
    question() {
      return this.$store.getters[`editor/${GET_QUESTION_BY_ID}`](
        this.question_id
      )
    },
    name() {
      return this.question.name || '(無題のセクション見出し)'
    },
    description_html() {
      const { description } = this.question
      return marked(description || '')
    }
  }
}
</script>

<style lang="scss" scoped>
.form-item {
  position: relative;
  &::before {
    border-top: 1px solid var(--color-border);
    content: '';
    display: block;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
  }
}

.heading {
  font-size: 1.5rem;
  font-weight: bold;
  line-height: 1.4;
  margin: 0;
  padding: 0 0 0.5rem;
}
</style>
