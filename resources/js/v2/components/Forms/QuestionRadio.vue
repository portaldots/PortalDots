<template>
  <div class="form-radio">
    <label
      class="form-radio__label"
      v-for="(option, index) in options"
      :key="`${option}_${index}`"
    >
      <input
        class="form-radio__input"
        type="radio"
        :name="inputName"
        :required="required"
        :checked="option === value"
        :value="option"
        :disabled="disabled"
      />
      {{ option }}
    </label>
  </div>
</template>

<script>
export default {
  props: {
    inputId: {
      type: String,
      default: null
    },
    inputName: {
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
    },
    options: {
      type: Array,
      default: () => []
    },
    disabled: {
      type: Boolean,
      default: false
    }
  }
}
</script>
