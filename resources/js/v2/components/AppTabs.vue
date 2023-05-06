<template>
  <TabGroup>
    <TabList class="tabs-list">
      <Tab
        v-for="tab in props.tabs"
        :key="tab.id"
        as="template"
        v-slot="{ selected }"
      >
        <button
          class="tabs-list__item"
          :class="{
            'is-selected': selected,
          }"
        >
          {{ tab.label }}
        </button>
      </Tab>
    </TabList>
    <TabPanels>
      <TabPanel v-for="tab in props.tabs" :key="tab.id">
        <slot :name="`tab-${tab.id}`" />
      </TabPanel>
    </TabPanels>
  </TabGroup>
</template>

<script setup>
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from "@headlessui/vue";

const props = defineProps({
  tabs: {
    type: Array, // { id: string; label: string }[]
    required: true,
  },
});
</script>

<style lang="scss" scoped>
.tabs {
  &-list {
    border-bottom: 1px solid $color-border;
    display: flex;
    overflow-x: auto;

    &__item {
      appearance: none;
      border: none;
      background: transparent;
      color: $color-text;
      display: block;
      padding: $spacing-md $spacing-sm;
      text-align: center;
      cursor: pointer;

      &.is-selected {
        font-weight: bold;
        position: relative;
        &::before {
          $bar-width: 4px;

          background: $color-primary;
          border-radius: $bar-width $bar-width 0 0;
          bottom: 0;
          content: "";
          display: block;
          height: $bar-width;
          left: 0;
          position: absolute;
          width: 100%;
        }
      }

      &:focus {
        outline: none;
      }

      &:focus-visible {
        outline: 1.5px solid $color-focus-primary;
        outline-offset: -1.5px;
      }
    }
  }
}
</style>
