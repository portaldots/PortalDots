<template>
  <div>
    <vue-tags-input
      v-model="inputTag"
      :tags="tags"
      @tags-changed="tagsChanged"
      :add-on-key="[13]"
      :separators="separators"
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
    }
  },
  data() {
    return {
      inputTag: '',
      tags: []
    }
  },
  methods: {
    tagsChanged(newTags) {
      this.tags = newTags
      this.$emit('tags-changed', newTags)
    }
  },
  computed: {
    separators() {
      // eslint-disable-next-line no-irregular-whitespace
      return [';', ',', ' ', '　']
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
    .ti-new-tag-input {
      caret-color: $color-primary;
    }
  }
  &.ti-focus .ti-input {
    border-color: $color-primary;
    box-shadow: $default-box-shadow, 0 0 0 3px rgba($color-primary, 0.25);
  }
  .ti-tag {
    background: $color-primary;
  }
}
</style>
