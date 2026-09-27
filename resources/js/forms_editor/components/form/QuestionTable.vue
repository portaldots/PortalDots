<template>
  <form-item :item_id="question_id" type_label="表形式入力">
    <template #content>
      <div class="table-question-preview">
        <p class="table-question-preview__title">{{ name }} <span class="badge text-bg-danger" v-if="question.is_required">必須</span></p>
        <p v-if="question.description" class="form-text text-muted">{{ question.description }}</p>
        <div class="table-question-preview__fields" v-if="columns.length">
          <div v-for="column in columns" :key="column.id" class="table-question-preview__field">
            <span>{{ column.name || '(列名未入力)' }}<small v-if="column.is_required"> 必須</small></span>
            <span class="table-question-preview__input">{{ typeLabel(column.type) }}</span>
          </div>
        </div>
        <p v-else class="text-muted mb-0">列を追加して、1行分の入力項目を設定してください。</p>
        <p v-if="columns.length" class="table-question-preview__note"><i class="fas fa-plus" aria-hidden="true"></i> 回答者が行を追加できます <span>{{ columns.length }}列</span></p>
      </div>
    </template>
    <template #edit-panel>
      <edit-panel :question="question" label_number_min="最小行数" label_number_max="最大行数" />
      <section class="table-question-editor" aria-label="表形式の列設定">
        <div class="table-question-editor__heading"><strong>列の設定</strong><span>{{ columns.length }}列</span></div>
        <p class="table-question-editor__help">1行分の入力項目を設定します。上から順に表示されます。</p>
        <div class="table-question-editor__column" v-for="(column, index) in columns" :key="column.id">
          <div class="table-question-editor__toolbar">
            <span class="table-question-editor__position">{{ index + 1 }}列目</span>
            <div class="table-question-editor__actions">
              <button type="button" :disabled="index === 0" :aria-label="`${index + 1}列目を上へ移動`" title="上へ移動" @click="moveColumn(index, -1)"><i class="fas fa-arrow-up" aria-hidden="true"></i></button>
              <button type="button" :disabled="index === columns.length - 1" :aria-label="`${index + 1}列目を下へ移動`" title="下へ移動" @click="moveColumn(index, 1)"><i class="fas fa-arrow-down" aria-hidden="true"></i></button>
              <button class="table-question-editor__delete" type="button" :aria-label="`${index + 1}列目を削除`" title="列を削除" @click="removeColumn(index)"><i class="far fa-trash-alt" aria-hidden="true"></i></button>
            </div>
          </div>
          <div class="table-question-editor__basics">
            <label class="form-label">列名<input class="form-control" type="text" :value="column.name" @input="updateColumn(index, 'name', $event.target.value)" @blur="save" /></label>
            <label class="form-label">入力形式
              <select class="form-select" :value="column.type" @change="updateColumn(index, 'type', $event.target.value); save()">
                <option v-for="type in columnTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
              </select>
            </label>
          </div>
          <label class="table-question-editor__required"><input type="checkbox" :checked="column.is_required" @change="updateColumn(index, 'is_required', $event.target.checked); save()" />入力を必須にする</label>
          <label class="form-label" v-if="hasOptions(column)">選択肢<textarea class="form-control" rows="3" :value="column.options" placeholder="1行に1つ選択肢を入力" @input="updateColumn(index, 'options', $event.target.value)" @blur="save" /><small class="table-question-editor__help">1行に1つずつ入力してください。</small></label>
          <label class="form-label" v-if="column.type === 'upload'">許可する拡張子<input class="form-control" type="text" :value="column.allowed_types" placeholder="pdf|png|jpg" @input="updateColumn(index, 'allowed_types', $event.target.value)" @blur="save" /><small class="table-question-editor__help">複数指定する場合は「|」で区切ります。</small></label>
          <details v-if="hasLimits(column)" class="table-question-editor__limits">
            <summary>入力範囲の設定 <span>{{ column.number_min != null || column.number_max != null ? '設定あり' : '制限なし' }}</span></summary>
            <div class="table-question-editor__basics">
              <label v-if="column.type !== 'upload'" class="form-label">{{ minimumLabel(column) }}<input class="form-control" :min="column.type === 'number' ? null : 0" type="number" :value="column.number_min" @input="updateColumn(index, 'number_min', nullableNumber($event.target.value))" @blur="save" /></label>
              <label class="form-label">{{ maximumLabel(column) }}<input class="form-control" :min="column.type === 'number' ? null : 0" type="number" :value="column.number_max" @input="updateColumn(index, 'number_max', nullableNumber($event.target.value))" @blur="save" /></label>
            </div>
          </details>
        </div>
        <button class="table-question-editor__add" type="button" @click="addColumn"><i class="fas fa-plus" aria-hidden="true"></i> 列を追加</button>
      </section>
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
    addColumn() { this.setColumns([...this.columns, { id: createUuid(), name: `列${this.columns.length + 1}`, type: "text", is_required: false, options: "", number_min: null, number_max: null, allowed_types: "" }]); this.save(); },
    removeColumn(index) { this.setColumns(this.columns.filter((_, currentIndex) => currentIndex !== index)); this.save(); },
    moveColumn(index, offset) { const columns = [...this.columns]; const target = index + offset; [columns[index], columns[target]] = [columns[target], columns[index]]; this.setColumns(columns); this.save(); },
    updateColumn(index, key, value) { this.setColumns(this.columns.map((column, currentIndex) => currentIndex === index ? { ...column, [key]: value } : column)); },
  },
};
</script>

<style lang="scss" scoped>
.table-question-preview__title { margin: 0 0 0.5rem; font-weight: $font-bold; }
.table-question-preview__fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; margin-top: 1rem; }
.table-question-preview__field { display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.875rem; overflow-wrap: anywhere; }
.table-question-preview__field small { color: $color-danger; }
.table-question-preview__input { border: 1px solid $color-border; border-radius: $border-radius; padding: 0.6rem 0.75rem; color: $color-muted; background: $color-bg-light; }
.table-question-preview__note { display: flex; align-items: center; gap: 0.5rem; color: $color-muted; font-size: 0.8125rem; margin: 1rem 0 0; }
.table-question-preview__note span { margin-left: auto; }
.table-question-editor { border-top: 1px solid $color-border; margin-top: 1.5rem; padding-top: 1.5rem; }
.table-question-editor__heading { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem; }
.table-question-editor__heading span, .table-question-editor__help { color: $color-muted; font-size: 0.8125rem; }
.table-question-editor__help { display: block; margin: 0.4rem 0 1rem; }
.table-question-editor__column { border: 1px solid $color-border; border-radius: $border-radius; padding: 1rem; margin: 0 0 1rem; background: $color-bg-surface; }
.table-question-editor__toolbar { display: flex; align-items: center; justify-content: space-between; margin: -0.5rem -0.5rem 0.5rem 0; }
.table-question-editor__position { color: $color-muted; font-size: 0.8125rem; font-weight: $font-bold; }
.table-question-editor__actions { display: flex; gap: 0.125rem; }
.table-question-editor__actions button { border: 0; background: transparent; color: $color-muted; width: 36px; height: 36px; border-radius: $border-radius-sm; }
.table-question-editor__actions button:disabled { opacity: 0.3; }
.table-question-editor__actions button:hover:not(:disabled) { color: $color-primary; background: $color-primary-light; }
.table-question-editor__actions .table-question-editor__delete:hover { color: $color-danger; background: $color-danger-light; }
.table-question-editor__basics { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 0.75rem; }
.table-question-editor .form-label { display: block; font-size: 0.875rem; margin-bottom: 0.75rem; }
.table-question-editor .form-control, .table-question-editor .form-select { margin-top: 0.4rem; }
.table-question-editor__required { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; margin: 0.25rem 0 1rem; }
.table-question-editor__required input { accent-color: $color-primary; }
.table-question-editor__limits { border-top: 1px solid $color-border; padding-top: 0.75rem; }
.table-question-editor__limits summary { cursor: pointer; font-size: 0.8125rem; }
.table-question-editor__limits summary span { float: right; color: $color-muted; }
.table-question-editor__limits[open] summary { margin-bottom: 1rem; }
.table-question-editor__add { display: flex; gap: 0.5rem; align-items: center; justify-content: center; width: 100%; min-height: 44px; border: 1px dashed $color-primary; border-radius: $border-radius; color: $color-primary; background: $color-bg-surface; font: inherit; font-size: 0.875rem; }
.table-question-editor__add:hover { background: $color-primary-light; }
.table-question-editor button:focus-visible, .table-question-editor summary:focus-visible { outline: $focus-outline; outline-offset: 2px; }
</style>
