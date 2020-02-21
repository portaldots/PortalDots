<template>
  <div class="editor-error">
    <div class="editor-error__modal text-center">
      <p>
        <strong>{{ title }}</strong>
      </p>
      <p>{{ message }}</p>
      <p class="mb-0">
        <template v-if="back">
          <button @click="goback" class="btn btn-primary">戻る</button>
        </template>
        <template v-else>
          <button @click="reload" class="btn btn-primary">再読み込み</button>
        </template>
      </p>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    reload() {
      window.location.reload()
    },
    goback() {
      window.history.back(-1)
    }
  },
  props: {
    title: {
      type: String,
      default: 'エラーが発生しました'
    },
    message: {
      type: String,
      default: '恐れ入りますが、ページを再読み込みしてください。'
    },
    back: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.editor-error {
  align-items: center;
  background: rgba(0, 0, 0, 0.25);
  display: flex;
  height: 100vh;
  justify-content: center;
  left: 0;
  padding: 1.5rem;
  position: fixed;
  top: 0;
  width: 100vw;
  z-index: 9999;
  &__modal {
    background: #fff;
    max-width: 360px;
    padding: 1.5rem;
    width: 100%;
  }
}
</style>
