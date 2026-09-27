<template>
  <div class="question-table" :class="{ 'question-table--readonly': disabled }">
    <p v-if="!disabled" class="question-table__guidance">1件ずつ入力してください。必要な分だけ行を追加できます。</p>
    <input v-if="!disabled" type="hidden" :name="inputName" value="" />
    <p v-if="disabled && rows.length === 0" class="question-table__unanswered">未回答</p>
    <fieldset v-for="(row, rowIndex) in rows" :key="row.id" class="question-table__row">
      <legend class="question-table__row-title"><span>{{ rowIndex + 1 }}</span> 行目</legend>
      <button v-if="!disabled" class="question-table__remove" type="button" :aria-label="`${rowIndex + 1}行目を削除`" @click="removeRow(row.id)"><i class="far fa-trash-alt" aria-hidden="true"></i><span>行を削除</span></button>
      <input v-if="requiresPresence(row)" type="hidden" :name="`${inputName}[${row.id}][__present]`" value="1" />
      <div class="question-table__cells">
        <div v-for="column in columns" :key="column.id" class="question-table__cell" :class="{ 'question-table__deleted-column': isDeleted(column), 'question-table__cell--wide': ['textarea', 'upload'].includes(column.type), 'question-table__cell--invalid': errorFor(row, column) }">
          <label v-if="column.type !== 'radio' && column.type !== 'checkbox'" class="question-table__label" :for="inputId(row, column)">
            {{ column.name || "(列名未入力)" }}
            <span v-if="column.is_required && !isDeleted(column)" class="question-table__required">必須</span>
            <span v-if="isDeleted(column)" class="question-table__deleted">削除済みの列</span>
          </label>
          <p v-else class="question-table__label">
            {{ column.name || "(列名未入力)" }}
            <span v-if="column.is_required && !isDeleted(column)" class="question-table__required">必須</span>
            <span v-if="isDeleted(column)" class="question-table__deleted">削除済みの列</span>
          </p>
          <p v-if="(disabled || isDeleted(column)) && column.type !== 'upload'" class="question-table__value">{{ hasValue(row.values[column.id]) ? displayValue(row, column) : '未回答' }}</p>
          <template v-else-if="column.type === 'textarea'">
            <textarea :id="inputId(row, column)" v-model="row.values[column.id]" class="form-control" rows="3" :name="cellName(row, column)" :minlength="column.number_min" :maxlength="column.number_max" :readonly="disabled || isDeleted(column)" :aria-describedby="describedBy(row, column)" />
          </template>
          <template v-else-if="column.type === 'number'">
            <input :id="inputId(row, column)" v-model="row.values[column.id]" class="form-control" type="number" step="1" :name="cellName(row, column)" :min="column.number_min" :max="column.number_max" :readonly="disabled || isDeleted(column)" :aria-describedby="describedBy(row, column)" />
          </template>
          <template v-else-if="column.type === 'select'">
            <select :id="inputId(row, column)" v-model="row.values[column.id]" class="form-control" :name="cellName(row, column)" :disabled="disabled || isDeleted(column)" :aria-describedby="describedBy(row, column)">
              <option value=""></option>
              <option v-for="option in optionsFor(column)" :key="option" :value="option">{{ option }}</option>
            </select>
          </template>
          <template v-else-if="column.type === 'radio'">
            <label v-for="(option, optionIndex) in optionsFor(column)" :key="option" class="question-table__choice" :class="{ 'question-table__choice--selected': isSelected(row, column, option) }" :for="inputId(row, column, optionIndex)">
              <input :id="inputId(row, column, optionIndex)" v-model="row.values[column.id]" type="radio" :name="cellName(row, column)" :value="option" :disabled="disabled || isDeleted(column)" :aria-describedby="describedBy(row, column)" />
              {{ option }}
            </label>
          </template>
          <template v-else-if="column.type === 'checkbox'">
            <label v-for="(option, optionIndex) in optionsFor(column)" :key="option" class="question-table__choice" :class="{ 'question-table__choice--selected': isSelected(row, column, option) }" :for="inputId(row, column, optionIndex)">
              <input :id="inputId(row, column, optionIndex)" v-model="row.values[column.id]" type="checkbox" :name="cellName(row, column, true)" :value="option" :disabled="disabled || isDeleted(column)" :aria-describedby="describedBy(row, column)" />
              {{ option }}
            </label>
          </template>
          <template v-else-if="column.type === 'upload'">
            <div v-if="hasExistingFile(row, column) && !row.reselect[column.id] && !row.deletedFiles[column.id]" class="question-table__file">
              <input v-if="!isDeleted(column)" type="hidden" :name="cellName(row, column)" value="__KEEP__" />
              <a :href="fileUrl(row, column)" target="_blank" rel="noopener noreferrer"><i class="far fa-file-alt" aria-hidden="true"></i> アップロード済ファイルを表示</a>
              <button v-if="!disabled && !isDeleted(column)" class="question-table__file-action" type="button" @click="reselectFile(row, column)">差し替え</button>
              <button v-if="!disabled && !isDeleted(column)" class="question-table__file-action" type="button" @click="deleteFile(row, column)">削除</button>
              <p v-if="hasErrors" class="question-table__file-notice">ファイルを差し替えた場合は、検証エラー後に再選択してください。</p>
            </div>
            <div v-else-if="!disabled && !isDeleted(column)">
              <input :id="inputId(row, column)" class="form-control question-table__file-input" type="file" :name="cellName(row, column)" :accept="acceptFor(column)" :aria-describedby="describedBy(row, column)" @change="selectFile(row, column)" />
              <p v-if="hasErrors" class="question-table__file-notice">検証エラー後はファイルを再選択してください。</p>
            </div>
            <span v-else class="question-table__value">未回答</span>
          </template>
          <template v-else>
            <input :id="inputId(row, column)" v-model="row.values[column.id]" class="form-control" type="text" :name="cellName(row, column)" :minlength="column.number_min" :maxlength="column.number_max" :readonly="disabled || isDeleted(column)" :aria-describedby="describedBy(row, column)" />
          </template>
          <p v-if="errorFor(row, column)" :id="errorId(row, column)" class="question-table__error">行 {{ rowIndex + 1 }}・{{ column.name || "列" }}: {{ errorFor(row, column) }}</p>
        </div>
      </div>
    </fieldset>
    <div v-if="!disabled" class="question-table__footer">
      <button ref="addButton" class="question-table__add" type="button" :disabled="!canAddRow" @click="addRow"><i class="fas fa-plus" aria-hidden="true"></i> {{ canAddRow ? '行を追加' : '最大行数に達しました' }}</button>
      <p class="question-table__count" aria-live="polite">{{ rows.length }}行<span v-if="numberMax !== null"> / 最大{{ numberMax }}行</span><span v-if="numberMin"> · 最小{{ numberMin }}行</span> · 空の行は送信されません</p>
    </div>
  </div>
</template>

<script>
const createUuid = () => {
  if (typeof crypto !== "undefined" && typeof crypto.randomUUID === "function") return crypto.randomUUID();
  return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, (character) => {
    const random = Math.floor(Math.random() * 16);
    return (character === "x" ? random : (random & 0x3) | 0x8).toString(16);
  });
};

export default {
  props: {
    inputName: { type: String, required: true },
    value: { type: [Object, Array], default: () => ({}) },
    tableColumns: { type: Array, default: () => [] },
    tableErrors: { type: [Object, Array], default: () => ({}) },
    tableUploadUrlTemplate: { type: String, default: null },
    numberMin: { type: Number, default: null },
    numberMax: { type: Number, default: null },
    invalid: { type: String, default: null },
    disabled: { type: Boolean, default: false },
  },
  data() { return { rows: this.rowsFromValue() }; },
  computed: {
    columns() { return Array.isArray(this.tableColumns) ? this.tableColumns : []; },
    canAddRow() { return this.numberMax === null || this.rows.length < this.numberMax; },
    hasErrors() { return Boolean(this.invalid) || Object.keys(this.tableErrors).length > 0; },
  },
  watch: {
    value: {
      deep: true,
      handler() { this.rows = this.rowsFromValue(); },
    },
  },
  methods: {
    rowsFromValue() {
      const source = this.value && typeof this.value === "object" && !Array.isArray(this.value) ? this.value : {};
      const rows = Object.entries(source).map(([id, values]) => this.newRow(id, values));
      if (rows.length) return rows;
      return this.disabled ? [] : [this.newRow()];
    },
    newRow(id = createUuid(), values = {}) {
      const source = values && typeof values === "object" && !Array.isArray(values) ? values : {};
      const normalized = {};
      (Array.isArray(this.tableColumns) ? this.tableColumns : []).forEach((column) => {
        if (column.type === "checkbox") normalized[column.id] = Array.isArray(source[column.id]) ? [...source[column.id]] : [];
        else normalized[column.id] = source[column.id] ?? "";
      });
      return { id, values: normalized, reselect: {}, deletedFiles: {} };
    },
    addRow() {
      const row = this.newRow();
      this.rows.push(row);
      this.$nextTick(() => {
        const column = this.columns.find((candidate) => !this.isDeleted(candidate));
        if (column) document.getElementById(this.inputId(row, column, ['radio', 'checkbox'].includes(column.type) ? 0 : null))?.focus();
      });
    },
    removeRow(id) {
      this.rows = this.rows.filter((row) => row.id !== id);
      this.$nextTick(() => this.$refs.addButton?.focus());
    },
    isSelected(row, column, option) { return Array.isArray(row.values[column.id]) ? row.values[column.id].includes(option) : row.values[column.id] === option; },
    isDeleted(column) { return Boolean(column.deleted || column.is_deleted); },
    cellName(row, column, isArray = false) {
      if (this.isDeleted(column)) return null;
      return `${this.inputName}[${row.id}][${column.id}]${isArray ? "[]" : ""}`;
    },
    inputId(row, column, optionIndex = null) {
      const question = this.inputName.replace(/[^A-Za-z0-9_-]/g, "-");
      return `table-${question}-${row.id}-${column.id}${optionIndex === null ? "" : `-${optionIndex}`}`;
    },
    optionsFor(column) { return typeof column.options === "string" ? column.options.split(/\r\n|\n/).map((option) => option.trim()).filter(Boolean) : []; },
    acceptFor(column) { const types = typeof column.allowed_types === "string" ? column.allowed_types.split("|").map((type) => type.trim()).filter(Boolean) : []; return types.length ? `.${types.join(",.")}` : null; },
    hasExistingFile(row, column) { return Boolean(row.values[column.id]); },
    fileUrl(row, column) {
      if (!this.tableUploadUrlTemplate) return null;
      return this.tableUploadUrlTemplate.replace("__ROW__", encodeURIComponent(row.id)).replace("__COLUMN__", encodeURIComponent(column.id));
    },
    reselectFile(row, column) { this.setReselect(row.id, column.id); },
    selectFile(row, column) { this.setReselect(row.id, column.id); },
    deleteFile(row, column) {
      this.rows = this.rows.map((candidate) => candidate.id === row.id
        ? {
          ...candidate,
          values: { ...candidate.values, [column.id]: "" },
          deletedFiles: { ...candidate.deletedFiles, [column.id]: true },
        }
        : candidate);
    },
    setReselect(rowId, columnId) {
      this.rows = this.rows.map((row) => row.id === rowId
        ? { ...row, reselect: { ...row.reselect, [columnId]: true } }
        : row);
    },
    displayValue(row, column) { const value = row.values[column.id]; return Array.isArray(value) ? value.join(", ") : value; },
    requiresPresence(row) {
      return this.columns.some((column) => this.isDeleted(column) && this.hasValue(row.values[column.id]));
    },
    hasValue(value) { return Array.isArray(value) ? value.length > 0 : value !== null && value !== ""; },
    errorFor(row, column) {
      const key = `${row.id}.${column.id}`;
      return this.tableErrors[key] || Object.entries(this.tableErrors).find(([path]) => path.startsWith(`${key}.`))?.[1] || null;
    },
    errorId(row, column) { return `${this.inputId(row, column)}-error`; },
    describedBy(row, column) { return this.errorFor(row, column) ? this.errorId(row, column) : null; },
  },
};
</script>

<style lang="scss" scoped>
.question-table { width: 100%; min-width: 0; }
.question-table__guidance, .question-table__count { color: $color-muted; font-size: 0.875rem; margin: 0 0 1rem; }
.question-table__row { position: relative; min-width: 0; border: 1px solid $color-border; border-radius: $border-radius; background: $color-bg-surface; margin: 0 0 1.25rem; padding: 1.25rem; }
.question-table__row-title { float: none; display: flex; align-items: center; gap: 0.25rem; font-size: 0.875rem; font-weight: $font-bold; margin: 0; padding: 0 0.5rem; width: auto; }
.question-table__row-title span { color: $color-primary; font-size: 1.125rem; font-variant-numeric: tabular-nums; }
.question-table__remove { position: absolute; right: 0.75rem; top: -1.6rem; display: inline-flex; align-items: center; gap: 0.5rem; min-height: 44px; padding: 0 0.75rem; border: 0; background: $color-bg-surface; color: $color-muted; font: inherit; font-size: 0.8125rem; cursor: pointer; }
.question-table__remove:hover { color: $color-danger; }
.question-table__cells { display: grid; gap: 1.25rem 1.5rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
.question-table__cell { min-width: 0; }
.question-table__cell--wide { grid-column: 1 / -1; }
.question-table__label { display: flex; flex-wrap: wrap; align-items: baseline; gap: 0.5rem; font-weight: $font-bold; margin: 0 0 0.5rem; }
.question-table__required { color: $color-danger; font-size: 0.75rem; font-weight: normal; }
.question-table__deleted { color: $color-muted; font-size: 0.75rem; font-weight: normal; }
.question-table__choice { display: flex; align-items: center; gap: 0.65rem; min-height: 44px; padding: 0.5rem 0.75rem; margin-bottom: 0.4rem; border: 1px solid $color-border; border-radius: $border-radius; cursor: pointer; overflow-wrap: anywhere; }
.question-table__choice--selected { border-color: $color-primary; background: $color-primary-light; }
.question-table__choice input { flex-shrink: 0; accent-color: $color-primary; width: 1.1rem; height: 1.1rem; }
.question-table__choice:last-child { margin-bottom: 0; }
.question-table__value { margin: 0; white-space: pre-wrap; overflow-wrap: anywhere; line-height: 1.7; }
.question-table__deleted-column { border-left: 2px solid $color-border; padding-left: 1rem; }
.question-table__error { color: $color-danger; font-size: 0.875rem; margin: 0.5rem 0 0; }
.question-table__cell--invalid .form-control, .question-table__cell--invalid .question-table__choice { border-color: $color-danger; }
.question-table__file { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 1rem; border: 1px solid $color-border; border-radius: $border-radius; padding: 0.75rem 1rem; }
.question-table__file a { flex: 1 1 14rem; overflow-wrap: anywhere; }
.question-table__file-action { border: 0; background: transparent; color: $color-primary; cursor: pointer; font: inherit; font-size: 0.875rem; padding: 0.5rem; min-height: 44px; }
.question-table__file-input { padding: 0.5rem; }
.question-table__file-input::file-selector-button { padding: 0.5rem 0.75rem; margin-right: 0.75rem; border: 1px solid $color-border; border-radius: $border-radius-sm; background: $color-bg-surface; color: $color-text; font: inherit; cursor: pointer; }
.question-table__file-notice { width: 100%; color: $color-muted; font-size: 0.8125rem; margin: 0.5rem 0 0; }
.question-table__add { display: flex; justify-content: center; align-items: center; gap: 0.5rem; width: 100%; min-height: 48px; border: 1px dashed $color-primary; border-radius: $border-radius; background: $color-bg-surface; color: $color-primary; font: inherit; font-weight: $font-bold; cursor: pointer; }
.question-table__add:hover:not(:disabled) { background: $color-primary-light; }
.question-table__add:disabled { color: $color-muted; border-color: $color-border; cursor: default; }
.question-table__count { margin: 0.5rem 0 0; text-align: right; font-size: 0.8125rem; }
.question-table button:focus-visible, .question-table a:focus-visible { outline: $focus-outline; outline-offset: 2px; }
.question-table__unanswered { color: $color-muted; margin: 0; }
.question-table--readonly .question-table__label { color: $color-muted; font-size: 0.875rem; font-weight: normal; }
@media (max-width: 600px) {
  .question-table__cells { grid-template-columns: minmax(0, 1fr); }
  .question-table__row { padding: 1rem; }
  .question-table__count { text-align: left; }
}
</style>
