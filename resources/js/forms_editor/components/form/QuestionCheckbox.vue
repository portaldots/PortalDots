<template>
  <form-item :item_id="question_id" type_label="複数選択(チェックボックス)">
    <template v-slot:content>
      <div class="form-group mb-0">
        <p class="mb-2">
          {{ name }}
          <span class="badge badge-danger" v-if="is_required">必須</span>
        </p>
        <p class="form-text text-muted mb-2">
          {{ description }}
        </p>
        <div class="form-check mb-1" v-for="option in options" :key="option">
          <input class="form-check-input" type="checkbox" tabindex="-1" />
          <p class="form-check-label">
            {{ option }}
          </p>
        </div>
      </div>
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
      return this.question.name || '(無題の複数選択(チェックボックス))'
    },
    description() {
      return this.question.description
    },
    options() {
      return this.question.options
        ? this.question.options.trim().split(/\r\n|\n/)
        : ['(選択肢なし)']
    },
    is_required() {
      return this.question.is_required
    }
  }
}
</script>
