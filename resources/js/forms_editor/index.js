import { createApp, configureCompat } from 'vue'
import EditorApp from './EditorApp.vue'
import store from './store'

export function mountFormsEditor() {
  configureCompat({
    RENDER_FUNCTION: false,
    COMPONENT_V_MODEL: false,
    TRANSITION_GROUP_ROOT: false,
  })
  const app = createApp(EditorApp)
  app.use(store)
  app.mount('#forms-editor-container')
}
