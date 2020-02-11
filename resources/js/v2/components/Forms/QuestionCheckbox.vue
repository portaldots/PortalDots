<template>
  <div>
    <label
      class="checkbox"
      v-for="(option, index) in options"
      :key="`${option}_${index}`"
    >
      <input
        type="checkbox"
        :name="inputName"
        :required="computedRequired"
        :checked="Array.isArray(value) && value.includes(option)"
        :value="option"
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

<style lang="scss" scoped>
.checkbox {
  display: block;
  margin: 0 0 $spacing-sm;
}
</style>
