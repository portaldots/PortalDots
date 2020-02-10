<template>
  <ListViewFormGroup :labelFor="inputId">
    <template #label>
      {{ name }}
      {{ required ? '(必須)' : '' }}
    </template>
    <template #description>
      {{ description }}
    </template>
    <slot />
    <template #invalid v-if="invalid">
      {{ invalid }}
    </template>
  </ListViewFormGroup>
</template>

<script>
import ListViewFormGroup from '../ListViewFormGroup.vue'

export default {
  components: {
    ListViewFormGroup
  },
  props: {
    inputId: {
      type: String,
      default: null
    },
    name: {
      type: String,
      default: null
    },
    description: {
      type: String,
      default: null
    },
    required: {
      type: Boolean,
      default: false
    },
    invalid: {
      type: String,
      default: null
    }
  }
}
</script>
