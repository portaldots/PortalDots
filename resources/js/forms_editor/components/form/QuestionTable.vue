<template>
  <form-item :item_id="question_id" type_label="表形式入力">
    <template #content>
      <div>
        <p class="mb-1">
          {{ name }}
          <span class="badge text-bg-danger" v-if="question.is_required">必須</span>
        </p>
        <p class="form-text text-muted mb-2">{{ question.description }}</p>
        <div class="table-responsive" v-if="columns.length">
          <table class="table table-sm mb-0">
            <thead><tr><th v-for="column in columns" :key="column.id">{{ column.name || "(列名未入力)" }}</th></tr></thead>
            <tbody><tr><td v-for="column in columns" :key="column.id"><span class="text-muted">{{ typeLabel(column.type) }}</span></td></tr></tbody>
          </table>
        </div>
        <p class="text-muted mb-0" v-else>列を追加してください。</p>
      </div>
    </template>
    <template #edit-panel>
      <edit-panel :question="question" label_number_min="最小行数" label_number_max="最大行数" />
      <div class="table-question-editor">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <strong>列</strong>
          <button class="btn btn-outline-primary btn-sm" type="button" @click="addColumn">列を追加</button>
        </div>
        <div class="card mb-2" v-for="(column, index) in columns" :key="column.id">
          <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <strong>{{ index + 1 }}列目</strong>
              <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary" type="button" :disabled="index === 0" @click="moveColumn(index, -1)">←</button>
                <button class="btn btn-outline-secondary" type="button" :disabled="index === columns.length - 1" @click="moveColumn(index, 1)">→</button>
                <button class="btn btn-outline-danger" type="button" @click="removeColumn(index)">削除</button>
              </div>
            </div>
            <label class="form-label">列名<input class="form-control" type="text" :value="column.name" @input="updateColumn(index, 'name', $event.target.value)" @blur="save" /></label>
            <label class="form-label">形式
              <select class="form-select" :value="column.type" @change="updateColumn(index, 'type', $event.target.value); save()">
                <option v-for="type in columnTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
              </select>
            </label>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" :id="`column-${column.id}-required`" :checked="column.is_required" @change="updateColumn(index, 'is_required', $event.target.checked); save()" />
              <label class="form-check-label" :for="`column-${column.id}-required`">この列は必須</label>
            </div>
            <label class="form-label" v-if="hasOptions(column)">選択肢<textarea class="form-control" rows="3" :value="column.options" placeholder="1行に1つ選択肢を入力" @input="updateColumn(index, 'options', $event.target.value)" @blur="save" /></label>
            <div class="row" v-if="hasLimits(column)">
              <label v-if="column.type !== 'upload'" class="col-sm-6 form-label">{{ minimumLabel(column) }}<input class="form-control" :min="column.type === 'number' ? null : 0" type="number" :value="column.number_min" @input="updateColumn(index, 'number_min', nullableNumber($event.target.value))" @blur="save" /></label>
              <label :class="column.type === 'upload' ? 'col-sm-12 form-label' : 'col-sm-6 form-label'">{{ maximumLabel(column) }}<input class="form-control" :min="column.type === 'number' ? null : 0" type="number" :value="column.number_max" @input="updateColumn(index, 'number_max', nullableNumber($event.target.value))" @blur="save" /></label>
            </div>
            <label class="form-label" v-if="column.type === 'upload'">許可される拡張子 (<code>|</code>区切り)<input class="form-control" type="text" :value="column.allowed_types" @input="updateColumn(index, 'allowed_types', $event.target.value)" @blur="save" /></label>
          </div>
        </div>
      </div>
    </template>
  </form-item>
</template>

<script>
import FormItem from "./FormItem.vue";
import EditPanel from "./EditPanel.vue";
import { GET_QUESTION_BY_ID, SAVE_QUESTION, UPDATE_QUESTION } from "../../store/editor";

const createUuid = () => {
  if (typeof crypto !== "undefined" && typeof crypto.randomUUID === "function") return crypto.randomUUID();
  return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, (character) => {
    const random = Math.floor(Math.random() * 16);
    return (character === "x" ? random : (random & 0x3) | 0x8).toString(16);
  });
};

export default {
  components: { FormItem, EditPanel },
  props: { question_id: { type: Number, required: true } },
  computed: {
    question() { return this.$store.getters[`editor/${GET_QUESTION_BY_ID}`](this.question_id); },
    columns() { return Array.isArray(this.question.table) ? this.question.table : []; },
    name() { return this.question.name || "(無題の表形式入力)"; },
    columnTypes() {
      return [
        { value: "text", label: "一行入力" }, { value: "textarea", label: "複数行入力" },
        { value: "number", label: "整数入力" }, { value: "radio", label: "単一選択(ラジオボタン)" },
        { value: "checkbox", label: "複数選択(チェックボックス)" }, { value: "select", label: "単一選択(ドロップダウン)" },
        { value: "upload", label: "ファイルアップロード" },
      ];
    },
  },
  methods: {
    typeLabel(type) { const item = this.columnTypes.find((candidate) => candidate.value === type); return item ? item.label : type; },
    hasOptions(column) { return ["radio", "checkbox", "select"].includes(column.type); },
    hasLimits(column) { return ["text", "textarea", "number", "checkbox", "upload"].includes(column.type); },
    minimumLabel(column) { return column.type === "number" ? "最低数" : column.type === "checkbox" ? "最少選択数" : "最小文字数"; },
    maximumLabel(column) { return column.type === "number" ? "最大数" : column.type === "checkbox" ? "最多選択数" : column.type === "upload" ? "最大サイズ(KB)" : "最大文字数"; },
    nullableNumber(value) { return value === "" ? null : Number(value); },
    setColumns(columns) { this.$store.commit(`editor/${UPDATE_QUESTION}`, { id: this.question_id, key: "table", value: columns }); },
    save() { this.$store.dispatch(`editor/${SAVE_QUESTION}`, this.question_id); },
    addColumn() { this.setColumns([...this.columns, { id: createUuid(), name: "", type: "text", is_required: false, options: "", number_min: null, number_max: null, allowed_types: "" }]); this.save(); },
    removeColumn(index) { this.setColumns(this.columns.filter((_, currentIndex) => currentIndex !== index)); this.save(); },
    moveColumn(index, offset) { const columns = [...this.columns]; const target = index + offset; [columns[index], columns[target]] = [columns[target], columns[index]]; this.setColumns(columns); this.save(); },
    updateColumn(index, key, value) { this.setColumns(this.columns.map((column, currentIndex) => currentIndex === index ? { ...column, [key]: value } : column)); },
  },
};
</script>

<style lang="scss" scoped>
.table-question-editor { border-top: 1px solid $color-border; margin-top: $spacing; padding-top: $spacing; }
.table-question-editor .form-label { display: block; }
</style>
