<template>
  <ListView>
    <template v-slot:title>外観</template>

    <ListViewCard>
      <AppInfoBox primary>
        外観設定はお使いのブラウザーに保存されます。Cookieを削除するとこの設定はリセットされます。
      </AppInfoBox>
    </ListViewCard>
    <ListViewFormGroup>
      <div class="form-radio">
        <label class="form-radio__label">
          <input
            class="form-radio__input"
            type="radio"
            name="theme"
            value="system"
            v-model="selectedTheme"
          />
          <strong>自動</strong><br />
          <span class="text-muted">
            お使いの端末の設定での外観モード設定に準じます
          </span>
        </label>
        <label class="form-radio__label">
          <input
            class="form-radio__input"
            type="radio"
            name="theme"
            id="appearanceRadios2"
            value="light"
            v-model="selectedTheme"
          />
          <strong>ライトテーマ</strong><br />
          <span class="text-muted">明るい外観になります</span>
        </label>
        <label class="form-radio__label">
          <input
            class="form-radio__input"
            type="radio"
            name="theme"
            id="appearanceRadios3"
            value="dark"
            v-model="selectedTheme"
          />
          <strong>ダークテーマ</strong><br />
          <span class="text-muted">暗い外観になります</span>
        </label>
      </div>
    </ListViewFormGroup>
  </ListView>
</template>

<script>
import ListView from './ListView.vue'
import ListViewCard from './ListViewCard.vue'
import AppInfoBox from './AppInfoBox.vue'
import ListViewFormGroup from './ListViewFormGroup.vue'

export default {
  components: {
    ListView,
    ListViewCard,
    AppInfoBox,
    ListViewFormGroup
  },
  props: {
    defaultTheme: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      selectedTheme: ''
    }
  },
  mounted() {
    // Cookieにセットされているテーマをセット
    this.selectedTheme = this.defaultTheme || 'system'
  },
  methods: {
    changeThemeClassName(newTheme) {
      const htmlElem = document.querySelector('html')
      htmlElem.classList.forEach((className) => {
        if (className.startsWith('theme-')) {
          document.documentElement.classList.remove(className)
        }
      })
      htmlElem.classList.add(`theme-${newTheme}`)
    }
  },
  watch: {
    selectedTheme(newTheme) {
      this.changeThemeClassName(newTheme)
    }
  },
  destroyed() {
    // HTMLタグのクラスをリセットする
    this.changeThemeClassName(this.defaultTheme)
  }
}
</script>
