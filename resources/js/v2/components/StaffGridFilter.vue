<template>
  <form class="staff_grid_sort" @submit.prevent="onClickApply">
    <template v-if="queries.length > 0">
      <div class="staff_grid_sort-list">
        <div
          class="staff_grid_sort-list__item"
          v-for="query in queries"
          :key="query.id"
        >
          <div class="staff_grid_sort-list__name">
            <span>{{ query.item.translation }}</span>
            <button
              class="staff_grid_sort-list__remove"
              type="button"
              @click="() => removeQuery(query.id)"
            >
              <i class="far fa-times-circle"></i>
            </button>
          </div>
          <div class="staff_grid_sort-list__inputs">
            <template v-if="!['bool', 'isNull'].includes(query.item.type)">
              <input
                type="text"
                class="form-control"
                v-model="query.value"
                v-if="query.item.type === 'string'"
              />
              <input
                type="number"
                class="form-control"
                v-model="query.value"
                v-else-if="query.item.type === 'number'"
              />
              <input
                type="datetime-local"
                class="form-control"
                v-model="query.value"
                v-else-if="query.item.type === 'datetime'"
                required
              />
            </template>
            <select
              type="text"
              class="form-control"
              v-model="query.operator"
              v-if="query.item.type === 'string'"
            >
              <option value="=">と一致</option>
              <option value="!=">と不一致</option>
              <option value="like">を含む</option>
              <option value="not like">を含まない</option>
            </select>
            <select
              type="text"
              class="form-control"
              v-model="query.operator"
              v-else-if="query.item.type === 'number'"
            >
              <option value="=">と一致</option>
              <option value="!=">と不一致</option>
              <option value="<">より小さい</option>
              <option value="<=">以下</option>
              <option value=">">より大きい</option>
              <option value=">=">以上</option>
            </select>
            <select
              type="text"
              class="form-control"
              v-model="query.operator"
              v-else-if="query.item.type === 'datetime'"
            >
              <option value="=">と一致</option>
              <option value="!=">と不一致</option>
              <option value="<">以前</option>
              <option value="<=">以前(一致含む)</option>
              <option value=">">以降</option>
              <option value=">=">以降(一致含む)</option>
            </select>
            <!-- ↓operatorではなくvalueを選択させている点に注意！ -->
            <select
              type="text"
              class="form-control"
              v-model="query.value"
              v-if="query.item.type === 'bool'"
            >
              <option value="1">はい</option>
              <option value="0">いいえ</option>
            </select>
            <select
              type="text"
              class="form-control"
              v-model="query.value"
              v-if="query.item.type === 'isNull'"
            >
              <option value="1">空</option>
              <option value="0">空でない</option>
            </select>
            <select
              type="text"
              class="form-control is-size-full"
              v-model="query.value"
              v-if="query.item.type === 'belongsToMany'"
            >
              <option
                v-for="choice in filterableKeys[query.item.keyName].choices"
                :key="choice.id"
                :value="choice.id"
              >
                {{ choice[filterableKeys[query.item.keyName].choices_name] }}
              </option>
            </select>
            <select
              type="text"
              class="form-control is-size-full"
              v-model="query.value"
              v-if="query.item.type === 'enum'"
            >
              <option
                v-for="(displayValue, value) in filterableKeys[
                  query.item.keyName
                ].choices"
                :key="value"
                :value="value"
              >
                {{ displayValue }}
              </option>
            </select>
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
        :dropdownItems="itemsForFilterQuery"
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
        type="submit"
        class="btn is-primary is-block"
        :disabled="loading || !isDirty"
      >
        <i class="fas fa-spinner fa-pulse" v-if="loading"></i>
        <strong v-else>適用</strong>
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
  </form>
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
    this.queries = this.defaultQueries.map(query => ({
      id: query.id,
      item: this.itemsForFilterQuery
        .flatMap(item => (item.sublist ? item.sublist : item))
        .find(q => q.keyName === query.keyName),
      operator: query.operator,
      value: query.value
    }))
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

      const defaultValues = {
        value: ''
      }

      switch (item.type) {
        case 'string':
          defaultValues.operator = 'like'
          break
        case 'bool':
        case 'isNull':
          defaultValues.value = '1'
          break
        default:
          defaultValues.operator = '='
          break
      }

      this.queries = [...this.queries, { id, item, ...defaultValues }]
    },
    removeQuery(queryId) {
      this.isDirty = true
      this.queries = this.queries.filter(query => query.id !== queryId)
    },
    onClickApply() {
      this.isDirty = false
      this.$emit(
        'clickApply',
        this.queries.map(query => ({
          keyName: query.item.keyName,
          operator: query.operator,
          value: query.value
        })),
        this.mode
      )
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
  },
  computed: {
    itemsForFilterQuery() {
      return Object.keys(this.filterableKeys).map(key => {
        if (this.filterableKeys[key].type !== 'belongsTo') {
          return {
            keyName: key,
            type: this.filterableKeys[key].type,
            translation: this.keyTranslations[key],
            menuLabel: this.keyTranslations[key]
          }
        }

        if (typeof this.filterableKeys[key].keys !== 'object') {
          return {}
        }

        return {
          keyName: key,
          label: this.keyTranslations[key],
          sublist: Object.keys(this.filterableKeys[key].keys).map(
            insideKey => ({
              keyName: `${key}.${insideKey}`,
              type: this.filterableKeys[key].keys[insideKey].type,
              translation: `${this.keyTranslations[key]} › ${
                this.keyTranslations[`${key}.${insideKey}`]
              }`,
              menuLabel: this.keyTranslations[`${key}.${insideKey}`]
            })
          )
        }
      })
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
      grid-template-columns: 3fr 2fr;
      .form-control.is-size-full {
        grid-column-end: span 2;
      }
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
