<template>
  <div class="edit-panel">
    <ListViewFormGroup v-if="show_required_switch">
      <div class="form-checkbox">
        <label class="form-checkbox__label">
          <input
            type="checkbox"
            v-model="is_required"
            :disabled="is_deleting"
          />
          <strong>この設問への回答は必須</strong>
        </label>
      </div>
    </ListViewFormGroup>
    <ListViewFormGroup v-if="label_name">
      <template #label>{{ label_name }}</template>
      <input
        type="text"
        class="form-control"
        v-model="name"
        @blur="save"
        :disabled="is_deleting"
      />
    </ListViewFormGroup>
    <ListViewFormGroup v-if="label_description">
      <template #label>{{ label_description }}</template>
      <textarea
        class="form-control"
        v-model="description"
        @blur="save"
        :disabled="is_deleting"
        rows="2"
      />
    </ListViewFormGroup>
    <ListViewFormGroup v-if="show_options">
      <template #label>選択肢</template>
      <textarea
        class="form-control"
        v-model="options"
        @blur="onBlur"
        :disabled="is_deleting"
        rows="4"
        placeholder="1行に1つ選択肢を入力"
      />
      <small class="edit-panel__help-text">改行区切りで選択肢を入力。</small>
    </ListViewFormGroup>
    <div class="edit-panel__grid">
      <ListViewFormGroup v-if="label_number_min">
        <template #label>{{ label_number_min }}</template>
        <input
          type="number"
          min="0"
          class="form-control"
          v-model="number_min"
          @blur="save"
          :disabled="is_deleting"
        />
      </ListViewFormGroup>
      <ListViewFormGroup v-if="label_number_max">
        <template #label>{{ label_number_max }}</template>
        <input
          type="number"
          min="0"
          class="form-control"
          v-model="number_max"
          @blur="save"
          :disabled="is_deleting"
        />
        <small class="edit-panel__help-text" v-if="help_number_max">
          {{ help_number_max }}
        </small>
      </ListViewFormGroup>
    </div>
    <ListViewFormGroup v-if="show_allowed_types">
      <template #label>許可される拡張子(<code>|</code>区切りで指定)</template>
      <input
        type="text"
        class="form-control"
        v-model="allowed_types"
        @blur="save"
        :disabled="is_deleting"
      />
      <small class="edit-panel__help-text">
        画像アップロードを許可したい場合 :
        <code>png|jpg|jpeg|gif</code> と入力。
      </small>
    </ListViewFormGroup>
    <div class="row mb-2">
      <div class="offset-sm-2 col-sm-10 text-right">
        <button
          class="btn btn-link text-danger p-0"
          @click="deleteQuestion"
          :disabled="is_deleting"
        >
          この項目を削除
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import ListViewFormGroup from '../../../v2/components/ListViewFormGroup.vue'
import {
  UPDATE_QUESTION,
  SAVE_QUESTION,
  DELETE_QUESTION
} from '../../store/editor'

export default {
  components: { ListViewFormGroup },
  props: {
    question: {
      required: true
    },
    show_required_switch: {
      required: false,
      default: true
    },
    label_name: {
      required: false,
      default: '設問名'
    },
    label_description: {
      required: false,
      default: '説明'
    },
    label_number_min: {
      required: false,
      default: '最小文字数'
    },
    label_number_max: {
      required: false,
      default: '最大文字数'
    },
    help_number_max: {
      required: false,
      default: ''
    },
    show_allowed_types: {
      required: false,
      default: false
    },
    show_options: {
      required: false,
      default: false
    }
  },
  data() {
    return {
      is_deleting: false
    }
  },
  methods: {
    save() {
      this.$store.dispatch(`editor/${SAVE_QUESTION}`, this.question.id)
    },
    deleteQuestion() {
      if (
        window.confirm(
          '設問を削除すると、この設問に対する回答も全て削除されます。本当に削除しますか？'
        )
      ) {
        this.is_deleting = true
        this.$store.dispatch(`editor/${DELETE_QUESTION}`, this.question.id)
      }
    },
    deleteDuplication() {
      if (this.options) {
        const options = new Set(
          this.options
            .trim()
            .split(/\r\n|\n/)
            .map((option) => option.trim())
        )
        this.options = Array.from(options).join('\n')
      }
    },
    onBlur() {
      this.deleteDuplication()
      this.save()
    }
  },
  // TODO: 変更点がない場合、saveメソッドが実行されないようにする
  computed: {
    name: {
      get() {
        return this.question.name
      },
      set(new_value) {
        this.$store.commit(`editor/${UPDATE_QUESTION}`, {
          id: this.question.id,
          key: 'name',
          value: new_value
        })
      }
    },
    description: {
      get() {
        return this.question.description
      },
      set(new_value) {
        this.$store.commit(`editor/${UPDATE_QUESTION}`, {
          id: this.question.id,
          key: 'description',
          value: new_value
        })
      }
    },
    options: {
      get() {
        return this.question.options
      },
      set(new_value) {
        this.$store.commit(`editor/${UPDATE_QUESTION}`, {
          id: this.question.id,
          key: 'options',
          value: new_value
        })
      }
    },
    is_required: {
      get() {
        return this.question.is_required
      },
      set(new_value) {
        this.$store.commit(`editor/${UPDATE_QUESTION}`, {
          id: this.question.id,
          key: 'is_required',
          value: new_value
        })
        this.save()
      }
    },
    number_min: {
      get() {
        return this.question.number_min
      },
      set(new_value) {
        this.$store.commit(`editor/${UPDATE_QUESTION}`, {
          id: this.question.id,
          key: 'number_min',
          value: new_value
        })
      }
    },
    number_max: {
      get() {
        return this.question.number_max
      },
      set(new_value) {
        this.$store.commit(`editor/${UPDATE_QUESTION}`, {
          id: this.question.id,
          key: 'number_max',
          value: new_value
        })
      }
    },
    allowed_types: {
      get() {
        return this.question.allowed_types
      },
      set(new_value) {
        this.$store.commit(`editor/${UPDATE_QUESTION}`, {
          id: this.question.id,
          key: 'allowed_types',
          value: new_value
        })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.edit-panel {
  &__grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
  }
  &__help-text {
    color: $color-muted;
    display: block;
    margin-top: $spacing-sm;
  }
}
</style>
