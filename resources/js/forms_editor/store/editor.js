import API from './api';

export const GET_QUESTION_BY_ID = 'GET_QUESTION_BY_ID';
export const CLOSE = 'CLOSE';
export const ITEM_HEADER = 'header';
export const TOGGLE_OPEN_STATE = 'TOGGLE_OPEN_STATE';
export const SET_LOADED = 'SET_LOADED';
export const SET_FORM = 'SET_FORM';
export const SET_QUESTIONS = 'SET_QUESTIONS';
export const UPDATE_FORM = 'UPDATE_FORM';
export const UPDATE_QUESTION = 'UPDATE_QUESTION';
export const DELETE_QUESTION = 'DELETE_QUESTION';
export const SET_FORM_PUBLIC = 'SET_FORM_PUBLIC';
export const SET_FORM_PRIVATE = 'SET_FORM_PRIVATE';
export const DRAG_START = 'DRAG_START';
export const DRAG_END = 'DRAG_END';
export const FETCH = 'FETCH';
export const UPDATE_QUESTIONS_ORDER = 'UPDATE_QUESTIONS_ORDER';
export const SAVE_QUESTION = 'SAVE_QUESTION';
export const SAVE_FORM = 'SAVE_FORM';
export const ADD_QUESTION = 'ADD_QUESTION';
export const SET_OPTIONS = 'SET_OPTIONS';

export default {
    namespaced: true,
    state: {
        // loaded も status に移動させる
        loaded: false,
        form: {},
        questions: [],
        // 現在、編集パネルが開いているFormItem
        // ITEM_HEADER or 数値(question id)
        open_item_id: null,
        // question をドラッグ中か
        drag: false,
    },
    getters: {
        [GET_QUESTION_BY_ID]: (state) => (id) => {
            return state.questions.find(question => question.id === id);
        }
    },
    mutations: {
        [TOGGLE_OPEN_STATE] (state, payload) {
            if (state.open_item_id === payload.item_id) {
                state.open_item_id = null;
            } else {
                state.open_item_id = payload.item_id;
            }
        },
        [CLOSE] (state) {
            state.open_item_id = null;
        },
        [SET_LOADED] (state) {
            state.loaded = true;
        },
        [SET_FORM] (state, form) {
            state.form = form;
        },
        [SET_QUESTIONS] (state, questions) {
            state.questions = questions;
        },
        [UPDATE_FORM] (state, payload) {
            state.form = { ...state.form, [payload.key]: payload.value };
        },
        [UPDATE_QUESTION] (state, payload) {
            const question_index = state.questions.findIndex(question => question.id === payload.id);
            const question = state.questions[question_index];
            question[payload.key] = payload.value;
            state.questions.splice(question_index, 1, question);
        },
        [DELETE_QUESTION] (state, question_id) {
            const question_index = state.questions.findIndex(question => question.id === question_id);
            state.questions.splice(question_index, 1);
        },
        [SET_FORM_PUBLIC] (state) {
            state.form = { ...state.form, is_public: true };
        },
        [SET_FORM_PRIVATE] (state) {
            state.form = { ...state.form, is_public: false };
        },
        [DRAG_START] (state) {
            state.drag = true;
        },
        [DRAG_END] (state) {
            state.drag = false;
        },
        [SET_OPTIONS] (state, payload) {
            const question_index = state.questions.findIndex(question => question.id === payload.question_id);
            const question = state.questions[question_index];
            question['options'] = payload.options;
            state.questions.splice(question_index, 1, question);
        },
    },
    actions: {
        async [FETCH] ({ commit }) {
            commit(SET_FORM, (await API.get_form()).data);
            commit(SET_QUESTIONS, (await API.get_questions()).data);
            commit(SET_LOADED);
        },
        async [UPDATE_QUESTIONS_ORDER] ({ commit, state }, questions) {
            // 現状のquestions配列の状態をバックアップ
            const questions_backup = state.questions;
            let count = 0;
            questions = questions.map(question => {
                question.priority = ++count;
                return question;
            });
            commit(SET_QUESTIONS, questions);
            try {
                await API.update_questions_order(questions.map(question => {
                    return {
                        id: question.id,
                        priority: question.priority,
                    };
                }));
            } catch (e) {
                // バックアップをリストア
                commit(SET_QUESTIONS, questions_backup);
            }
        },
        [DRAG_START] ({ commit }) {
            commit(CLOSE);
            commit(DRAG_START);
        },
        [DRAG_END] ({ commit }) {
            commit(DRAG_END);
        },
        async [ADD_QUESTION] ({ commit, state }, type) {
            const question = (await API.add_question(type)).data;
            commit(SET_QUESTIONS, [...state.questions, question]);
            commit(TOGGLE_OPEN_STATE, { item_id: question['id'] });
        },
        async [DELETE_QUESTION]({ commit }, question_id) {
            await API.delete_question(question_id);
            commit(DELETE_QUESTION, question_id);
        },
        async [SAVE_QUESTION] ({ getters }, question_id) {
            await API.update_question(getters[GET_QUESTION_BY_ID](question_id));
        },
        async [SAVE_FORM] ({ state}) {
            await API.update_form(state.form);
        },
    }
};
