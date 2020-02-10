<template>
  <div>
    <label
      class="checkbox"
      v-for="(option, index) in options"
      :key="`${option}_${index}`"
      :value="option"
    >
      <input
        type="checkbox"
        :name="inputName"
        :required="required"
        :checked="option === value"
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
    }
  }
}
</script>

<style lang="scss" scoped>
.checkbox {
  display: block;
  margin: 0 0 $spacing-sm;
}
</style>
