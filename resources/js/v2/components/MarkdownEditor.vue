<template>
  <div>
    <vue-simplemde v-model="content" ref="markdownEditor" />
    <input
      type="hidden"
      :name="inputName"
      :value="this.content"
      v-if="inputName"
    />
  </div>
</template>

<script>
import VueSimplemde from 'vue-simplemde'

export default {
  components: {
    VueSimplemde
  },
  data() {
    return {
      content: ''
    }
  },
  props: {
    inputName: {
      type: String,
      default: null
    },
    defaultValue: {
      type: String,
      default: ''
    }
  },
  mounted() {
    this.content = this.defaultValue
  }
}
</script>

<style>
@import '~simplemde/dist/simplemde.min.css';
</style>
