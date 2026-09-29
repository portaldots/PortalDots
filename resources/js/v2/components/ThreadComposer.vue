<template>
  <form
    :action="mode === 'internal_note' ? notesUrl : messagesUrl"
    :class="{ 'is-note': mode === 'internal_note' }"
    method="post"
    enctype="multipart/form-data"
  >
    <input type="hidden" name="_token" :value="csrfToken" />
    <input type="hidden" name="client_token" :value="clientToken" />

    <div class="thread-composer-panel__modes" role="group" aria-label="入力モード">
      <label class="thread-composer-panel__mode" :class="{ 'is-selected': mode === 'message' }">
        <input v-model="mode" class="thread-composer-panel__radio" type="radio" value="message" />
        メッセージ
      </label>
      <label class="thread-composer-panel__mode" :class="{ 'is-selected': mode === 'internal_note' }">
        <input v-model="mode" class="thread-composer-panel__radio" type="radio" value="internal_note" />
        内部メモ
      </label>
    </div>

    <label class="visually-hidden" for="thread_composer_body">本文</label>
    <textarea
      id="thread_composer_body"
      name="body"
      class="form-control"
      :placeholder="mode === 'internal_note' ? '内部メモを入力' : 'メッセージを入力'"
      rows="2"
      required
    ></textarea>

    <div class="thread-composer-panel__footer">
      <div class="thread-composer-panel__attachments">
        <label for="thread_composer_attachments" class="btn is-secondary">添付</label>
        <span class="thread-composer-panel__attachment-limit" aria-hidden="true">最大{{ maxFiles }}件・各10MB</span>
        <input
          id="thread_composer_attachments"
          type="file"
          name="attachments[]"
          multiple
          class="visually-hidden"
          aria-describedby="thread_composer_attachment_help"
          @change="selectedFiles = Array.from($event.target.files).map(file => file.name)"
        />
        <span id="thread_composer_attachment_help" class="visually-hidden">
          {{ maxFiles }}個まで、1つにつき10MBまで添付できます。
          <template v-if="mode === 'internal_note'">内部メモの添付ファイルは企画側には表示されません。</template>
        </span>
        <span v-if="selectedFiles.length" class="thread-composer-panel__selected-files">
          {{ selectedFiles.join('、') }}
        </span>
      </div>
      <div class="thread-composer-panel__actions">
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
    </div>
  </form>
</template>

<script>
export default {
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
      selectedFiles: [],
    };
  },
};
</script>
