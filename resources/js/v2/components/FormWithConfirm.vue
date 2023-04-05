<template>
  <form
    :action="action"
    :method="method"
    @submit="onSubmit"
    class="form-with-confirm"
    :class="{ 'is-inline': inline }"
  >
    <slot />
  </form>
</template>

<script>
// import { reenableSubmit } from '../utils/formDisabling'

export default {
  props: {
    action: {
      type: String,
      required: true
    },
    method: {
      type: String,
      required: true
    },
    confirmMessage: {
      type: String,
      required: true
    },
    inline: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    onSubmit(evt) {
      const ans = window.confirm(this.confirmMessage)
      if (!ans) {
        evt.stopPropagation()
        evt.preventDefault()

        // /* eslint-disable no-restricted-syntax */
        // const submits = document.querySelectorAll('button[type="submit"], input[type="submit"]')
        // console.log(submits)
        // for (const submit of submits) {
        //   submit.removeAttribute('disabled')
        // }
        // /* eslint-enable */
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.form-with-confirm {
  &.is-inline {
    display: inline;
  }
}
</style>
