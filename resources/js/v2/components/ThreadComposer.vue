<template>
  <form
    :action="mode === 'internal_note' ? notesUrl : messagesUrl"
    method="post"
    enctype="multipart/form-data"
  >
    <input type="hidden" name="_token" :value="csrfToken" />
    <input type="hidden" name="client_token" :value="clientToken" />

    <list-view-form-group>
      <template #label>入力モード</template>
      <label class="thread-composer__mode">
        <input v-model="mode" type="radio" value="message" />
        メッセージ
      </label>
      <label class="thread-composer__mode">
        <input v-model="mode" type="radio" value="internal_note" />
        内部メモ
      </label>
    </list-view-form-group>

    <list-view-form-group label-for="thread_composer_body">
      <template #label>本文</template>
      <textarea
        id="thread_composer_body"
        name="body"
        class="form-control"
        rows="6"
        required
      ></textarea>
    </list-view-form-group>

    <list-view-form-group label-for="thread_composer_attachments">
      <template #label>添付ファイル</template>
      <template #description>
        {{ maxFiles }}個まで、1つにつき10MBまで添付できます。
        <template v-if="mode === 'internal_note'">内部メモの添付ファイルは企画側には表示されません。</template>
      </template>
      <input
        id="thread_composer_attachments"
        type="file"
        name="attachments[]"
        multiple
        class="form-control"
      />
    </list-view-form-group>

    <div class="text-right pt-spacing-md">
      <template v-if="mode === 'message'">
        <button type="submit" name="target_status" value="awaiting_reply" class="btn is-secondary">
          送信して返答待ちにする
        </button>
        <button type="submit" name="target_status" value="resolved" class="btn is-primary">
          送信して解決済みにする
        </button>
      </template>
      <template v-else>
        <button type="submit" class="btn is-primary">メモを追加</button>
      </template>
    </div>
  </form>
</template>

<script>
import ListViewFormGroup from "./ListViewFormGroup.vue";

export default {
  components: {
    ListViewFormGroup,
  },
  props: {
    messagesUrl: {
      type: String,
      required: true,
    },
    notesUrl: {
      type: String,
      required: true,
    },
    csrfToken: {
      type: String,
      required: true,
    },
    clientToken: {
      type: String,
      required: true,
    },
    maxFiles: {
      type: Number,
      default: 5,
    },
  },
  data() {
    return {
      mode: "message",
    };
  },
};
</script>

<style lang="scss" scoped>
.thread-composer__mode {
  display: inline-flex;
  align-items: center;
  gap: $spacing-xs;
  margin-right: $spacing-md;
  font-weight: normal;
}
</style>
