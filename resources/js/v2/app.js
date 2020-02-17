import Turbolinks from 'turbolinks'

import Vue from 'vue'
import GlobalEvents from 'vue-global-events'
import TurbolinksAdapter from './vue-turbolinks'

import AppHeader from './components/AppHeader.vue'
import AppContainer from './components/AppContainer.vue'
import ListView from './components/ListView.vue'
import ListViewItem from './components/ListViewItem.vue'
import ListViewActionBtn from './components/ListViewActionBtn.vue'
import ListViewEmpty from './components/ListViewEmpty.vue'
import ListViewFormGroup from './components/ListViewFormGroup.vue'
import TopAlert from './components/TopAlert.vue'
import FormWithConfirm from './components/FormWithConfirm.vue'

// Form Questions
import QuestionItem from './components/Forms/QuestionItem.vue'
import QuestionHeading from './components/Forms/QuestionHeading.vue'

// iOS で CSS の hover を有効にするハック
document.body.addEventListener('touchstart', () => {}, { passive: true })

Vue.use(TurbolinksAdapter)

Turbolinks.start()

document.addEventListener('turbolinks:load', () => {
  new Vue({
    components: {
      GlobalEvents,
      AppHeader,
      AppContainer,
      ListView,
      ListViewItem,
      ListViewActionBtn,
      ListViewEmpty,
      ListViewFormGroup,
      TopAlert,
      FormWithConfirm,
      QuestionItem,
      QuestionHeading
    },
    data() {
      return {
        isDrawerOpen: false
      }
    },
    mounted() {
      const loading = document.querySelector('#loading')
      loading.classList.add('is-done')

      // フォーム送信時に送信ボタンを disabled にする
      // this.$nextTick(() => {
      //   this.registerSubmitHandler()
      // })
    },
    methods: {
      toggleDrawer() {
        this.isDrawerOpen = !this.isDrawerOpen
      },
      closeDrawer() {
        this.isDrawerOpen = false
      }
      // registerSubmitHandler() {
      //   const forms = document.querySelectorAll('form')
      //   const submits = document.querySelectorAll(
      //     'button[type="submit"], input[type="submit"]'
      //   )
      //   const handler = () => {
      //     /* eslint-disable no-restricted-syntax */
      //     for (const submit of submits) {
      //       submit.disabled = true
      //     }
      //     /* eslint-enable */
      //   }
      //   /* eslint-disable no-restricted-syntax */
      //   for (const form of forms) {
      //     form.addEventListener('submit', handler)
      //   }
      //   /* eslint- enable */
      // }
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
  }).$mount('#v2-app')
})
