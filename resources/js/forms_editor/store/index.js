import { createStore } from 'vuex'

import editor from './editor'
import status from './status'

const store = createStore({
  modules: {
    editor,
    status,
  },
})

export default store
