export const SAVE_STATUS_INIT = 'init'
export const SAVE_STATUS_DIRTY = 'dirty'
export const SAVE_STATUS_SAVING = 'saving'
export const SAVE_STATUS_SAVED = 'saved'

export const SET_DIRTY = 'SET_DIRTY'
export const SET_SAVING = 'SET_SAVING'
export const SET_SAVED = 'SET_SAVED'
export const ENQUEUED = 'ENQUEUED'
export const DEQUEUED = 'DEQUEUED'
export const SET_ERROR = 'SET_ERROR'

export default {
  namespaced: true,
  state: {
    save_status: SAVE_STATUS_INIT,
    request_queued_count: 0,
    is_error: false
  },
  mutations: {
    [SET_DIRTY](state) {
      state.save_status = SAVE_STATUS_DIRTY
    },
    [SET_SAVING](state) {
      state.save_status = SAVE_STATUS_SAVING
    },
    [SET_SAVED](state) {
      state.save_status = SAVE_STATUS_SAVED
    },
    [ENQUEUED](state) {
      state.request_queued_count += 1
    },
    [DEQUEUED](state) {
      state.request_queued_count = Math.max(0, state.request_queued_count - 1)
    },
    [SET_ERROR](state) {
      state.is_error = true
    }
  }
}
