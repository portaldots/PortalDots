import Vue from 'vue'
import Vuex from 'vuex'

import editor from './editor'
import status from './status'

Vue.use(Vuex)

const store = new Vuex.Store({
  modules: {
    editor,
    status
  }
})

export default store
