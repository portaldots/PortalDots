<template>
  <div>
    <vue-tags-input
      v-model="inputTag"
      :tags="tags"
      @tags-changed="tagsChanged"
      :add-on-key="[13, ',']"
      :separators="separators"
      :autocomplete-items="filteredItems"
      :is-duplicate="isDuplicate"
      placeholder="タグを追加"
    />
    <template v-if="inputName">
      <input
        type="hidden"
        name="tags[]"
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
        tags.map(t => t.text.toLowerCase()).indexOf(tag.text.toLowerCase()) !==
        -1
      )
    }
  },
  computed: {
    separators() {
      // eslint-disable-next-line no-irregular-whitespace
      return [';', '、', ' ', '　']
    },
    filteredItems() {
      return this.autocompleteItems.filter(i => {
        return i.text.toLowerCase().indexOf(this.inputTag.toLowerCase()) !== -1
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.vue-tags-input {
  max-width: 100%;
}
</style>

<style lang="scss">
.vue-tags-input {
  $default-box-shadow: inset 0 0.5px 1.5px rgba($color-text, 0.1);
  .ti-input {
    border-radius: $border-radius;
    line-height: 1.6;
    padding: $spacing-sm $spacing-md;
    transition: #{$transition-base-fast} background-color,
      #{$transition-base-fast} box-shadow, #{$transition-base-fast} border-color;
  }
  &.ti-focus .ti-input {
    border-color: $color-primary;
    box-shadow: $default-box-shadow, 0 0 0 3px rgba($color-primary, 0.25);
  }
  .ti-tag {
    background: $color-primary;
  }
  .ti-autocomplete {
    box-shadow: 0 0.25rem 0.75rem rgba($color-muted, 0.25);
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
  }
  .ti-new-tag-input-wrapper {
    &:first-child {
      margin-left: 0;
      padding-left: 0;
    }
  }
  .ti-new-tag-input {
    caret-color: $color-primary;
  }
}
</style>
