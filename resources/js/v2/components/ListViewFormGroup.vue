<template>
  <ListViewBaseItem
    class="listview-form-group"
    :class="{ 'is-invalid': $slots.invalid }"
  >
    <component
      :is="labelFor ? 'label' : 'div'"
      v-bind="labelFor ? { for: labelFor } : {}"
      class="listview-form-group__label"
    >
      <slot name="label" />
    </component>
    <div class="listview-form-group__description" v-if="$slots.description">
      <slot name="description" />
    </div>
    <div class="listview-form-group__body">
      <slot />
    </div>
    <div
      class="listview-form-group__invalid-message"
      role="alert"
      v-if="$slots.invalid"
    >
      <slot name="invalid" />
    </div>
  </ListViewBaseItem>
</template>

<script>
import ListViewBaseItem from './ListViewBaseItem.vue'

export default {
  components: {
    ListViewBaseItem
  },
  props: {
    labelFor: {
      type: String,
      default: null
    }
  }
}
</script>

<style lang="scss" scoped>
.listview-form-group {
  border: 0;
  padding-bottom: $spacing-lg / 2;
  padding-top: $spacing-lg / 2;
  &:first-child {
    padding-top: $spacing;
  }
  &:last-child {
    padding-bottom: $spacing;
  }
  &.is-invalid {
    color: $color-danger;
  }
  &__label {
    display: block;
    font-weight: bold;
    line-height: 1.1;
    margin: 0 0 $spacing-sm;
  }
  &__description {
    color: $color-muted;
    font-size: 0.9rem;
    margin: 0 0 $spacing-sm;
  }
  &__body {
    margin: 0;
  }
  &__invalid-message {
    font-weight: bold;
  }
}
</style>
