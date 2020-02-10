<template>
  <!--
    TODO: value に応じて、ファイル削除ボタンなどを表示するようにしたい
    というか、ファイルピッカーでファイルを選択したら、即座にファイル送信
    しちゃって、削除ボタン押したら即座に削除、とかでも良いかも
    （ファイルを選択するとすぐにサーバーにアップロードされます、的な
    注釈とか必要かも）
  -->
  <input
    type="file"
    :id="inputId"
    :name="inputName"
    class="form-control"
    :required="required"
  />
</template>

<script>
export default {
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
    }
  }
}
</script>
