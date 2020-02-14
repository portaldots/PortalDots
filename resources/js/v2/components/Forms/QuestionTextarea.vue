<template>
  <textarea
    type="text"
    :id="inputId"
    :name="inputName"
    class="form-control"
    rows="3"
    :value="value"
    :required="required"
    :minlength="numberMin"
    :maxlength="numberMax"
    :readonly="disabled"
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
    },
    numberMin: {
      type: Number,
      default: null
    },
    numberMax: {
      type: Number,
      default: null
    },
    disabled: {
      type: Boolean,
      default: false
    }
  }
}
</script>
