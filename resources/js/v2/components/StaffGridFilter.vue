<template>
  <div class="staff_grid_sort">
    <template v-if="queries.length > 0">
      <div class="staff_grid_sort-list">
        <div
          class="staff_grid_sort-list__item"
          v-for="(query, arrayIndex) in queries"
          :key="query.id"
        >
          <div class="staff_grid_sort-list__name">
            <span>{{ keyTranslations[query.keyName] }}</span>
            <button
              class="staff_grid_sort-list__remove"
              type="button"
              @click="() => removeQuery(query.id)"
            >
              <i class="far fa-times-circle"></i>
            </button>
          </div>
          <div class="staff_grid_sort-list__inputs">
            <select
              type="text"
              class="form-control"
              v-model="queries[arrayIndex].operator"
              v-if="filterableKeys[query.keyName] === 'string'"
            >
              <option value="=">＝ (一致)</option>
              <option value="!=">≠ (不一致)</option>
              <option value="like">含む</option>
              <option value="not like">含まない</option>
            </select>
            <select
              type="text"
              class="form-control"
              v-model="queries[arrayIndex].operator"
              v-else-if="
                ['number', 'datetime'].includes(filterableKeys[query.keyName])
              "
            >
              <option value="=">＝ (一致)</option>
              <option value="!=">≠ (不一致)</option>
              <option value="<">＜ (より小さい)</option>
              <option value="<=">≦ (以下)</option>
              <option value=">">＞ (より大きい)</option>
              <option value=">=">≧ (以上)</option>
            </select>
            <!-- ↓operatorではなくvalueを選択させている点に注意！ -->
            <select
              type="text"
              class="form-control"
              v-model="queries[arrayIndex].value"
              v-else-if="
                ['bool', 'isNull'].includes(filterableKeys[query.keyName])
              "
            >
              <option value="1">はい</option>
              <option value="0">いいえ</option>
            </select>
            <template
              v-if="!['bool', 'isNull'].includes(filterableKeys[query.keyName])"
            >
              <input
                type="text"
                class="form-control"
                v-model="queries[arrayIndex].value"
                v-if="filterableKeys[query.keyName] === 'string'"
              />
              <input
                type="number"
                class="form-control"
                v-model="queries[arrayIndex].value"
                v-else-if="filterableKeys[query.keyName] === 'number'"
              />
              <input
                type="datetime-local"
                class="form-control"
                v-model="queries[arrayIndex].value"
                v-else-if="filterableKeys[query.keyName] === 'datetime'"
              />
            </template>
          </div>
        </div>
      </div>
    </template>
    <div class="staff_grid_sort-empty" v-else>
      <i class="fas fa-filter fa-fw staff_grid_sort-empty__icon"></i>
      <div>
        <strong>絞り込み未設定</strong><br />
        「絞り込み」機能を利用することで、特定の条件を満たすデータを検索できます。<br />
        「<i class="fas fa-plus-circle fa-fw"></i
        >条件を追加」ボタンから条件を追加しましょう。
      </div>
    </div>
    <div class="staff_grid_sort-dropdown">
      <StaffGridFilterAddDropdown
        :keys="
          Object.keys(filterableKeys).filter(keyName =>
            ['string', 'number', 'datetime', 'bool'].includes(
              filterableKeys[keyName]
            )
          )
        "
        :keyTranslations="keyTranslations"
        @clickItem="addQuery"
      />
    </div>
    <div class="form-radio staff_grid_sort-mode" v-if="queries.length > 0">
      <label class="form-radio__label">
        <input
          class="form-radio__input"
          type="radio"
          name="status"
          value="and"
          v-model="mode"
        />
        すべての条件を満たす
      </label>
      <label class="form-radio__label">
        <input
          class="form-radio__input"
          type="radio"
          name="status"
          value="or"
          v-model="mode"
        />
        いずれかの条件を満たす
      </label>
    </div>
    <div class="staff_grid_sort-actions">
      <button
        type="button"
        @click="onClickApply"
        class="btn is-primary is-block"
        :disabled="loading || !isDirty"
      >
        <strong>適用</strong>
      </button>
      <button
        type="button"
        @click="onClickClearAll"
        class="btn is-secondary is-block"
        :disabled="loading"
      >
        絞り込みを解除
      </button>
    </div>
  </div>
</template>

<script>
import StaffGridFilterAddDropdown from './StaffGridFilterAddDropdown.vue'

export default {
  components: {
    StaffGridFilterAddDropdown
  },
  props: {
    filterableKeys: {
      type: Object,
      default: () => {}
    },
    keyTranslations: {
      type: Object,
      required: true
    },
    defaultQueries: {
      type: Array,
      default: () => []
    },
    defaultMode: {
      type: String,
      default: 'and'
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      queries: [],
      mode: 'and',
      // isDirty: 適用ボタンによってフィルタ設定が反映されていない場合trueになる
      isDirty: false
    }
  },
  mounted() {
    this.queries = this.defaultQueries
    this.mode = this.defaultMode
    this.$nextTick(() => {
      this.isDirty = false
    })
  },
  methods: {
    addQuery(item) {
      const id =
        this.queries[this.queries.length - 1] &&
        Number.isInteger(this.queries[this.queries.length - 1].id)
          ? this.queries[this.queries.length - 1].id + 1
          : 0
      this.queries = [
        ...this.queries,
        { id, keyName: item, operator: '=', value: '' }
      ]
    },
    removeQuery(queryId) {
      this.isDirty = true
      this.queries = this.queries.filter(query => query.id !== queryId)
    },
    onClickApply() {
      this.isDirty = false
      this.$emit('clickApply', this.queries, this.mode)
    },
    onClickClearAll() {
      this.queries = []
      this.onClickApply()
      this.$nextTick(() => {
        this.isDirty = false
      })
    }
  },
  watch: {
    queries: {
      handler() {
        this.isDirty = true
      },
      deep: true
    },
    mode() {
      this.isDirty = true
    }
  }
}
</script>

<style lang="scss" scoped>
.staff_grid_sort {
  padding: 0 $spacing;
  &-list {
    &__item {
      border-bottom: 1px solid $color-border;
      padding: $spacing 0;
    }
    &__name {
      display: flex;
      font-weight: 600;
      justify-content: space-between;
      margin-bottom: $spacing-sm;
    }
    &__remove {
      appearance: none;
      background: transparent;
      border: 0;
      color: $color-primary;
      cursor: pointer;
    }
    &__inputs {
      column-gap: $spacing-sm;
      display: grid;
      grid-template-columns: 2fr 3fr;
    }
  }
  &-mode {
    background: $color-bg-light;
    border-radius: $border-radius;
    margin: $spacing 0 0;
    padding: $spacing;
  }
  &-empty {
    padding: $spacing-lg 0;
    text-align: center;
    &__icon {
      color: $color-muted;
      font-size: 2rem;
      margin: 0 0 $spacing;
    }
  }
  &-dropdown {
    margin: $spacing-md #{-$spacing} 0;
  }
  &-actions {
    display: grid;
    margin: $spacing 0 0;
    row-gap: $spacing-md;
  }
}
</style>
