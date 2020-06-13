import Turbolinks from 'turbolinks'

import Vue from 'vue'
import GlobalEvents from 'vue-global-events'
import TurbolinksAdapter from './vue-turbolinks'

import AppHeader from './components/AppHeader.vue'
import AppNavBar from './components/AppNavBar.vue'
import AppNavBarBack from './components/AppNavBarBack.vue'
import AppNavBarToggle from './components/AppNavBarToggle.vue'
import CircleSelectorDropdown from './components/CircleSelectorDropdown.vue'
import AppContainer from './components/AppContainer.vue'
import AppBadge from './components/AppBadge.vue'
import AppDropdown from './components/AppDropdown.vue'
import AppDropdownItem from './components/AppDropdownItem.vue'
import ListView from './components/ListView.vue'
import ListViewCard from './components/ListViewCard.vue'
import ListViewItem from './components/ListViewItem.vue'
import ListViewActionBtn from './components/ListViewActionBtn.vue'
import ListViewEmpty from './components/ListViewEmpty.vue'
import ListViewFormGroup from './components/ListViewFormGroup.vue'
import ListViewPagination from './components/ListViewPagination.vue'
import TopAlert from './components/TopAlert.vue'
import FormWithConfirm from './components/FormWithConfirm.vue'
import StepsList from './components/StepsList.vue'
import StepsListItem from './components/StepsListItem.vue'
import TagsInput from './components/TagsInput.vue'
import MarkdownEditor from './components/MarkdownEditor.vue'

// Form Questions
import QuestionItem from './components/Forms/QuestionItem.vue'
import QuestionHeading from './components/Forms/QuestionHeading.vue'

export function mountV2App() {
  // iOS で CSS の hover を有効にするハック
  document.body.addEventListener('touchstart', () => {}, { passive: true })

  Vue.use(TurbolinksAdapter)

  Turbolinks.start()

  // ページ移動時、ボタンやフォームコントロールを無効化する
  window.addEventListener('beforeunload', () => {
    const inputs = document.querySelectorAll('input, select, textarea, button')
    /* eslint-disable no-restricted-syntax */
    for (const input of inputs) {
      input.disabled = 'disabled'
    }
    /* eslint-enable */
  })

  document.addEventListener('turbolinks:load', () => {
    new Vue({
      components: {
        GlobalEvents,
        AppHeader,
        AppNavBar,
        AppNavBarBack,
        AppNavBarToggle,
        CircleSelectorDropdown,
        AppContainer,
        AppBadge,
        AppDropdown,
        AppDropdownItem,
        ListView,
        ListViewCard,
        ListViewItem,
        ListViewActionBtn,
        ListViewEmpty,
        ListViewFormGroup,
        ListViewPagination,
        TopAlert,
        FormWithConfirm,
        QuestionItem,
        QuestionHeading,
        StepsList,
        StepsListItem,
        TagsInput,
        MarkdownEditor
      },
      data() {
        return {
          isDrawerOpen: false
        }
      },
      mounted() {
        const loading = document.querySelector('#loading')
        loading.classList.add('is-done')
      },
      // destroyed() {
      //   const loading = document.querySelector('#loading')
      //   loading.classList.remove('is-done')
      // },
      methods: {
        toggleDrawer() {
          this.isDrawerOpen = !this.isDrawerOpen
        },
        closeDrawer() {
          this.isDrawerOpen = false
        },
        share(shareData) {
          if (navigator.share) {
            navigator.share(shareData)
          } else {
            window.alert('お使いのブラウザでは共有機能に対応していません')
          }
        }
      },
      watch: {
        isDrawerOpen(newVal) {
          // アクセシビリティのため、適切な位置にフォーカスする
          if (newVal) {
            this.$refs.drawer.focus()
          } else {
            this.$refs.toggle.$el.focus()
          }
        }
      }
    }).$mount('#v2-app')
  })
}
