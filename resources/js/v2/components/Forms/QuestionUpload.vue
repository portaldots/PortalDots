<template>
  <!--
    TODO: value に応じて、ファイル削除ボタンなどを表示するようにしたい
    というか、ファイルピッカーでファイルを選択したら、即座にファイル送信
    しちゃって、削除ボタン押したら即座に削除、とかでも良いかも
    （ファイルを選択するとすぐにサーバーにアップロードされます、的な
    注釈とか必要かも）
  -->
  <div>
    <template v-if="!value || showInput">
      <input
        type="file"
        :id="inputId"
        :name="inputName"
        class="form-control"
        :required="required"
        :accept="accept"
        :disabled="disabled"
      />
      <div class="uploaded-notice" v-if="value">
        <button
          type="button"
          class="btn is-secondary is-sm"
          @click="restoreFile"
        >
          削除取り消し
        </button>
        — 注意 :
        このページ最下部の「送信」をクリックするまで、削除は反映されません。
      </div>
    </template>
    <div class="uploaded-notice" v-else>
      <!-- 以前アップロードしたファイルを回答として利用した場合、"__KEEP__" という値を送信する -->
      <input type="hidden" :name="inputName" value="__KEEP__" />
      <strong class="uploaded-notice__success">
        <i class="fas fa-check"></i>
        アップロード済
      </strong>
      —
      <a :href="value" target="_blank" rel="noopener">
        アップロード済ファイルをダウンロード
      </a>
      <button
        type="button"
        class="btn is-secondary is-sm"
        @click="deleteFile"
        :disabled="disabled"
      >
        削除
      </button>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      showInput: false
    }
  },
  methods: {
    deleteFile() {
      this.showInput = true
    },
    restoreFile() {
      this.showInput = false
    }
  },
  props: {
    inputId: {
      type: String,
      default: null
    },
    inputName: {
      type: String,
      default: null
    },
    required: {
      type: Boolean,
      default: false
    },
    invalid: {
      type: String,
      default: null
    },
    value: {
      type: String,
      default: null
    },
    allowedTypes: {
      type: Array,
      default: () => []
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    accept() {
      // .jpg,.pdf,.doc のような形式の文字列にする
      return `.${this.allowedTypes.join(',.')}`
    }
  }
}
</script>

<style lang="scss" scoped>
.uploaded-notice {
  color: $color-muted;
  margin: $spacing-xs 0 0;
  &__success {
    color: $color-success;
  }
}
</style>
