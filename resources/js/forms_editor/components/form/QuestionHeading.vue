<template>
  <form-item :item_id="question_id" type_label="セクション見出し">
    <template v-slot:content>
      <div class="form-group mb-0">
        <h2>{{ name }}</h2>
        <div>
          {{ description }}
        </div>
      </div>
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
      return this.question.name || '(無題のセクション見出し)'
    },
    description() {
      return this.question.description
    }
  }
}
</script>
