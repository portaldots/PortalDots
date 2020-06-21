<template>
  <div class="staff_grid">
    <template v-if="paginator">
      <div class="staff_grid-toolbar">
        <slot name="toolbar" />
      </div>
      <div class="staff_grid__table_wrap">
        <table class="staff_grid-table">
          <thead class="staff_grid-table__thead">
            <tr class="staff_grid-table__tr">
              <th class="staff_grid-table__th"></th>
              <th
                class="staff_grid-table__th"
                v-for="keyName in keys"
                :key="keyName"
              >
                <slot name="th" :keyName="keyName" />
              </th>
            </tr>
          </thead>
          <tbody class="staff_grid-table__tbody">
            <tr
              class="staff_grid-table__tr is-in-tbody"
              v-for="row in paginator.data"
              :key="row.id"
            >
              <td class="staff_grid-table__td">
                <slot name="activities" :row="row" />
              </td>
              <td
                class="staff_grid-table__td"
                v-for="keyName in keys"
                :key="`${row.id}-${keyName}`"
              >
                <slot name="td" :row="row" :keyName="keyName" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="staff_grid-footer">
        <button
          class="btn is-secondary is-no-border"
          title="最初のページ"
          :disabled="loading || page === 1"
          @click="page = 1"
        >
          <i class="fas fa-angle-double-left"></i>
        </button>
        <button
          class="btn is-secondary is-no-border"
          title="前のページ"
          :disabled="loading || page === 1"
          @click="page = page - 1"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
        <button
          class="btn is-secondary is-no-border"
          title="次のページ"
          :disabled="loading || page === paginator.last_page"
          @click="page = page + 1"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
        <button
          class="btn is-secondary is-no-border"
          title="最後のページ"
          :disabled="loading || page === paginator.last_page"
          @click="page = paginator.last_page"
        >
          <i class="fas fa-angle-double-right"></i>
        </button>
        <div class="staff_grid-footer__label">
          表示件数 :
          <AppDropdown
            :items="[10, 25, 50, 100, 250, 500]"
            name="grid-per-page"
          >
            <template #button="{ toggle, props }">
              <button
                class="btn is-secondary is-no-border"
                @click="toggle"
                v-bind="props"
              >
                {{ perPage }}&nbsp;
                <i class="fas fa-caret-down"></i>
              </button>
            </template>
            <template #item="{ item }">
              <AppDropdownItem
                class="staff_grid-footer__selector-item"
                component-is="button"
                @click="perPage = item"
              >
                {{ item }}
                <i
                  class="fas fa-check staff_grid-footer__selector-item__icon"
                  v-if="perPage === item"
                ></i>
              </AppDropdownItem>
            </template>
          </AppDropdown>
        </div>
        <div class="staff_grid-footer__label">
          {{ paginator.from }}〜{{ paginator.to }}件目 • 全{{
            paginator.total
          }}件 (ページ{{ paginator.current_page }} / {{ paginator.last_page }})
        </div>
        <div class="staff_grid-footer__label text-primary" v-if="loading">
          <i class="fas fa-spinner fa-pulse"></i>
        </div>
      </div>
    </template>
    <div class="staff_grid-loading" v-else>
      <i class="fas fa-spinner fa-pulse fa-2x text-primary"></i>
    </div>
  </div>
</template>

<script>
import Axios from 'axios'
import AppDropdown from './AppDropdown.vue'
import AppDropdownItem from './AppDropdownItem.vue'

const axios = Axios.create({
  headers: {
    'X-Requested-With': 'XMLHttpRequest'
  }
})

export default {
  components: {
    AppDropdown,
    AppDropdownItem
  },
  props: {
    apiUrl: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      loading: true,
      keys: [],
      paginator: null,
      page: 1,
      perPage: 25
    }
  },
  async mounted() {
    await this.fetch()
  },
  methods: {
    async fetch() {
      this.loading = true
      const res = await axios.get(
        `${this.apiUrl}?is_ajax=true&page=${this.page}&per_page=${this.perPage}`
      )
      this.keys = res.data.keys
      this.paginator = res.data.paginator
      this.loading = false
    }
  },
  watch: {
    // TODO: GETパラメータで page と per_page を指定できるようにする
    async page() {
      await this.fetch()
    },
    async perPage(newPerPage) {
      if (Math.ceil(this.paginator.total / newPerPage) < this.page) {
        this.page = Math.ceil(this.paginator.total / newPerPage)
      } else {
        await this.fetch()
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.staff_grid {
  background: $color-bg-white;
  border-radius: $border-radius;
  box-shadow: 0 1px 2px $color-border;
  margin: $spacing;
  &-toolbar {
    padding: $spacing-sm $spacing-md;
  }
  &-loading {
    align-items: center;
    display: flex;
    flex-direction: column;
    height: 300px;
    justify-content: center;
    width: 100%;
  }
  &__table_wrap {
    background: $color-bg-white;
    border-bottom: 1px solid $color-border;
    border-top: 1px solid $color-border;
    overflow: auto;
    width: 100%;
  }
  &-table {
    border: 0;
    border-collapse: collapse;
    border-spacing: 0;
    width: 100%;
    &__thead {
      border-bottom: 1px solid $color-border;
    }
    &__th {
      font-size: 0.9rem;
      padding: $spacing-sm $spacing-md;
      text-align: left;
      white-space: nowrap;
    }
    &__tr {
      border-bottom: 1px solid $color-border;
      &:last-child {
        border: 0;
      }
      &.is-in-tbody:hover {
        background: $color-bg-light;
      }
    }
    &__td {
      font-size: 0.9rem;
      padding: $spacing-sm $spacing-md;
      white-space: nowrap;
    }
  }
  &-footer {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    padding: $spacing-sm $spacing-md;
    &__label {
      display: inline-block;
      padding: 0 $spacing-md;
      position: relative;
      &::before {
        border-left: 1px solid $color-border;
        content: '';
        display: block;
        height: 1.5rem;
        left: 0;
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
      }
    }
    &__selector-item {
      align-items: center;
      display: flex;
      min-width: 7.5rem;
      &__icon {
        color: $color-primary;
        margin-left: auto;
      }
    }
  }
}
</style>
