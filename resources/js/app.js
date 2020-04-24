import 'bootstrap'

import { mountV2App } from './v2/app'
import { mountFormsEditor } from './forms_editor'
import { mountUsersChecker } from './users_checker'

// TODO: ユーザー登録チェッカーとフォームエディターを v2 化することにより、
//      以下の条件分岐をやめる
if (document.querySelector('#vue-user-checker')) {
  mountUsersChecker()
} else if (document.querySelector('#forms-editor-container')) {
  mountFormsEditor()
} else if (document.querySelector('#v2-app')) {
  mountV2App()
}
