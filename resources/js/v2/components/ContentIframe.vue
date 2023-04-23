<script setup>
import { ref, defineProps } from "vue";

const isLoaded = ref(false);

const props = defineProps({
  src: {
    type: String,
    default: "",
    required: true,
  },
});

function handleLoad() {
  isLoaded.value = true;
}
</script>

<template>
  <div class="content_iframe content__body__content_iframe">
    <div v-if="!isLoaded" class="content_iframe-loading">
      <i class="fas fa-spinner fa-pulse fa-2x text-primary" />
    </div>
    <iframe
      :src="props.src"
      class="content__body__iframe"
      allowtransparency="true"
      @load="handleLoad"
    />
  </div>
</template>

<style lang="scss" scoped>
.content_iframe {
  height: 100%;
  position: relative;
  &-loading {
    align-items: center;
    display: flex;
    inset: 0;
    justify-content: center;
    position: absolute;
  }
  &-frame {
    border: 0;
    display: block;
    height: 100%;
    width: 100%;
  }
}
</style>
