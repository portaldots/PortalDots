module.exports = {
  extends: [
    'stylelint-config-standard',
    'stylelint-config-prettier',
    'stylelint-config-standard-vue',
    'stylelint-config-standard-scss',
    'stylelint-config-standard-vue/scss',
  ],
  plugins: ['stylelint-order', 'stylelint-scss'],
  ignoreFiles: ['resources/sass/v2/_normalize.scss'],
  rules: {
    'at-rule-no-unknown': null,
    'scss/at-rule-no-unknown': true,
    'no-empty-source': null,
    'rule-empty-line-before': [
      'always',
      {
        except: 'inside-block',
      },
    ],
    'selector-class-pattern': null,
  },
}
