<template>
  <form-item :item_id="question_id" type_label="単一選択(ドロップダウン)">
    <template v-slot:content>
      <div class="form-group mb-0">
        <p class="mb-2">
          {{ name }}
          <span class="badge badge-danger" v-if="is_required">必須</span>
        </p>
        <p class="form-text text-muted mb-2">
          {{ description }}
        </p>
        <template v-if="options">
          <select class="custom-select" tabindex="-1">
            <option>単一選択(ドロップダウン)</option>
          </select>
          <ul class="list-group">
            <li
              class="list-group-item py-1"
              v-for="option in options"
              :key="option"
            >
              {{ option }}
            </li>
          </ul>
        </template>
        <template v-else>
          <div class="empty-option">
            <p class="empty-option-text">
              <i class="fa fa-exclamation-triangle mr-1"></i>
              <b>選択肢がありません。</b>
            </p>
            <p class="empty-option-text">選択肢を1つ以上入力してください。</p>
          </div>
        </template>
      </div>
    </template>
    <template v-slot:edit-panel>
      <edit-panel
        :question="question"
        :label_number_min="false"
        :label_number_max="false"
        :show_options="true"
      />
    </template>
  </form-item>
</template>

<script>
import FormItem from "./FormItem.vue";
import EditPanel from "./EditPanel.vue";
import { GET_QUESTION_BY_ID } from "../../store/editor";

export default {
  props: {
    question_id: {
      required: true,
      type: Number,
    },
  },
  components: {
    FormItem,
    EditPanel,
  },
  computed: {
    question() {
      return this.$store.getters[`editor/${GET_QUESTION_BY_ID}`](
        this.question_id
      );
    },
    name() {
      return this.question.name || "(無題の単一選択(ドロップダウン))";
    },
    description() {
      return this.question.description;
    },
    options() {
      if (this.question.options) {
        const options = new Set(this.question.options.trim().split(/\r\n|\n/));
        return Array.from(options);
      }
      return null;
    },
    is_required() {
      return this.question.is_required;
    },
  },
};
</script>

<style lang="scss" scoped>
.custom-select {
  appearance: none;
  background: $color-bg-form-control;
  border-bottom: 0;
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
  border-color: $color-border-form-control;
}

.list-group-item {
  background: $color-bg-form-control;
  border-color: $color-border-form-control;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
  margin: 0;
}
</style>
