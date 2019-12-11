<template>
  <form-item :item_id="question_id" type_label="ファイルアップロード">
    <template v-slot:content>
      <div class="form-group mb-0">
        <label class="mb-1">
          {{ name }}
          <span class="badge badge-danger" v-if="is_required">必須</span>
        </label>
        <p class="form-text text-muted mb-2">
          {{ description }}
        </p>
        <input type="file" class="form-control" tabindex="-1" />
      </div>
    </template>
    <template v-slot:edit-panel>
      <edit-panel
        :question="question"
        :label_number_min="false"
        label_number_max="最大サイズ(KB)"
        :show_allowed_types="true"
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
