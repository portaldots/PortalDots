<template>
  <ListViewFormGroup :labelFor="inputId">
    <!-- TODO: Min や Max といったバリデーションルールにも対応する -->
    <!-- TODO: せっかくVue使ってるので、バリデーションエラーは即座に表示したい -->
    <template #label>
      {{ name }}
      {{ required ? '(必須)' : '' }}
    </template>
    <template #description>
      {{ description }}
    </template>
    <component
      :is="componentIs"
      :inputId="inputId"
      :inputName="inputName"
      :required="required"
      :invalid="invalid"
      :value="value"
      :options="options"
    />
    <template #invalid v-if="invalid">
      {{ invalid }}
    </template>
  </ListViewFormGroup>
</template>

<script>
import ListViewFormGroup from '../ListViewFormGroup.vue'

import QuestionText from './QuestionText.vue'
import QuestionTextarea from './QuestionTextarea.vue'
import QuestionUpload from './QuestionUpload.vue'
import QuestionNumber from './QuestionNumber.vue'
import QuestionSelect from './QuestionSelect.vue'
import QuestionRadio from './QuestionRadio.vue'
import QuestionCheckbox from './QuestionCheckbox.vue'

export default {
  components: {
    ListViewFormGroup,
    QuestionText,
    QuestionTextarea,
    QuestionUpload,
    QuestionNumber,
    QuestionSelect,
    QuestionRadio,
    QuestionCheckbox
  },
  props: {
    type: {
      type: String,
      required: true
    },
    questionId: {
      type: Number,
      required: true
    },
    name: {
      type: String,
      default: null
    },
    description: {
      type: String,
      default: null
    },
    required: {
      type: Boolean,
      default: false
    },
    invalid: {
      type: String,
      default: null
    },
    value: {
      type: String
    },
    options: {
      type: Array
    }
  },
  computed: {
    componentIs() {
      return `Question${this.type.charAt(0).toUpperCase() + this.type.slice(1)}`
    },
    inputId() {
      if (['radio', 'checkbox'].includes(this.type)) {
        return undefined
      }
      return `question-${this.questionId}`
    },
    inputName() {
      if (this.type === 'checkbox') {
        return `answers[${this.questionId}][]`
      }
      return `answers[${this.questionId}]`
    }
  }
}
</script>
