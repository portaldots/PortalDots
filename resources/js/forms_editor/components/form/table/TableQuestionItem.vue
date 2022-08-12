<template>
  <label class="form-group w-100">
    <div class="form-label">
      {{ label_name }}
      <span class="badge badge-danger" v-if="is_required">必須</span>
    </div>
    <input
      v-if="type === 'text'"
      class="form-control"
      type="text"
      tabindex="-1"
      placeholder="(一行入力)"
    />

    <select class="custom-select" tabindex="-1" v-if="type === 'dropdown'">
      <option>ドロップダウン</option>
    </select>
    <ul class="list-group">
      <li class="list-group-item py-1" v-for="option in options" :key="option">
        {{ option }}
      </li>
    </ul>
  </label>
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
      q: this.question
    }
  },
  computed: {
    label_name() {
      return this.q.name || '(無題の設問)'
    },
    is_required() {
      return this.q.is_required
    },
    type() {
      return this.q.type
    },
    options() {
      if (
        this.type === 'dropdown' &&
        typeof this.q.options === 'string' &&
        this.q.options.length > 0
      ) {
        return this.q.options.trim().split(/\r\n|\n/)
      }
      return null
    }
  }
}
</script>

<style lang="scss" scoped>
.custom-select {
  appearance: none;
  background: $color-form-control;
  border-bottom: 0;
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
  border-color: $color-border;
}

.list-group-item {
  background: $color-form-control;
  border-color: $color-border;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
  margin: 0;
}
</style>
