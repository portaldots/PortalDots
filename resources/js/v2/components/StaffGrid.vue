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
          @click="onClickFirst"
        >
          <i class="fas fa-angle-double-left"></i>
        </button>
        <button
          class="btn is-secondary is-no-border"
          title="前のページ"
          :disabled="loading || page === 1"
          @click="onClickPrev"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
        <button
          class="btn is-secondary is-no-border"
          title="次のページ"
          :disabled="loading || page === paginator.last_page"
          @click="onClickNext"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
        <button
          class="btn is-secondary is-no-border"
          title="最後のページ"
          :disabled="loading || page === paginator.last_page"
          @click="onClickLast"
        >
          <i class="fas fa-angle-double-right"></i>
        </button>
        <button
          class="btn is-secondary is-no-border"
          title="再読み込み"
          :disabled="loading"
          @click="fetch"
        >
          <i class="fas fa-sync"></i>
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
                @click="() => onChangePerPage(item)"
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
    this.setFromUrlParams()
    window.addEventListener('popstate', this.setFromUrlParams, false)
    await this.fetch()
  },
  destroyed() {
    window.removeEventListener('popstate', this.setFromUrlParams, false)
  },
  methods: {
    onClickFirst() {
      this.page = 1
      this.setUrlParams()
    },
    onClickPrev() {
      this.page -= 1
      this.setUrlParams()
    },
    onClickNext() {
      this.page += 1
      this.setUrlParams()
    },
    onClickLast() {
      this.page = this.paginator.last_page
      this.setUrlParams()
    },
    onChangePerPage(perPage) {
      this.perPage = perPage
      if (
        this.paginator &&
        Math.ceil(this.paginator.total / perPage) < this.page
      ) {
        this.page = Math.ceil(this.paginator.total / perPage)
      }
      this.setUrlParams()
    },
    setUrlParams() {
      window.history.pushState(
        '',
        '',
        `?page=${this.page}&per_page=${this.perPage}`
      )
    },
    setFromUrlParams() {
      const matchesOfPage = window.location.search.match(/page=([0-9]+)/)
      const matchesOfPerPage = window.location.search.match(/per_page=([0-9]+)/)

      if (matchesOfPage) {
        this.page = parseInt(matchesOfPage[1], 10)
      }
      if (matchesOfPerPage) {
        this.perPage = parseInt(matchesOfPerPage[1], 10)
      }
    },
    async fetch() {
      // FIXME: ハッシュパラメータの page と perPage が同時に変化すると fetch が 2 回連続で呼ばれてしまう問題
      this.loading = true
      const res = await axios.get(
        `${this.apiUrl}?page=${this.page}&per_page=${this.perPage}`
      )
      this.keys = res.data.keys
      this.paginator = res.data.paginator
      this.loading = false
    }
  },
  watch: {
    async page() {
      await this.fetch()
    },
    async perPage() {
      await this.fetch()
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
    padding: $spacing-md;
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
