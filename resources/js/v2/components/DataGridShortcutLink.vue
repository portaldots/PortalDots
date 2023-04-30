<template>
  <a :href="url" @click="handleClick" class="data-grid-shortcut-link">
    <i class="fas fa-link data-grid-shortcut-link__icon"></i>
    <span class="data-grid-shortcut-link__label">
      <slot />
    </span>
  </a>
</template>

<script setup>
const props = defineProps({
  url: {
    type: String,
    required: true,
  },
  openEditorByUrl: {
    type: Function,
    required: true,
  },
  editorTitle: {
    type: String,
    required: false,
    default: "",
  },
});

function handleClick(e) {
  if (e.ctrlKey || e.metaKey || e.altKey || e.shiftKey) {
    return;
  }
  e.preventDefault();
  console.log(e);
  props.openEditorByUrl(props.url, props.editorTitle);
}
</script>

<style lang="scss" scoped>
.data-grid-shortcut-link {
  color: $color-text;
  text-decoration: none;
  margin-right: $spacing-md;

  &:hover {
    opacity: 0.75;
  }

  &__icon {
    opacity: 0.4;
    margin-right: $spacing-sm;
  }

  &__label {
    background-image: linear-gradient(
      to right,
      $color-border 0%,
      $color-border 100%
    );
    background-repeat: repeat-x;
    background-size: 100% 1px;
    background-position: 0 100%;
    font-weight: 500;
  }
}
</style>
