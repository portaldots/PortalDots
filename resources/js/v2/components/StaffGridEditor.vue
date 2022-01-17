<template>
  <div class="staff_grid_editor">
    <div class="staff_grid_editor-loading" v-if="!isLoaded">
      <i class="fas fa-spinner fa-pulse fa-2x text-primary"></i>
    </div>
    <iframe
      :src="editorUrl"
      class="staff_grid_editor-frame"
      allowtransparency="true"
      @load="handleLoad"
    ></iframe>
  </div>
</template>

<script>
export default {
  props: {
    editorUrl: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      isLoaded: false
    }
  },
  methods: {
    handleLoad() {
      this.isLoaded = true
    }
  }
}
</script>

<style lang="scss" scoped>
.staff_grid_editor {
  inset: 0;
  position: absolute;
  &-loading {
    align-items: center;
    display: flex;
    inset: 0;
    justify-content: center;
    position: absolute;
  }
  &-frame {
    border: 0;
    height: 100%;
    width: 100%;
  }
}
</style>
