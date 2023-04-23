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
      "resources/js/app.js",
    ]),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
  ],
  resolve: {
    alias: {
      "@": "/resources",
    },
  },
});
