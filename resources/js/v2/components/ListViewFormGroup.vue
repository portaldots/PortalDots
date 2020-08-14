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
      :class="{ 'form-addon': $slots.prepend || $slots.append }"
    >
      <div class="form-addon__body" v-if="$slots.prepend">
        <slot name="prepend" />
      </div>
      <slot />
      <div class="form-addon__body" v-if="$slots.append">
        <slot name="append" />
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
    ListViewBaseItem,
  },
  props: {
    labelFor: {
      type: String,
      default: null,
    },
  },
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

<style lang="scss">
.form-addon {
  display: flex;
  flex-wrap: wrap;
  &__body {
    align-items: center;
    background-color: $color-bg-grey;
    border: 1px solid $color-border;
    border-radius: $border-radius;
    color: $color-muted;
    display: flex;
    font-size: $font-size-input;
    justify-content: center;
    line-height: 1.6;
    max-width: 100%;
    overflow: auto;
    padding: $spacing-sm $spacing-md;
    text-align: center;
    word-break: keep-all;
    &:first-child {
      border-bottom-right-radius: 0;
      border-top-right-radius: 0;
      margin-right: -1px;
    }
    &:last-child {
      border-bottom-left-radius: 0;
      border-top-left-radius: 0;
      margin-left: -1px;
    }
  }
  .form-control {
    flex: 1 1 0%;
    &:not(:first-child) {
      border-bottom-left-radius: 0;
      border-top-left-radius: 0;
    }
    &:not(:last-child) {
      border-bottom-right-radius: 0;
      border-top-right-radius: 0;
    }
  }
}
</style>
