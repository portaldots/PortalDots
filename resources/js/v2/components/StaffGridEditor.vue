<template>
  <div class="staff_grid_editor">
    <iframe
      :src="editorUrl"
      class="staff_grid_editor-frame"
      allowtransparency="true"
    />
  </div>
</template>

<script>
export default {
  props: {
    editorUrl: {
      type: String,
      required: true
    }
  }
  // data() {
  //   return {
  //     queries: [],
  //     mode: 'and',
  //     // isDirty: 適用ボタンによってフィルタ設定が反映されていない場合trueになる
  //     isDirty: false,
  //   }
  // },
  // mounted() {
  //   this.queries = this.defaultQueries.map((query) => ({
  //     id: query.id,
  //     item: this.itemsForFilterQuery
  //       .flatMap((item) => (item.sublist ? item.sublist : item))
  //       .find((q) => q.keyName === query.keyName),
  //     operator: query.operator,
  //     value: query.value,
  //   }))
  //   this.mode = this.defaultMode
  //   this.$nextTick(() => {
  //     this.isDirty = false
  //   })
  // },
  // methods: {
  //   addQuery(item) {
  //     const id =
  //       this.queries[this.queries.length - 1] &&
  //       Number.isInteger(this.queries[this.queries.length - 1].id)
  //         ? this.queries[this.queries.length - 1].id + 1
  //         : 0

  //     const defaultValues = {
  //       value: '',
  //     }

  //     switch (item.type) {
  //       case 'string':
  //         defaultValues.operator = 'like'
  //         break
  //       case 'bool':
  //       case 'isNull':
  //         defaultValues.operator = '='
  //         defaultValues.value = '1'
  //         break
  //       default:
  //         defaultValues.operator = '='
  //         break
  //     }

  //     this.queries = [...this.queries, { id, item, ...defaultValues }]
  //   },
  //   removeQuery(queryId) {
  //     this.isDirty = true
  //     this.queries = this.queries.filter((query) => query.id !== queryId)
  //   },
  //   onClickApply() {
  //     this.isDirty = false
  //     this.$emit(
  //       'clickApply',
  //       this.queries.map((query) => ({
  //         keyName: query.item.keyName,
  //         operator: query.operator,
  //         value: query.value,
  //       })),
  //       this.mode
  //     )
  //   },
  //   onClickClearAll() {
  //     this.queries = []
  //     this.onClickApply()
  //     this.$nextTick(() => {
  //       this.isDirty = false
  //     })
  //   },
  // },
  // watch: {
  //   queries: {
  //     handler() {
  //       this.isDirty = true
  //     },
  //     deep: true,
  //   },
  //   mode() {
  //     this.isDirty = true
  //   },
  // },
  // computed: {
  //   itemsForFilterQuery() {
  //     return Object.keys(this.filterableKeys).map((key) => {
  //       if (this.filterableKeys[key].type !== 'belongsTo') {
  //         return {
  //           keyName: key,
  //           type: this.filterableKeys[key].type,
  //           translation: this.keyTranslations[key],
  //           menuLabel: this.keyTranslations[key],
  //         }
  //       }

  //       if (typeof this.filterableKeys[key].keys !== 'object') {
  //         return {}
  //       }

  //       return {
  //         keyName: key,
  //         label: this.keyTranslations[key],
  //         sublist: Object.keys(this.filterableKeys[key].keys).map(
  //           (insideKey) => ({
  //             keyName: `${key}.${insideKey}`,
  //             type: this.filterableKeys[key].keys[insideKey].type,
  //             translation: `${this.keyTranslations[key]} › ${
  //               this.keyTranslations[`${key}.${insideKey}`]
  //             }`,
  //             menuLabel: this.keyTranslations[`${key}.${insideKey}`],
  //           })
  //         ),
  //       }
  //     })
  //   },
  // },
}
</script>

<style lang="scss" scoped>
.staff_grid_editor {
  inset: 0;
  position: absolute;
  &-frame {
    border: 0;
    height: 100%;
    width: 100%;
  }
}
</style>
