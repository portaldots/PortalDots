<template>
  <form-item :item_id="question_id" type_label="単一選択(ドロップダウン)">
    <template v-slot:content>
      <div class="form-group mb-0">
        <div class="text-muted text-center">
          <p class="lead">申請フォームエディターでの編集に未対応</p>
          <p>この設問は、まだエディターで編集できません。</p>
        </div>
      </div>
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

export default {
  props: {
    question_id: {
      required: true,
      type: Number
    }
  },
  components: {
    FormItem,
    EditPanel
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
    }
  }
}
</script>
