module.exports = {
  env: {
    browser: true,
    es6: true,
  },
  extends: ['prettier', 'plugin:vue/vue3-recommended'],
  globals: {
    Atomics: 'readonly',
    SharedArrayBuffer: 'readonly',
  },
  parserOptions: {
    ecmaVersion: 2018,
    sourceType: 'module',
  },
  plugins: ['vue'],
  rules: {
    'no-param-reassign': [
      'error',
      {
        props: true,
        ignorePropertyModificationsFor: ['state'],
      },
    ],
    'no-unexpected-multiline': 'error',
    'no-unreachable': 'error',
    'import/prefer-default-export': 'off',
    // TODO: 以下で warn にしているルールは、最終的には error にする
    camelcase: 'warn',
  },
}
