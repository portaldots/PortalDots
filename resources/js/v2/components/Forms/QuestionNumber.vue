<template>
  <input
    type="number"
    :id="inputId"
    :name="inputName"
    class="form-control"
    :value="value"
    :required="required"
  />
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
    }
  }
}
</script>
