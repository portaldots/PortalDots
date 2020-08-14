<template>
  <AppDropdown
    :items="dropdownItems"
    name="grid-add-filter"
    class="add_dropdown"
    menu-fluid
  >
    <template #button="{ toggle, props }">
      <button
        type="button"
        class="btn is-secondary is-no-border is-block add_dropdown-btn"
        @click="toggle"
        v-bind="props"
      >
        <span class="text-muted">
          <i class="fas fa-plus-circle fa-fw"></i>
          条件を追加 &nbsp;
          <i class="fas fa-caret-down"></i>
        </span>
      </button>
    </template>
    <template #item="{ item }">
      <AppDropdownItem
        component-is="button"
        @click="(e) => onClickItem(item, e)"
      >
        {{ item.menuLabel }}
      </AppDropdownItem>
    </template>
  </AppDropdown>
</template>

<script>
import AppDropdown from './AppDropdown.vue'
import AppDropdownItem from './AppDropdownItem.vue'

export default {
  components: {
    AppDropdown,
    AppDropdownItem,
  },
  props: {
    dropdownItems: {
      type: Array,
      required: true,
    },
  },
  methods: {
    onClickItem(item, e) {
      this.$emit('clickItem', item, e)
    },
  },
}
</script>

<style lang="scss" scoped>
.add_dropdown {
  display: block;
  &-btn {
    border-radius: 0 !important;
    padding-left: $spacing !important;
    text-align: left !important;
  }
}
</style>
