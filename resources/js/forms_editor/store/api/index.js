import Repository from './repository'

export default {
  get_form() {
    return Repository.get('get_form')
  },
  get_questions() {
    return Repository.get('get_questions')
  },
  add_question(type) {
    return Repository.post('add_question', { type })
  },
  update_questions_order(questions) {
    return Repository.post('update_questions_order', { questions })
  },
  update_question(question) {
    return Repository.post('update_question', { question })
  },
  delete_question(question) {
    return Repository.post('delete_question', { question })
  },
  update_form(form) {
    return Repository.post('update_form', { form })
  }
}
