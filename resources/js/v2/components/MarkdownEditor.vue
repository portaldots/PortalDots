<template>
  <div data-turbolinks="false" class="markdown-editor">
    <md-editor
      class="markdown-editor"
      v-model="content"
      language="ja-JP"
      preview-theme="markdowneditor"
      :preview="false"
      :toolbars="toolbars"
      :footers="footers"
      :theme="colorScheme"
      ref="editorRef"
    >
      <template #defToolbars>
        <NormalToolbar title="プレビュー" @onClick="togglePreview">
          <template #trigger>
            <svg class="md-editor-icon" aria-hidden="true">
              <use xlink:href="#md-editor-icon-preview"></use>
            </svg>
            プレビュー
          </template>
        </NormalToolbar>
      </template>
      <template #defFooters>
        <span>
          <a href="/staff/markdown-guide" target="_blank">Markdownガイド</a>
        </span>
      </template>
    </md-editor>
    <input type="hidden" :name="inputName" v-if="inputName" :value="content" />
  </div>
</template>

<script>
import MdEditor from "md-editor-v3";
import "md-editor-v3/lib/style.css";
import JA_JP from "@vavt/md-editor-extension/dist/locale/jp-JP";

MdEditor.config({
  editorConfig: {
    languageUserDefined: {
      "ja-JP": {
        ...JA_JP,
        toolbarTips: {
          ...JA_JP.toolbarTips,
          title: "見出し",
          revoke: "取り消す",
          next: "やり直す",
        },
        linkModalTips: {
          ...JA_JP.linkModalTips,
          linkTitle: "リンクを追加",
          imageTitle: "画像を追加",
          descLabel: "表示文字列:",
          descLabelPlaceHolder: "",
          urlLabel: "リンク先:",
          urlLabelPlaceHolder: "",
          buttonOK: "OK",
        },
        footer: {
          ...JA_JP.footer,
          markdownTotal: "文字数",
          scrollAuto: "自動スクロール",
        },
      },
    },
  },
});

const isDarkQuery = window.matchMedia("(prefers-color-scheme: dark)");

export default {
  components: {
    MdEditor,
    NormalToolbar: MdEditor.NormalToolbar,
  },
  props: {
    inputName: {
      type: String,
      default: null,
    },
    defaultValue: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      content: "",
      colorScheme: "light",
    };
  },
  computed: {
    toolbars() {
      return [
        "bold",
        "italic",
        "strikeThrough",
        "title",
        "-",
        "quote",
        "unorderedList",
        "orderedList",
        "-",
        "link",
        "table",
        "-",
        "revoke",
        "next",
        "-",
        0,
      ];
    },
    footers() {
      return ["markdownTotal", "=", 0, "scrollSwitch"];
    },
  },
  mounted() {
    this.content = this.defaultValue;
    this.handleChangeColorScheme();
    isDarkQuery.addEventListener("change", this.handleChangeColorScheme);
  },
  unmounted() {
    isDarkQuery.removeEventListener("change", this.handleChangeColorScheme);
  },
  methods: {
    handleChangeColorScheme() {
      const metaColorScheme = document.querySelector("meta[name=color-scheme]")
        .content;

      if (metaColorScheme === "light" || metaColorScheme === "dark") {
        this.colorScheme = metaColorScheme;
      } else if (isDarkQuery.matches) {
        this.colorScheme = "dark";
      } else {
        this.colorScheme = "light";
      }
    },
    togglePreview() {
      this.$refs.editorRef.togglePreview();
    },
  },
};
</script>

<style lang="scss">
@use "@/sass/v2/modules/_markdown";

.markdown-editor {
  width: 100%;
  height: 50em;
  max-height: 80svi;
}

.markdowneditor-theme {
  @extend .markdown;
}

.md-editor-toolbar-wrapper .md-editor-toolbar-item {
  display: inline-flex;
  align-items: center;
  font-size: 13px;
}
</style>
