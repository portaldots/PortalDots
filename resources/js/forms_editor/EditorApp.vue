<template>
  <div class="editor-wrap">
    <div
      class="alert alert-danger rounded-0 m-0 d-block d-md-none fixed-bottom text-center"
    >
      フォームエディターは、パソコンのみ対応しています。
    </div>
    <editor-loading v-show="!loaded" />
    <editor-header />
    <editor-content />
    <editor-sidebar />
    <editor-error v-show="is_error" />
  </div>
</template>

<script>
import EditorLoading from './components/EditorLoading.vue'
import EditorHeader from './components/EditorHeader.vue'
import EditorContent from './components/EditorContent.vue'
import EditorSidebar from './components/EditorSidebar.vue'
import EditorError from './components/EditorError.vue'
import { FETCH, TOGGLE_OPEN_STATE, ITEM_HEADER } from './store/editor'
import { SAVE_STATUS_SAVING } from './store/status'

const on_before_unload = event => {
  event.preventDefault()
  // eslint-disable-next-line no-param-reassign
  event.returnValue = ''
}

export default {
  components: {
    EditorLoading,
    EditorHeader,
    EditorContent,
    EditorSidebar,
    EditorError
  },
  async mounted() {
    await this.$store.dispatch(`editor/${FETCH}`)
    if (this.$store.state.editor.questions.length === 0) {
      this.$store.commit(`editor/${TOGGLE_OPEN_STATE}`, {
        item_id: ITEM_HEADER
      })
    }
  },
  computed: {
    loaded() {
      return this.$store.state.editor.loaded
    },
    is_error() {
      return this.$store.state.status.is_error
    },
    is_saving() {
      return this.$store.state.status.save_status === SAVE_STATUS_SAVING
      // is_saving の状態は、以下で watch されている
    }
  },
  watch: {
    is_saving(value) {
      if (value) {
        window.addEventListener('beforeunload', on_before_unload)
      } else {
        window.removeEventListener('beforeunload', on_before_unload)
      }
    },
    is_error(value) {
      if (value) {
        window.removeEventListener('beforeunload', on_before_unload)
      }
    }
  }
}
</script>
