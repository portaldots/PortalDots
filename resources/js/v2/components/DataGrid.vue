<template>
  <SideWindowContainer
    v-slot="{
      isSideWindowOpen: isFilterSideWindowOpen,
      toggleSideWindow: toggleFilterSideWindow,
      closeSideWindow: closeFilterSideWindow,
    }"
  >
    <SideWindowContainer
      v-slot="{
        isSideWindowOpen: isEditorSideWindowOpen,
        openSideWindow: openEditorSideWindow,
        closeSideWindow: closeEditorSideWindow,
      }"
    >
      <div class="data_grid">
        <DataGridTable
          :keys="keys"
          :sortableKeys="sortableKeys"
          :orderBy="orderBy"
          :direction="direction"
          :paginator="paginator"
          :page="page"
          :perPage="perPage"
          :loading="loading"
          :isFilterActive="filterQueries.length > 0"
          @clickFirst="onClickFirst"
          @clickPrev="onClickPrev"
          @clickNext="onClickNext"
          @clickLast="onClickLast"
          @clickReload="reload"
          @clickFilter="
            () =>
              onClickFilter(
                toggleFilterSideWindow,
                isFilterSideWindowOpen,
                closeEditorSideWindow
              )
          "
          @clickTh="onClickTh"
          @changePerPage="onChangePerPage"
        >
          <template v-slot:toolbar>
            <slot name="toolbar" />
          </template>
          <template v-slot:th="{ keyName }">
            {{ keyTranslations[keyName] }}
          </template>
          <template v-slot:activities="{ row }">
            <slot
              name="activities"
              :row="row"
              :openEditorByUrl="
                (url, title = '') =>
                  openEditorByUrl({
                    openEditorSideWindow,
                    url,
                    title,
                    isEditorSideWindowOpen,
                    closeFilterSideWindow,
                  })
              "
            />
          </template>
          <template v-slot:td="{ row, keyName }">
            <slot
              name="td"
              :row="row"
              :keyName="keyName"
              :openEditorByUrl="
                (url, title = '') =>
                  openEditorByUrl({
                    openEditorSideWindow,
                    url,
                    title,
                    isEditorSideWindowOpen,
                    closeFilterSideWindow,
                  })
              "
            />
          </template>
        </DataGridTable>
      </div>
      <SideWindow
        :isOpen="isFilterSideWindowOpen"
        @clickClose="toggleFilterSideWindow"
      >
        <template #title>絞り込み</template>
        <DataGridFilter
          :filterableKeys="filterableKeys"
          :keyTranslations="keyTranslations"
          :defaultQueries="filterQueries"
          :defaultMode="filterMode"
          :loading="loading"
          @clickApply="onClickApplyFilter"
        />
      </SideWindow>
      <SideWindow
        :isOpen="isEditorSideWindowOpen"
        @clickClose="closeEditorSideWindow"
        :popUpUrl="sideWindowEditorPopUpUrl"
      >
        <template #title>{{ sideWindowEditorTitle }}</template>
        <DataGridEditor :editorUrl="sideWindowEditorUrl" @urlChanged="reload" />
      </SideWindow>
    </SideWindowContainer>
  </SideWindowContainer>
</template>

<script>
import Axios from "axios";
import DataGridTable from "./DataGridTable.vue";
import SideWindowContainer from "./SideWindowContainer.vue";
import SideWindow from "./SideWindow.vue";
import DataGridFilter from "./DataGridFilter.vue";
import DataGridEditor from "./DataGridEditor.vue";

const axios = Axios.create({
  headers: {
    "X-Requested-With": "XMLHttpRequest",
  },
});

export default {
  components: {
    DataGridTable,
    SideWindowContainer,
    SideWindow,
    DataGridFilter,
    DataGridEditor,
  },
  props: {
    apiUrl: {
      type: String,
      required: true,
    },
    keyTranslations: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      keys: [],
      sortableKeys: [],
      filterableKeys: [],
      orderBy: "",
      direction: "",
      paginator: null,
      page: 1,
      perPage: 25,
      filterQueries: [],
      filterMode: "and",
      needReload: false,
      loading: true,
      sideWindowEditorUrl: "",
      sideWindowEditorTitle: "",
    };
  },
  async mounted() {
    this.setFromUrlParams();
    window.addEventListener("popstate", this.onPopState, false);
    await this.fetch();
  },
  unmounted() {
    this.needReload = true;
  },
  methods: {
    openEditorByUrl({
      openEditorSideWindow,
      url,
      title = "",
      isEditorSideWindowOpen,
      closeFilterSideWindow,
    }) {
      if (!isEditorSideWindowOpen) {
        closeFilterSideWindow();
      }

      // iframe 内でページが開いているということが、リンク先でわかるようにする
      const urlObject = new URL(url);
      urlObject.searchParams.set("iframe", true);
      this.sideWindowEditorUrl = urlObject.href;

      this.sideWindowEditorTitle = title ? title : "編集";
      openEditorSideWindow();
    },
    async onPopState() {
      if (this.needReload) {
        // 別のページへ移動してからブラウザバックしてこのページに戻った場合、
        // 正常に画面が表示されないので、ここで再読込する
        window.location.reload();
        return;
      }
      this.setFromUrlParams();
      await this.fetch();
    },
    async fetch() {
      this.loading = true;
      const res = await axios.get(`${this.apiUrl}?${this.urlParams}`);
      this.keys = res.data.keys;
      this.sortableKeys = res.data.sortable_keys;
      this.filterableKeys = res.data.filterable_keys;
      this.orderBy = res.data.order_by;
      this.direction = res.data.direction;
      this.paginator = res.data.paginator;
      this.loading = false;
    },
    async onClickFirst() {
      this.page = 1;
      this.setUrlParams();
      await this.fetch();
    },
    async onClickPrev() {
      this.page -= 1;
      this.setUrlParams();
      await this.fetch();
    },
    async onClickNext() {
      this.page += 1;
      this.setUrlParams();
      await this.fetch();
    },
    async onClickLast() {
      this.page = this.paginator.last_page;
      this.setUrlParams();
      await this.fetch();
    },
    async reload() {
      await this.fetch();
    },
    onClickFilter(
      toggleSideWindow,
      isFilterSideWindowOpen,
      closeEditorSideWindow
    ) {
      if (!isFilterSideWindowOpen) {
        closeEditorSideWindow();
      }
      toggleSideWindow();
    },
    async onClickApplyFilter(queries, mode) {
      this.filterQueries = [...queries];
      this.filterMode = mode;

      this.setUrlParams();
      await this.fetch();
    },
    async onClickTh(keyName) {
      if (this.orderBy === keyName) {
        // 現在がascだったらdescに、descだったらascに変える
        this.direction = {
          asc: "desc",
          desc: "asc",
        }[this.direction];
      } else {
        this.orderBy = keyName;
        this.direction = "asc";
      }
      this.setUrlParams();
      await this.fetch();
    },
    async onChangePerPage(perPage) {
      this.perPage = perPage;
      if (
        this.paginator &&
        Math.ceil(this.paginator.total / perPage) < this.page
      ) {
        this.page = Math.ceil(this.paginator.total / perPage);
      }
      this.setUrlParams();
      await this.fetch();
    },
    setUrlParams() {
      window.history.replaceState("", "", `?${this.urlParams}`);
    },
    setFromUrlParams() {
      const queries = window.location.search
        .replace("?", "")
        .split("&")
        .map((e) => e.split("="))
        .reduce((obj, e) => ({ ...obj, [e[0]]: e[1] }), {});

      if (queries.page) {
        this.page = parseInt(queries.page, 10);
      }
      if (queries.per_page) {
        this.perPage = parseInt(queries.per_page, 10);
      }
      if (queries.order_by) {
        this.orderBy = queries.order_by;
      }
      if (queries.direction) {
        this.direction = queries.direction;
      }
      if (queries.queries) {
        this.filterQueries = JSON.parse(
          decodeURIComponent(queries.queries)
        ).map((query, index) => ({
          id: index,
          keyName: query.key_name,
          operator: query.operator === "not+like" ? "not like" : query.operator,
          value: query.value,
        }));
      }
      if (queries.mode) {
        this.filterMode = queries.mode;
      }
    },
  },
  computed: {
    sideWindowEditorPopUpUrl() {
      if (!this.sideWindowEditorUrl) return null;

      const url = new URL(this.sideWindowEditorUrl);
      url.searchParams.delete("iframe");
      return url.href;
    },
    urlParams() {
      const params = new URLSearchParams();
      params.append("page", this.page);
      params.append("per_page", this.perPage);
      params.append("order_by", this.orderBy);
      params.append("direction", this.direction);

      if (this.filterQueries && this.filterQueries.length > 0) {
        const stringQueries = JSON.stringify(
          this.filterQueries.map((query) => ({
            key_name: query.keyName,
            operator: query.operator,
            value: query.value,
          }))
        );
        params.append("queries", stringQueries);
        params.append("mode", this.filterMode);
      }

      return params.toString();
    },
  },
};
</script>

<style lang="scss" scoped>
.data_grid {
  background: $color-bg-surface;
}
</style>
