<template>
  <div>
    <vue-tags-input
      v-model="inputTag"
      :tags="tags"
      @tags-changed="tagsChanged"
      :add-on-key="[13, ',']"
      :separators="separators"
      :autocomplete-items="filteredItems"
      :add-only-from-autocomplete="addOnlyFromAutocomplete"
      :is-duplicate="isDuplicate"
      :placeholder="
        tags.length === 0 && placeholderEmpty ? placeholderEmpty : placeholder
      "
      :autocomplete-min-length="0"
    />
    <template v-if="inputName">
      <input
        type="hidden"
        :name="`${inputName}[]`"
        v-for="tag in tags"
        :value="tag.text"
        :key="tag.text"
      />
    </template>
  </div>
</template>

<script>
import VueTagsInput from '@johmun/vue-tags-input'

export default {
  components: {
    VueTagsInput
  },
  props: {
    inputName: {
      type: String,
      default: null
    },
    defaultTags: {
      type: Array,
      default: () => []
    },
    autocompleteItems: {
      type: Array,
      default: () => []
    },
    addOnlyFromAutocomplete: {
      type: Boolean,
      default: false
    },
    placeholder: {
      type: String,
      default: 'タグを追加'
    },
    placeholderEmpty: {
      type: String,
      required: false
    }
  },
  data() {
    return {
      inputTag: '',
      tags: []
    }
  },
  mounted() {
    this.tags = this.defaultTags
  },
  methods: {
    tagsChanged(newTags) {
      this.tags = newTags
      this.$emit('tags-changed', newTags)
    },
    isDuplicate(tags, tag) {
      return (
        tags
          .map((t) => t.text.toLowerCase())
          .indexOf(tag.text.toLowerCase()) !== -1
      )
    }
  },
  computed: {
    separators() {
      // eslint-disable-next-line no-irregular-whitespace
      return [';', '、', ' ', '　']
    },
    filteredItems() {
      return this.autocompleteItems.filter((i) => {
        return i.text.toLowerCase().indexOf(this.inputTag.toLowerCase()) !== -1
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.vue-tags-input {
  background-color: $color-bg-white;
  max-width: 100%;
}
</style>

<style lang="scss">
.vue-tags-input {
  $default-box-shadow: inset 0 0.5px 1.5px $color-box-shadow-light;
  .ti-input {
    border: 1px solid $color-border;
    border-radius: $border-radius;
    line-height: 1.6;
    padding: $spacing-sm $spacing-md;
    transition: #{$transition-base-fast} background-color,
      #{$transition-base-fast} box-shadow, #{$transition-base-fast} border-color;
  }
  &.ti-focus .ti-input {
    border-color: $color-primary;
    box-shadow: $default-box-shadow,
      0 0 0 3px rgba(var(--rgb-color-primary), 0.25);
  }
  .ti-tag {
    background: $color-primary;
    color: $color-bg-white;
  }
  .ti-autocomplete {
    background: $color-bg-white;
    border: none;
    box-shadow: 0 0.25rem 0.75rem $color-box-shadow;
  }
  .ti-item {
    margin: 0;
    padding: $spacing-xs $spacing-md;
    > div {
      padding: 0;
    }
  }
  .ti-selected-item {
    background: $color-primary;
    color: $color-bg-white;
  }
  .ti-new-tag-input-wrapper {
    &:first-child {
      margin-left: 0;
      padding-left: 0;
    }
  }
  .ti-new-tag-input {
    background: transparent;
    caret-color: $color-primary;
  }
}
</style>
