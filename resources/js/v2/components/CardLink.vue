<template>
  <component
    :is="href ? 'a' : 'div'"
    :href="href"
    class="card-link"
    :class="{ 'is-disabled': !href }"
  >
    <div class="card-link-body">
      <div class="card-link__icon">
        <slot name="icon" />
      </div>
      <div class="card-link__title">
        <slot name="title" />
      </div>
      <div class="card-link__description" v-if="$slots.description">
        <slot name="description" />
      </div>
    </div>
  </component>
</template>

<script>
export default {
  props: {
    href: {
      type: String,
      default: "",
    },
  },
};
</script>

<style lang="scss" scoped>
.card-link {
  background: $color-bg-surface;
  border-radius: $border-radius;
  box-shadow: $box-shadow-lv1;
  color: currentcolor;
  display: block;
  height: 100%;
  text-decoration: none;
  &.is-disabled {
    opacity: 0.5;
    pointer-events: none;
  }
  &-body {
    align-items: center;
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
    padding: $spacing-lg $spacing;
    text-align: center;
    transition: #{$transition-base-fast} background-color;
  }
  &:not(.is-disabled):hover > &-body,
  &:not(.is-disabled):focus > &-body {
    background-color: $color-bg-light;
  }
  &__icon {
    align-items: center;
    background-color: $color-primary-light;
    border-radius: $border-radius;
    color: $color-primary;
    display: flex;
    font-size: 1.75rem;
    height: 3rem;
    justify-content: center;
    line-height: 1;
    width: 3rem;
  }
  &.is-disabled &__icon {
    color: $color-muted;
  }
  &__title {
    font-size: 1.1rem;
    font-weight: $font-bold;
    margin-top: $spacing-md;
  }
  &__description {
    color: $color-muted;
    margin-top: $spacing-sm;
  }
}
</style>
