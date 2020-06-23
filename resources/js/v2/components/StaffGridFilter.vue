<template>
  <div class="staff_grid_sort">
    <template v-if="queries.length > 0">
      <div class="staff_grid_sort-list">
        <div
          class="staff_grid_sort-list__item"
          v-for="query in queries"
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
            <select type="text" class="form-control" :value="query.operator">
              <option value="=">＝ (等しい)</option>
              <option value="!=">≠ (等しくない)</option>
              <option value="like">含む</option>
              <option value="not like">含まない</option>
            </select>
            <input type="text" class="form-control" :value="query.value" />
          </div>
        </div>
      </div>
    </template>
    <div class="staff_grid_sort-empty" v-else>
      <i class="fas fa-filter fa-fw staff_grid_sort-empty__icon"></i>
      <div>
        「絞り込み」機能を利用することで、特定の条件を満たすデータを検索できます。<br />
        「条件を追加」ボタンから条件を追加しましょう。
      </div>
    </div>
    <div class="staff_grid_sort-dropdown">
      <StaffGridFilterAddDropdown
        :keys="Object.keys(filterableKeys)"
        :keyTranslations="keyTranslations"
        @clickItem="addQuery"
      />
    </div>
    <div class="staff_grid_sort-actions" v-if="queries.length > 0">
      <button
        type="button"
        @click="onClickApply"
        class="btn is-primary is-block"
      >
        <strong>適用</strong>
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
    }
  },
  data() {
    return {
      queries: [],
      mode: 'and'
    }
  },
  mounted() {
    this.queries = this.defaultQueries
    this.mode = this.defaultMode
  },
  methods: {
    addQuery(item) {
      const id = this.queries[this.queries.length - 1]
        ? this.queries[this.queries.length - 1].id + 1
        : 0
      this.queries = [
        ...this.queries,
        { id, keyName: item, operator: '=', value: '' }
      ]
    },
    removeQuery(queryId) {
      this.queries = this.queries.filter(query => query.id !== queryId)
    },
    onClickApply() {
      this.$emit('clickApply', this.queries, this.mode)
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
    margin: $spacing 0 0;
  }
}
</style>
