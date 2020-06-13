<template>
  <div class="selector">
    <AppContainer>
      <AppDropdown
        menuFluid
        class="selector-dropdown"
        :items="circles"
        :name="name"
      >
        <template #button="{ toggle, props }">
          <button class="selector-button" @click="toggle" v-bind="props">
            <div class="selector-button__content">
              <div class="selector-button__name">{{ selectedCircleName }}</div>
              <div class="selector-button__group-name">
                {{ selectedCircleGroupName }}
              </div>
            </div>
            <i class="fas fa-caret-down"></i>
          </button>
        </template>
        <template #item="{ item }">
          <AppDropdownItem
            class="selector-item"
            component-is="a"
            :href="item.href"
          >
            <div class="selector-item__name">{{ item.name }}</div>
            <div class="selector-item__group-name">{{ item.group_name }}</div>
          </AppDropdownItem>
        </template>
      </AppDropdown>
    </AppContainer>
  </div>
</template>

<script>
import AppContainer from './AppContainer.vue'
import AppDropdown from './AppDropdown.vue'
import AppDropdownItem from './AppDropdownItem.vue'

export default {
  components: {
    AppContainer,
    AppDropdown,
    AppDropdownItem
  },
  props: {
    name: {
      type: String,
      default: 'top-circle-selector'
    },
    circles: {
      // {name: String, group_name: String, href: String}
      type: Array,
      required: true
    },
    selectedCircleName: {
      type: String,
      required: true
    },
    selectedCircleGroupName: {
      type: String,
      required: true
    }
  }
}
</script>

<style lang="scss" scoped>
.selector {
  background: $color-bg-white;
  border-bottom: 1px solid $color-border;
  padding: $spacing 0;
  &-dropdown {
    display: block;
    width: 100%;
  }
  &-button {
    align-items: center;
    appearance: none;
    background: $color-bg-white;
    border: none;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    padding: 0;
    text-align: left;
    width: 100%;
    &__content {
      flex: 1;
      margin-right: $spacing;
    }
    &__name {
      font-weight: bold;
      margin: 0 0 $spacing-sm;
    }
    &__group-name {
      color: $color-muted;
      font-size: 0.75rem;
    }
  }
  &-item {
    width: 100%;
    &__name {
      font-weight: bold;
      margin: 0 0 $spacing-sm;
    }
    &__group-name {
      font-size: 0.75rem;
      opacity: 0.75;
    }
  }
}
</style>
