<template>
  <div class="editor-error">
    <div class="editor-error__modal text-center">
      <p><strong>エラーが発生しました</strong></p>
      <p>恐れ入りますが、ページを再読み込みしてください。</p>
      <p class="mb-0">
        <button @click="reload" class="btn btn-primary">再読み込み</button>
      </p>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    reload() {
      window.location.reload()
    }
  }
}
</script>

<style lang="scss" scoped>
.editor-error {
  position: fixed;
  z-index: 9999;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.25);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;

  &__modal {
    background: #fff;
    width: 100%;
    max-width: 360px;
    padding: 1.5rem;
  }
}
</style>
