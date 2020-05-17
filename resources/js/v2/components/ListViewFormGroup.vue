<template>
  <ListViewBaseItem
    class="listview-form-group"
    :class="{ 'is-invalid': $slots.invalid }"
    noBorder
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
    <div
      class="listview-form-group__body"
      :class="{ 'form-append': $slots.append_before || $slots.append_after }"
    >
      <div class="form-control form-append__body" v-if="$slots.append_before">
        <slot name="append_before" />
      </div>
      <slot />
      <div class="form-control form-append__body" v-if="$slots.append_after">
        <slot name="append_after" />
      </div>
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
  border: none !important; // !important がないと、ListViewBaseItem のスタイルが優先されてしまうことがある
  padding-bottom: $spacing-md;
  padding-top: $spacing-md;
  &:first-child {
    padding-top: $spacing-s;
  }
  &:last-child {
    padding-bottom: $spacing-s;
  }
  &.is-invalid {
    color: $color-danger;
  }
  &__label {
    display: block;
    font-weight: bold;
    margin: 0 0 $spacing-xs;
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
