<template>
  <TagsInput
    element-id="tags"
    v-model="selectedTags"
    :existing-tags="[
      { key: 'web-development', value: 'Web Development' },
      { key: 'php', value: 'PHP' },
      { key: 'javascript', value: 'JavaScript' }
    ]"
  ></TagsInput>
</template>

<script>
import VoerroTagsInput from '@voerro/vue-tagsinput'

export default {
  components: {
    TagsInput: VoerroTagsInput
  },
  data() {
    return {
      selectedTags: [],
      // 日本語入力中のEnterキーであってもタグ追加と認識されてしまう。
      // そのため、isAdd が true のときのみタグ追加する。
      isAdd: false
    }
  },
  methods: {
    onKeydown(e) {
      if (e.keyCode === 13) {
        this.isAdd = true
      }
    },
    async beforeAddingTag() {
      await this.$nextTick()
      if (this.isAdd) {
        this.isAdd = false
        return true
      }
      return false
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../node_modules/@voerro/vue-tagsinput/dist/style.css';
</style>
