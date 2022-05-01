<template>
  <div class="edit-panel-table">
    <div>
      {{ table }}
    </div>
    <div>
      <button class="m-2 btn btn-primary" @click="addQuestion">
        +設問を追加
      </button>
    </div>
  </div>
</template>

<script>
import { v4 as uuid } from 'uuid'

export default {
  model: {
    prop: 'table'
  },
  props: {
    table: {
      required: true,
      default: () => ({ questions: [] }),
      type: Object
    }
  },
  methods: {
    addQuestion() {
      this.$set(this.table, 'questions', [
        ...this.table.questions,
        {
          id: uuid(),
          type: 'text'
        }
      ])
      this.save()
    },
    save() {
      this.$emit('save', this.table)
    }
  }
}
</script>

<style lang="scss" scoped></style>
