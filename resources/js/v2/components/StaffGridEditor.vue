<template>
  <div class="staff_grid_editor">
    <iframe
      :src="editorUrl"
      class="staff_grid_editor-frame"
      allowtransparency="true"
    />
  </div>
</template>

<script>
export default {
  props: {
    editorUrl: {
      type: String,
      required: true
    }
  }
}
</script>

<style lang="scss" scoped>
.staff_grid_editor {
  inset: 0;
  position: absolute;
  &-frame {
    border: 0;
    height: 100%;
    width: 100%;
  }
}
</style>
