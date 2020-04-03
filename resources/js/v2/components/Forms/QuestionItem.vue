<template>
  <ListViewFormGroup :labelFor="inputId" class="question-item">
    <!-- TODO: Min や Max といったバリデーションルールにも対応する -->
    <!-- TODO: せっかくVue使ってるので、バリデーションエラーは即座に表示したい -->
    <template #label>
      {{ name }}
      <span class="badge is-danger" v-if="required">必須</span>
    </template>
    <template #description>
      <p class="question-item__description is-text-color" v-if="description">
        {{ description }}
      </p>
      <p class="question-item__description" v-if="validationNotice">
        {{ validationNotice }}
      </p>
    </template>
    <component
      :is="componentIs"
      :inputId="inputId"
      :inputName="inputName"
      :required="required"
      :invalid="invalid"
      :value="value"
      :options="computedOptions"
      :numberMin="numberMin"
      :numberMax="numberMax"
      :allowedTypes="computedAllowedTypes"
      :disabled="disabled"
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
      type: [String, Array]
    },
    options: {
      type: Array
    },
    numberMin: {
      type: Number,
      default: null
    },
    numberMax: {
      type: Number,
      default: null
    },
    allowedTypes: {
      type: Array
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    componentIs() {
      return `Question${this.type.charAt(0).toUpperCase() + this.type.slice(1)}`
    },
    inputId() {
      if (['radio', 'checkbox'].indexOf(this.type) >= 0) {
        return undefined
      }
      return `question-${this.questionId}`
    },
    inputName() {
      if (this.type === 'checkbox') {
        return `answers[${this.questionId}][]`
      }
      return `answers[${this.questionId}]`
    },
    computedOptions() {
      if (['radio', 'select', 'checkbox'].indexOf(this.type) >= 0) {
        return this.options
      }
      return undefined
    },
    computedAllowedTypes() {
      if (this.type === 'upload') {
        return this.allowedTypes
      }
      return undefined
    },
    validationNotice() {
      let text = ''
      switch (this.type) {
        case 'text':
        case 'textarea': {
          if (this.numberMin !== null || this.numberMax !== null) {
            if (this.numberMin !== null) {
              text += `${this.numberMin}文字以上`
            }
            if (this.numberMax !== null) {
              text += `${this.numberMax}文字以下`
            }
            text += 'で入力してください'
          }
          break
        }
        case 'number': {
          if (this.numberMin !== null || this.numberMax !== null) {
            if (this.numberMin !== null) {
              text += `${this.numberMin}以上`
            }
            if (this.numberMax !== null) {
              text += `${this.numberMax}以下`
            }
            text += 'の値を入力してください'
          }
          break
        }
        case 'upload': {
          if (this.numberMax !== null) {
            text += `${this.numberMax}KB以下としてください。`
          }
          if (this.allowedTypes && this.allowedTypes.length > 0) {
            text += `対応形式 : ${this.allowedTypes.join(', ')}`
          }
          break
        }
        case 'checkbox': {
          if (this.numberMin !== null || this.numberMax !== null) {
            if (this.numberMin !== null) {
              text += `${this.numberMin}個以上`
            }
            if (this.numberMax !== null) {
              text += `${this.numberMax}個以下`
            }
            text += 'の項目を選択してください'
          }
          break
        }
        default: {
          break
        }
      }
      return text
    }
  }
}
</script>

<style lang="scss" scoped>
.question-item {
  &__description {
    margin: 0 0 $spacing-xs;
    &:last-child {
      margin: 0;
    }
    &.is-text-color {
      color: $color-text;
    }
  }
}
</style>
