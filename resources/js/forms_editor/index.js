import Vue from 'vue'
import Vuex from 'vuex'
import EditorApp from './EditorApp.vue'
import store from './store'

export function mountFormsEditor() {
  Vue.use(Vuex)

  Vue.component('editor-app', EditorApp)

  new Vue({
    store,
    template: '<editor-app />'
  }).$mount('#forms-editor-container')
}
