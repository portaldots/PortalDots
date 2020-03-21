import Axios from 'axios'

const baseURL = document.querySelector('#forms-editor-config')
  ? JSON.parse(
      document.querySelector('#forms-editor-config').dataset.apiBaseUrl
    )
  : null
const token = document.head.querySelector('meta[name="csrf-token"]').content

const axios = Axios.create({
  baseURL,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': token
  }
})

let isProcessing = false

axios.interceptors.request.use(config => {
  if (config.method === 'get') return config

  return new Promise(resolve => {
    const interval = setInterval(() => {
      if (!isProcessing) {
        isProcessing = true
        clearInterval(interval)
        resolve(config)
      }
    }, 10)
  })
})

axios.interceptors.response.use(
  // リクエスト成功時
  response => {
    isProcessing = false
    return response
  },
  // リクエスト失敗時
  error => {
    // vm.$store.commit(`status/${DEQUEUED}`)
    // vm.$store.commit(`status/${SET_ERROR}`)
    throw error
  }
)

export default axios
