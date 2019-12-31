import Vue from 'vue'
import GlobalEvents from 'vue-global-events'

export default new Vue({
  el: '#v2-app',
  components: {
    GlobalEvents
  },
  data() {
    return {
      isDrawerOpen: false
    }
  },
  methods: {
    toggleDrawer() {
      this.isDrawerOpen = !this.isDrawerOpen
    },
    closeDrawer() {
      this.isDrawerOpen = false
    }
  },
  watch: {
    isDrawerOpen(newVal) {
      // アクセシビリティのため、適切な位置にフォーカスする
      if (newVal) {
        this.$refs.drawer.focus()
      } else {
        this.$refs.toggle.focus()
      }
    }
  }
})
