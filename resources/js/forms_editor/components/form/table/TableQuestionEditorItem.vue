<template>
  <details class="table-question-editor my-1">
    <summary>
      {{ label_name }}
      <span class="badge badge-danger ml-1" v-if="is_required">必須</span>
      <div class="drag-grip"><i class="fas fa-grip-vertical"></i></div>
    </summary>
    <div class="table-question-editor__body">
      <div class="form-group">
        <div class="form-label">回答必須か</div>
        <label class="custom-control custom-switch">
          <input
            class="custom-control-input"
            type="checkbox"
            v-model="is_required"
          />
          <span class="custom-control-label">この設問への回答は必須</span>
        </label>
      </div>
      <label class="form-group w-100">
        <div class="form-label">名称</div>
        <input class="form-control" type="text" v-model="name" @blur="save" />
      </label>
      <label class="form-group w-100">
        <div class="form-label">設問タイプ</div>
        <select class="form-control" v-model="type">
          <option
            v-for="type in types"
            :key="type.value"
            :value="type.value"
            @blur="save"
          >
            {{ type.name }}
          </option>
        </select>
      </label>
      <label class="form-gorup w-100" v-if="question.type === 'dropdown'">
        <div class="form-label">選択肢</div>
        <textarea
          class="form-control"
          rows="5"
          v-model="options"
          @blur="save"
        ></textarea>
      </label>
      <div class="text-right">
        <button class="btn btn-link text-danger" @click="remove">
          「{{ label_name }}」を削除
        </button>
      </div>
    </div>
  </details>
</template>

<script>
export default {
  props: {
    question: {
      required: true,
      type: Object
    }
  },
  data() {
    return {
      types: [
        {
          value: 'text',
          name: '一行入力'
        },
        {
          value: 'dropdown',
          name: 'ドロップダウン'
        }
      ]
    }
  },
  methods: {
    save() {
      this.$emit('saveQuestion')
    },
    remove() {
      if (window.confirm(`「${this.label_name}」を削除しますか？`)) {
        this.$emit('removeQuestion', this.question.id)
      }
    },
    normalizeOptions(value) {
      if (
        (typeof value === 'string' || typeof value === 'object') &&
        value.length > 0
      ) {
        return Array.from(
          new Set(
            value
              .trim()
              .split(/\r\n|\n/)
              .map((option) => option.trim())
          )
        ).join('\n')
      }
      return ''
    }
  },
  computed: {
    label_name: {
      get() {
        return this.question.name || '(無題の設問)'
      }
    },
    name: {
      get() {
        return this.question.name
      },
      set(value) {
        this.$set(this.question, 'name', value)
      }
    },
    is_required: {
      get() {
        return this.question.is_required
      },
      set(value) {
        this.$set(this.question, 'is_required', value)
        this.save()
      }
    },
    type: {
      get() {
        return this.question.type
      },
      set(value) {
        this.$set(this.question, 'type', value)
      }
    },
    options: {
      get() {
        return this.question.options || ''
      },
      set(value) {
        this.$set(this.question, 'options', this.normalizeOptions(value))
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.drag-grip {
  float: right;
  margin-right: $spacing-xs;
  &:hover {
    cursor: move;
  }
}

.table-question-editor {
  &__body {
    background-color: $color-bg-light;
    padding: $spacing-sm $spacing;
  }
}
</style>
