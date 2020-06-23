<template>
  <div class="staff_grid">
    <GridTable
      :keys="keys"
      :sortableKeys="sortableKeys"
      :orderBy="orderBy"
      :direction="direction"
      :paginator="paginator"
      :page="page"
      :perPage="perPage"
      :loading="loading"
      @clickFirst="onClickFirst"
      @clickPrev="onClickPrev"
      @clickNext="onClickNext"
      @clickLast="onClickLast"
      @clickReload="onClickReload"
      @clickTh="onClickTh"
      @changePerPage="onChangePerPage"
    >
      <template v-slot:toolbar>
        <slot name="toolbar" />
      </template>
      <template v-slot:th="{ keyName }">
        <slot name="th" :keyName="keyName" />
      </template>
      <template v-slot:activities="{ row }">
        <slot name="activities" :row="row" />
      </template>
      <template v-slot:td="{ row, keyName }">
        <slot name="td" :row="row" :keyName="keyName" />
      </template>
    </GridTable>
  </div>
</template>

<script>
import Axios from 'axios'
import GridTable from './GridTable.vue'

const axios = Axios.create({
  headers: {
    'X-Requested-With': 'XMLHttpRequest'
  }
})

export default {
  components: {
    GridTable
  },
  props: {
    apiUrl: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      keys: [],
      sortableKeys: [],
      orderBy: '',
      direction: '',
      paginator: null,
      page: 1,
      perPage: 25,
      needReload: false,
      loading: true
    }
  },
  async mounted() {
    this.setFromUrlParams()
    window.addEventListener('popstate', this.onPopState, false)
    await this.fetch()
  },
  destroyed() {
    this.needReload = true
  },
  methods: {
    async onPopState() {
      if (this.needReload) {
        // 別のページへ移動してからブラウザバックしてこのページに戻った場合、
        // 正常に画面が表示されないので、ここで再読込する
        window.location.reload()
        return
      }
      this.setFromUrlParams()
      await this.fetch()
    },
    async fetch() {
      this.loading = true
      const res = await axios.get(
        `${this.apiUrl}?page=${this.page}&per_page=${this.perPage}&order_by=${this.orderBy}&direction=${this.direction}`
      )
      this.keys = res.data.keys
      this.sortableKeys = res.data.sortable_keys
      this.orderBy = res.data.order_by
      this.direction = res.data.direction
      this.paginator = res.data.paginator
      this.loading = false
    },
    async onClickFirst() {
      this.page = 1
      this.setUrlParams()
      await this.fetch()
    },
    async onClickPrev() {
      this.page -= 1
      this.setUrlParams()
      await this.fetch()
    },
    async onClickNext() {
      this.page += 1
      this.setUrlParams()
      await this.fetch()
    },
    async onClickLast() {
      this.page = this.paginator.last_page
      this.setUrlParams()
      await this.fetch()
    },
    async onClickReload() {
      await this.fetch()
    },
    async onClickTh(keyName) {
      if (this.orderBy === keyName) {
        // 現在がascだったらdescに、descだったらascに変える
        this.direction = {
          asc: 'desc',
          desc: 'asc'
        }[this.direction]
      } else {
        this.orderBy = keyName
        this.direction = 'asc'
      }
      this.setUrlParams()
      await this.fetch()
    },
    async onChangePerPage(perPage) {
      this.perPage = perPage
      if (
        this.paginator &&
        Math.ceil(this.paginator.total / perPage) < this.page
      ) {
        this.page = Math.ceil(this.paginator.total / perPage)
      }
      this.setUrlParams()
      await this.fetch()
    },
    setUrlParams() {
      window.history.pushState(
        '',
        '',
        `?page=${this.page}&per_page=${this.perPage}&order_by=${this.orderBy}&direction=${this.direction}`
      )
    },
    setFromUrlParams() {
      const queries = window.location.search
        .replace('?', '')
        .split('&')
        .map(e => e.split('='))
        .reduce((obj, e) => ({ ...obj, [e[0]]: e[1] }), {})

      if (queries.page) {
        this.page = parseInt(queries.page, 10)
      }
      if (queries.per_page) {
        this.perPage = parseInt(queries.per_page, 10)
      }
      if (queries.order_by) {
        this.orderBy = queries.order_by
      }
      if (queries.direction) {
        this.direction = queries.direction
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.staff_grid {
  padding: $spacing;
}
</style>
