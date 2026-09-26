const { defineConfig } = require("eslint/config");
const prettier = require("eslint-config-prettier");
const vue = require("eslint-plugin-vue");
const globals = require("globals");

module.exports = defineConfig([
  {
    ignores: ["node_modules/**", "public/build/**"],
  },
  ...vue.configs["flat/recommended"],
  {
    files: ["resources/**/*.{js,vue}"],
    languageOptions: {
      ecmaVersion: "latest",
      sourceType: "module",
      globals: {
        ...globals.browser,
        Atomics: "readonly",
        SharedArrayBuffer: "readonly",
      },
    },
    rules: {
      "no-param-reassign": [
        "error",
        {
          props: true,
          ignorePropertyModificationsFor: ["state"],
        },
      ],
      "no-unexpected-multiline": "error",
      "no-unreachable": "error",
      camelcase: "warn",
    },
  },
  prettier,
]);
