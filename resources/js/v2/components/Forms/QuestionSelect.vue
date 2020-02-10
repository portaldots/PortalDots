<template>
  <select
    :id="inputId"
    :name="inputName"
    class="form-control"
    :required="required"
  >
    <option></option>
    <option
      v-for="(option, index) in options"
      :key="`${option}_${index}`"
      :selected="option === value"
      :value="option"
    >
      {{ option }}
    </option>
  </select>
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
    }
  }
}
</script>
