import Turbolinks from 'turbolinks'

import Vue from 'vue'
import GlobalEvents from 'vue-global-events'
import TurbolinksAdapter from './vue-turbolinks'

import AppContainer from './components/AppContainer.vue'
import ListView from './components/ListView.vue'
import ListViewItem from './components/ListViewItem.vue'
import ListViewActionBtn from './components/ListViewActionBtn.vue'
import ListViewEmpty from './components/ListViewEmpty.vue'
import ListViewFormGroup from './components/ListViewFormGroup.vue'
import TopAlert from './components/TopAlert.vue'

// iOS で CSS の hover を有効にするハック
document.body.addEventListener('touchstart', () => {}, { passive: true })

Vue.use(TurbolinksAdapter)

Turbolinks.start()

document.addEventListener('turbolinks:load', () => {
  new Vue({
    components: {
      GlobalEvents,
      AppContainer,
      ListView,
      ListViewItem,
      ListViewActionBtn,
      ListViewEmpty,
      ListViewFormGroup,
      TopAlert
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
    },
    mounted() {
      const loading = document.querySelector('#loading')
      loading.classList.add('is-done')
    }
  }).$mount('#v2-app')
})
