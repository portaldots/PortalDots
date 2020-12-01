<template>
  <div data-turbolinks="false">
    <vue-easymde
      v-model="content"
      ref="markdownEditor"
      :configs="configs"
      preview-class="markdown"
    />
    <input
      type="hidden"
      :name="inputName"
      :value="this.content"
      v-if="inputName"
    />
  </div>
</template>

<script>
import Easymde from 'easymde'
import VueEasymde from 'vue-easymde'

export default {
  components: {
    VueEasymde
  },
  data() {
    return {
      content: ''
    }
  },
  props: {
    inputName: {
      type: String,
      default: null
    },
    defaultValue: {
      type: String,
      default: ''
    }
  },
  mounted() {
    this.content = this.defaultValue
  },
  computed: {
    configs() {
      return {
        autoDownloadFontAwesome: false,
        spellChecker: false,
        indentWithTabs: false,
        promptURLs: true,
        tabSize: 4,
        status: false,
        toolbar: [
          {
            name: 'bold',
            action: Easymde.toggleBold,
            className: 'fas fa-bold',
            title: '太字'
          },
          {
            name: 'italic',
            action: Easymde.toggleItalic,
            className: 'fas fa-italic',
            title: '斜体'
          },
          {
            name: 'strikethrough',
            action: Easymde.toggleStrikethrough,
            className: 'fas fa-strikethrough',
            title: '取り消し線'
          },
          {
            name: 'heading',
            action: Easymde.toggleHeadingSmaller,
            className: 'fas fa-heading',
            title: '見出し'
          },
          '|',
          {
            name: 'quote',
            action: Easymde.toggleBlockquote,
            className: 'fas fa-quote-left',
            title: '引用'
          },
          {
            name: 'unordered-list',
            action: Easymde.toggleUnorderedList,
            className: 'fas fa-list-ul',
            title: '箇条書き'
          },
          {
            name: 'ordered-list',
            action: Easymde.toggleOrderedList,
            className: 'fas fa-list-ol',
            title: '番号付きリスト'
          },
          '|',
          {
            name: 'link',
            action: Easymde.drawLink,
            className: 'fas fa-link',
            title: 'リンク'
          },
          // TODO: 画像アップロード機能の実装
          // {
          //   name: "image",
          //   action: Easymde.drawImage,
          //   className: "far fa-image",
          //   title: "画像",
          // },
          {
            name: 'table',
            action: Easymde.drawTable,
            className: 'fas fa-table',
            title: '表'
          },
          {
            name: 'horizontal-rule',
            action: Easymde.drawHorizontalRule,
            className: 'fas fa-minus',
            title: '水平線'
          },
          '|',
          {
            name: 'preview',
            action: Easymde.togglePreview,
            className: 'far fa-eye no-disable show-title-label',
            title: 'プレビュー'
          },
          '|',
          {
            name: 'guide',
            action: '/staff/markdown-guide',
            className: 'far fa-question-circle show-title-label',
            title: 'Markdownガイド'
          }
        ]
      }
    }
  }
}
</script>

<style lang="scss" scoped>
@import '~easymde/dist/easymde.min.css';
</style>

<style lang="scss">
.vue-easymde {
  .editor-toolbar.fullscreen {
    top: $navbar-height;
  }
  .CodeMirror-fullscreen,
  .editor-preview-side {
    top: calc(#{$navbar-height} + 50px);
  }
  .editor-statusbar {
    .lines::before {
      content: '';
    }
    .lines::after {
      content: '行';
    }
    .words::before {
      content: '';
    }
    .words::after {
      content: '語';
    }
  }
  .show-title-label {
    align-items: center;
    display: inline-flex;
    padding: 0 $spacing-sm;
    width: auto;
    &::after {
      content: attr(title);
      font-size: 0.9rem;
      margin-left: $spacing-xs;
    }
  }
}
</style>
