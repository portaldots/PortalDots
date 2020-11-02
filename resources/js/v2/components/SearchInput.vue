<template>
  <div class="search-input">
    <input
      class="form-control search-input__input"
      type="search"
      :name="name"
      :placeholder="placeholder"
      :value="defaultValue"
    />
    <i class="fas fa-fw fa-search search-input__icon"></i>
  </div>
</template>

<script>
export default {
  props: {
    name: {
      type: String,
      required: true
    },
    defaultValue: {
      type: String,
      required: false,
      default: ''
    },
    placeholder: {
      type: String,
      required: false,
      default: ''
    }
  }
}
</script>

<style lang="scss" scoped>
.search-input {
  $icon-size: 1.1rem;

  position: relative;
  &__input {
    padding-left: $spacing-md + $spacing-sm + $spacing-xs + $icon-size;
  }
  &__icon {
    font-size: $icon-size;
    left: $spacing-md;
    opacity: 0.4;
    pointer-events: none;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
  }
}
</style>
