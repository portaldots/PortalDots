import { createApp } from 'vue'
import EditorApp from './EditorApp.vue'
import store from './store'

export function mountFormsEditor() {
  const app = createApp(EditorApp)
  app.use(store)
  app.mount('#forms-editor-container')
}
