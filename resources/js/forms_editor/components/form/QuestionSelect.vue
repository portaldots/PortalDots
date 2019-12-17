<template>
  <form-item :item_id="question_id" type_label="単一選択(ドロップダウン)">
    <template v-slot:content>
      <div class="form-group mb-0">
        <p class="mb-2">
          {{ name }}
          <span class="badge badge-danger" v-if="is_required">必須</span>
        </p>
        <p class="form-text text-muted mb-2">
          {{ description }}
        </p>
        <select class="custom-select" tabindex="-1">
          <option>単一選択(ドロップダウン)</option>
        </select>
        <ul class="list-group">
          <li
            class="list-group-item py-1"
            v-for="option in options"
            :key="option"
          >
            {{ option }}
          </li>
        </ul>
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
      return this.question.name || '(無題の単一選択(ドロップダウン))'
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

<style lang="scss" scoped>
.custom-select {
  appearance: none;
  border-bottom: 0;
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
}

.list-group-item {
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}
</style>
