<template>
  <div data-turbolinks="false">
    <vue-simplemde
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
import Simplemde from 'simplemde'
import VueSimplemde from 'vue-simplemde'

export default {
  components: {
    VueSimplemde
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
            action: Simplemde.toggleBold,
            className: 'fas fa-bold',
            title: '太字'
          },
          {
            name: 'italic',
            action: Simplemde.toggleItalic,
            className: 'fas fa-italic',
            title: '斜体'
          },
          {
            name: 'strikethrough',
            action: Simplemde.toggleStrikethrough,
            className: 'fas fa-strikethrough',
            title: '取り消し線'
          },
          {
            name: 'heading',
            action: Simplemde.toggleHeadingSmaller,
            className: 'fas fa-heading',
            title: '見出し'
          },
          '|',
          {
            name: 'quote',
            action: Simplemde.toggleBlockquote,
            className: 'fas fa-quote-left',
            title: '引用'
          },
          {
            name: 'unordered-list',
            action: Simplemde.toggleUnorderedList,
            className: 'fas fa-list-ul',
            title: '箇条書き'
          },
          {
            name: 'ordered-list',
            action: Simplemde.toggleOrderedList,
            className: 'fas fa-list-ol',
            title: '番号付きリスト'
          },
          '|',
          {
            name: 'link',
            action: Simplemde.drawLink,
            className: 'fas fa-link',
            title: 'リンク'
          },
          // TODO: 画像アップロード機能の実装
          // {
          //   name: "image",
          //   action: Simplemde.drawImage,
          //   className: "far fa-image",
          //   title: "画像",
          // },
          {
            name: 'table',
            action: Simplemde.drawTable,
            className: 'fas fa-table',
            title: '表'
          },
          {
            name: 'horizontal-rule',
            action: Simplemde.drawHorizontalRule,
            className: 'fas fa-minus',
            title: '水平線'
          },
          '|',
          {
            name: 'preview',
            action: Simplemde.togglePreview,
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
@import '~simplemde/dist/simplemde.min.css';
</style>

<style lang="scss">
.vue-simplemde {
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
