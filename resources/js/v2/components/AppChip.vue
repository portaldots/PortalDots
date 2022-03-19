<template>
  <a :href="href" :target="target" class="chip">
    <div class="chip-body">
      <div class="chip__icon" v-if="$slots.icon">
        <slot name="icon" />
      </div>
      <div class="chip__content">
        <slot />
      </div>
    </div>
  </a>
</template>

<script>
export default {
  props: {
    href: {
      type: String,
      required: true
    },
    target: {
      type: String,
      default: ''
    }
  }
}
</script>

<style lang="scss" scoped>
.chip {
  $chip-spacing-x: $spacing-sm;

  align-items: stretch;
  background-color: $color-bg-surface;
  border: 1px solid $color-border;
  border-radius: $border-radius;
  color: currentColor;
  display: inline-block;
  display: inline-flex;
  font-weight: 500;
  height: 2.25rem;
  line-height: 1.4;
  max-width: 100%;
  transition: $transition-base background-color;
  &-body {
    align-items: center;
    display: inline-flex;
    max-width: 100%;
    overflow: hidden;
    padding: 0 $chip-spacing-x;
    white-space: nowrap;
  }
  &:hover,
  &:focus {
    background-color: $color-bg-light;
    text-decoration: none;
  }
  &__icon {
    color: $color-muted;
    font-size: 1.15rem;
    margin-right: 0.1rem;
  }
  &__content {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
}
</style>
