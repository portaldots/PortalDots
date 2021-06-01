<template>
  <form-item :item_id="question_id" type_label="ファイルアップロード">
    <template v-slot:content>
      <QuestionItem
        :required="is_required"
        type="upload"
        :questionId="question_id"
        :name="name"
        :description="description"
        :numberMax="number_max"
        :allowedTypes="allowed_types"
        disabled
      />
      <ListViewCard v-if="!question.allowed_types">
        <AppInfoBox danger>
          <b>
            ファイルアップロードを受け付けるには「許可される拡張子」を1つ以上指定してください
          </b>
        </AppInfoBox>
      </ListViewCard>
    </template>
    <template v-slot:edit-panel>
      <edit-panel
        :question="question"
        :label_number_min="false"
        label_number_max="最大サイズ(KB)"
        help_number_max="サーバーの upload_max_filesize 設定の値を超える設定は無効になります。規定値は 50MB であり、public フォルダ内の .htaccess ファイルで設定されています。upload_max_filesize 設定の詳細は PHP ドキュメントをご確認ください"
        :show_allowed_types="true"
      />
    </template>
  </form-item>
</template>

<script>
import FormItem from './FormItem.vue'
import EditPanel from './EditPanel.vue'
import { GET_QUESTION_BY_ID } from '../../store/editor'
import QuestionItem from '../../../v2/components/Forms/QuestionItem.vue'
import ListViewCard from '../../../v2/components/ListViewCard.vue'
import AppInfoBox from '../../../v2/components/AppInfoBox.vue'

export default {
  props: {
    question_id: {
      required: true,
      type: Number
    }
  },
  components: {
    FormItem,
    EditPanel,
    QuestionItem,
    ListViewCard,
    AppInfoBox
  },
  computed: {
    question() {
      return this.$store.getters[`editor/${GET_QUESTION_BY_ID}`](
        this.question_id
      )
    },
    name() {
      return this.question.name || '(無題の設問)'
    },
    number_max() {
      return this.question.number_max
        ? parseInt(this.question.number_max, 10)
        : undefined
    },
    description() {
      return this.question.description
    },
    is_required() {
      return this.question.is_required
    },
    allowed_types() {
      return this.question.allowed_types
        ? this.question.allowed_types.split('|')
        : undefined
    }
  }
}
</script>
