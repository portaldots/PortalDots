<template>
  <div class="ui-primary-color-picker">
    <input
      type="hidden"
      :name="inputNameH"
      :value="!waitForReset ? hslValue[0] : 'null'"
      v-if="inputNameH"
    />
    <input
      type="hidden"
      :name="inputNameS"
      :value="!waitForReset ? hslValue[1] : 'null'"
      v-if="inputNameS"
    />
    <input
      type="hidden"
      :name="inputNameL"
      :value="!waitForReset ? hslValue[2] : 'null'"
      v-if="inputNameL"
    />
    <input
      type="color"
      v-model="hexValue"
      class="form-control ui-primary-color-picker__input"
    />
    <button type="button" class="btn is-secondary is-sm" @click="reset">
      デフォルトにもどす
    </button>
  </div>
</template>

<style lang="scss" scoped>
.ui-primary-color-picker {
  align-items: center;
  display: flex;
  &__input {
    height: 2rem;
    margin-right: $spacing-sm;
    padding: $spacing-xs;
    width: 4rem;
  }
}
</style>

<script>
import convert from 'color-convert'

export default {
  props: {
    inputNameH: {
      type: String,
      default: null
    },
    inputNameS: {
      type: String,
      default: null
    },
    inputNameL: {
      type: String,
      default: null
    },
    defaultHslaValue: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      hexValue: '#000000',
      waitForReset: false
    }
  },
  mounted() {
    this.hexValue = this.convertHslaToHex(this.defaultHslaValue)
  },
  computed: {
    hslValue() {
      return this.convertHexToHsl(this.hexValue)
    }
  },
  methods: {
    convertHslaToHex(hsla) {
      const hsl_array = hsla
        .replace('hsla(', '')
        .replace(')', '')
        .split(',')
        .map((value) => parseInt(value.trim(), 10))
      return `#${convert.hsl.hex(hsl_array[0], hsl_array[1], hsl_array[2])}`
    },
    convertHexToHsl(hex) {
      return convert.hex.hsl(hex.replace('#', ''))
    },
    reset() {
      this.hexValue = '#1a79f4'
      this.$nextTick(() => {
        this.waitForReset = true
      })
    }
  },
  watch: {
    hexValue(value) {
      this.waitForReset = false
      const hsl = this.convertHexToHsl(value)
      document.documentElement.style.setProperty(
        '--color-primary',
        `hsla(${hsl[0]}, ${hsl[1]}%, ${hsl[2]}%, 1)`
      )
      document.documentElement.style.setProperty(
        '--color-primary-hover',
        `hsla(${hsl[0]}, ${hsl[1]}%, ${hsl[2]}%, 0.8)`
      )
      document.documentElement.style.setProperty(
        '--color-focus-primary',
        `hsla(${hsl[0]}, ${hsl[1]}%, ${hsl[2]}%, 0.25)`
      )
    }
  }
}
</script>
