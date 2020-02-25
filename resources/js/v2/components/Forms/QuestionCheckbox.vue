<template>
  <div class="form-checkbox">
    <label
      class="form-checkbox__label"
      v-for="(option, index) in options"
      :key="`${option}_${index}`"
    >
      <input
        class="form-checkbox__input"
        type="checkbox"
        :name="inputName"
        :required="computedRequired"
        :checked="Array.isArray(value) && value.indexOf(option) >= 0"
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
      type: Array,
      default: () => []
    },
    options: {
      type: Array,
      default: () => []
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    computedRequired() {
      // 選択肢が1つの時の場合のみ、required属性をつける
      //
      // 選択肢が2つ以上の場合、1つ以上のチェックがついていれば良しとしたいため
      if (this.options.length === 1 && this.required) {
        return true
      }
      return false
    }
  }
}
</script>
