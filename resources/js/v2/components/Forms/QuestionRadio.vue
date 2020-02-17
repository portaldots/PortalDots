<template>
  <div class="radio">
    <label
      class="radio__label"
      v-for="(option, index) in options"
      :key="`${option}_${index}`"
    >
      <input
        class="radio__input"
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

<style lang="scss" scoped>
.radio {
  $input-spacing: $spacing-md + $spacing-xs;
  &__label {
    display: block;
    margin: 0 0 $spacing-sm;
    padding: 0 0 0 #{$input-spacing};
    position: relative;
  }
  &__input {
    margin: 0.4rem 0 0 #{-$input-spacing};
    position: absolute;
  }
}
</style>
