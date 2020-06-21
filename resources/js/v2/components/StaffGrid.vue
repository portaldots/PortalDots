<template>
  <div class="staff_grid">
    <div class="staff_grid-toolbar">
      <a class="btn is-primary" v-if="createUrl" :href="createUrl">
        <i class="fas fa-plus fa-fw"></i>
        新規作成
      </a>
      <a
        class="btn is-secondary"
        v-if="csvExportUrl"
        :href="csvExportUrl"
        data-turbolinks="false"
      >
        <i class="fas fa-file-csv"></i>
        CSVで出力
      </a>
    </div>
    <div class="staff_grid__table_wrap" v-if="paginator">
      <table class="staff_grid-table">
        <thead class="staff_grid-table__thead">
          <tr class="staff_grid-table__tr">
            <th class="staff_grid-table__th">
              操作
            </th>
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
            class="staff_grid-table__tr"
            v-for="row in paginator.data"
            :key="row.id"
          >
            <td class="staff_grid-table__td">
              <slot name="activities" :row="row" />
            </td>
            <td
              class="staff_grid-table__td"
              v-for="(cell, keyName) in row"
              :key="`${row.id}-${keyName}`"
            >
              <slot name="td" :row="row" :keyName="keyName" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="staff_grid-loading" v-else>
      読み込み中…
    </div>
  </div>
</template>

<script>
import Axios from 'axios'

const axios = Axios.create({
  headers: {
    'X-Requested-With': 'XMLHttpRequest'
  }
})

export default {
  props: {
    apiUrl: {
      type: String,
      required: true
    },
    createUrl: {
      type: String,
      default: ''
    },
    csvExportUrl: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      loading: true,
      keys: [],
      paginator: null,
      currentPage: 1,
      perPage: 10
    }
  },
  async mounted() {
    await this.fetch()
  },
  methods: {
    async fetch() {
      this.loading = true
      const res = await axios.get(
        `${this.apiUrl}?is_ajax=true&current_page=${this.currentPage}&per_page=${this.perPage}`
      )
      this.keys = res.data.keys
      this.paginator = res.data.paginator
      this.loading = false
    }
  }
}
</script>

<style lang="scss" scoped>
.staff_grid {
  &-toolbar {
    background: $color-bg-white;
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
      border-bottom: 2px solid $color-border;
    }
    &__th {
      font-size: 0.75rem;
      padding: $spacing-md;
      text-align: left;
      white-space: nowrap;
    }
    &__tr {
      border-bottom: 1px solid $color-border;
      &:last-child {
        border: 0;
      }
      &:hover {
        background: $color-bg-light;
      }
    }
    &__td {
      font-size: 0.9rem;
      padding: $spacing-sm $spacing-md;
      white-space: nowrap;
    }
  }
}
</style>
