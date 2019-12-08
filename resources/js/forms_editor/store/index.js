import Vue from 'vue';
import Vuex from 'vuex';
Vue.use(Vuex);

import editor from './editor';
import status from './status';

const store = new Vuex.Store({
    modules: {
        editor,
        status,
    }
});

export default store;
