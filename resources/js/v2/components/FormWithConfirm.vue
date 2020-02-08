<template>
  <form :action="action" :method="method" @submit="onSubmit">
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
