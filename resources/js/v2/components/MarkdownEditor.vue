<template>
  <div data-turbolinks="false" class="markdown-editor">
    <md-editor
      class="markdown-editor"
      v-model="content"
      language="ja-JP"
      preview-theme="markdowneditor"
      :toolbars="toolbars"
      :theme="colorScheme"
    />
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
      "ja-JP": JA_JP,
    },
  },
});

const isDarkQuery = window.matchMedia("(prefers-color-scheme: dark)");

export default {
  components: {
    MdEditor,
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
        "=",
        "preview",
      ];
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
</style>
