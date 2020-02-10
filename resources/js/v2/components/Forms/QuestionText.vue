<template>
  <BaseQuestion
    :inputId="inputId"
    :name="name"
    :description="description"
    :required="required"
    :invalid="invalid"
  >
    <input
      type="text"
      :id="inputId"
      :name="inputName"
      class="form-control"
      :value="value"
    />
  </BaseQuestion>
</template>

<script>
import BaseQuestion from './BaseQuestion.vue'

export default {
  components: {
    BaseQuestion
  },
  props: {
    inputId: {
      type: String,
      default: null
    },
    inputName: {
      type: String,
      default: null
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
      type: String,
      default: null
    }
  }
}
</script>
