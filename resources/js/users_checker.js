import Axios from 'axios'
import Vue from 'vue'

export function mountUsersChecker() {
  new Vue({
    data() {
      return {
        student_id: null,
        list: [],
        timeout_id: null,
        is_loading: false,
        is_init: true
      }
    },
    watch: {
      async student_id(newValue) {
        clearTimeout(this.timeout_id)
        this.timeout_id = setTimeout(async () => {
          await this.getList(newValue)
        }, 500)
      }
    },
    methods: {
      async getList(student_id) {
        this.is_loading = true
        this.is_init = false
        const res = await Axios.get('./check/list', {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          params: {
            student_id
          }
        })
        this.list = res.data
        this.is_loading = false
      },
      async onPressEnter() {
        clearTimeout(this.timeout_id)
        await this.getList(this.student_id)
      }
    }
  }).$mount('#vue-user-checker')
}
