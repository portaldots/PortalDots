import { copyFile } from "node:fs/promises";
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
  css: {
    preprocessorOptions: {
      scss: {
        additionalData: `@use "@/sass/variables" as *;`,
      },
    },
  },
  plugins: [
    laravel([
      "resources/sass/bootstrap.scss",
      "resources/sass/app.scss",
      "resources/js/v2/app.js",
      "resources/js/forms_editor/index.js",
    ]),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    {
      name: "include-fontawesome-license",
      apply: "build",
      enforce: "post",
      async closeBundle() {
        await copyFile(
          new URL("./node_modules/@fortawesome/fontawesome-free/LICENSE.txt", import.meta.url),
          new URL("./public/build/FONT_AWESOME_LICENSE.txt", import.meta.url),
        );
      },
    },
  ],
  resolve: {
    dedupe: ["@codemirror/state"],
    alias: {
      "@": "/resources",
      // ブラウザー上でVueテンプレートをコンパイルする必要があるため、フルビルド版のVueを利用する。
      // https://ja.vuejs.org/guide/scaling-up/tooling.html#project-scaffolding
      vue: "vue/dist/vue.esm-bundler.js",
    },
  },
});
