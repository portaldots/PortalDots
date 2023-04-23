<template>
  <div class="tags-input">
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
        :value="tag.value || tag.text"
        :key="tag.text"
      />
    </template>
  </div>
</template>

<script>
import VueTagsInput from "@sipec/vue3-tags-input";

export default {
  components: {
    VueTagsInput,
  },
  props: {
    inputName: {
      type: String,
      default: null,
    },
    defaultTags: {
      type: Array,
      default: () => [],
    },
    autocompleteItems: {
      type: Array,
      default: () => [],
    },
    addOnlyFromAutocomplete: {
      type: Boolean,
      default: false,
    },
    placeholder: {
      type: String,
      default: "タグを追加",
    },
    placeholderEmpty: {
      type: String,
      required: false,
    },
  },
  emits: ["tags-changed"],
  data() {
    return {
      inputTag: "",
      tags: [],
    };
  },
  mounted() {
    this.tags = this.defaultTags;
  },
  methods: {
    tagsChanged(newTags) {
      this.tags = newTags;
      this.$emit("tags-changed", newTags);
    },
    isDuplicate(tags, tag) {
      return (
        tags
          .map((t) => t.text.toLowerCase())
          .indexOf(tag.text.toLowerCase()) !== -1
      );
    },
  },
  computed: {
    separators() {
      // eslint-disable-next-line no-irregular-whitespace
      return [";", "、", " ", "　"];
    },
    filteredItems() {
      return this.autocompleteItems.filter(
        (i) => i.text.toLowerCase().indexOf(this.inputTag.toLowerCase()) !== -1
      );
    },
  },
};
</script>

<style lang="scss" scoped>
.tags-input {
  width: 100% !important;
}

.vue-tags-input {
  background-color: $color-bg-surface !important;
  max-width: 100% !important;
}
</style>

<style lang="scss">
.vue-tags-input {
  .ti-input {
    background: $color-form-control !important;
    border: 1px solid $color-border !important;
    border-radius: $border-radius !important;
    line-height: 1.6 !important;
    padding: $spacing-sm $spacing-md !important;
    transition: #{$transition-base-fast} background-color,
      #{$transition-base-fast} box-shadow, #{$transition-base-fast} border-color !important;
  }
  &.ti-focus .ti-input {
    background: $color-form-control-focus !important;
    border-color: $color-primary !important;
    box-shadow: $box-shadow-focus !important;
  }
  .ti-tag {
    background: $color-primary !important;
    border-radius: $border-radius !important;
    color: $color-bg-surface !important;
    &.ti-invalid,
    &.ti-deletion-mark {
      background: $color-danger !important;
    }
  }
  .ti-autocomplete {
    background: $color-bg-surface-3 !important;
    border: none !important;
    border-radius: $border-radius !important;
    box-shadow: $box-shadow-lv3 !important;
    padding: $border-radius 0 !important;
  }
  .ti-item {
    margin: 0 !important;
    padding: $spacing-xs $spacing-md !important;
    > div {
      padding: 0 !important;
    }
  }
  .ti-selected-item {
    background: $color-primary !important;
    color: $color-bg-surface-3 !important;
  }
  .ti-new-tag-input-wrapper {
    &:first-child {
      margin-left: 0 !important;
      padding-left: 0 !important;
    }
  }
  .ti-new-tag-input {
    background: transparent !important;
    caret-color: $color-primary !important;
    color: $color-text !important;
  }
}
</style>
