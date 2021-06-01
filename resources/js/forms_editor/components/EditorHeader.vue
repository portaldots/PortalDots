<template>
  <header class="editor-header editor-header-styling">
    <div class="editor-header__title">フォームエディター</div>
    <div
      class="editor-header__status"
      v-if="!is_unexpected_error && !validation_error"
    >
      <span class="text-muted editor-header__status__saving" v-if="is_saving">
        <i class="fas fa-sync fa-spin fa-fw"></i>
        保存中...
        <template v-if="request_queued_count > 1">
          ({{ request_queued_count }} 件処理中)
        </template>
      </span>
      <span class="text-success editor-header__status__saved" v-if="is_saved">
        <i class="fas fa-check fa-fw"></i>
        保存しました
      </span>
    </div>
    <div class="editor-header__status" v-else-if="validation_error">
      <span class="text-danger editor-header__status__error">
        <strong>{{ validation_error }}</strong>
      </span>
    </div>
    <div class="editor-header__status" v-else>
      <span class="text-danger editor-header__status__error">
        <i class="fas fa-exclamation-circle fa-fw"></i>
        エラーが発生しました
      </span>
    </div>
    <div class="editor-header__actions">
      <a :href="preview_url" target="_blank">プレビュー</a>
      <template v-if="is_public">
        <AppBadge success strong>公開</AppBadge>
        <button
          class="btn is-danger"
          :disabled="is_saving"
          @click="setPrivate()"
        >
          非公開にする
        </button>
      </template>
      <template v-else>
        <AppBadge danger strong>非公開</AppBadge>
        <button
          class="btn is-primary"
          :disabled="is_saving"
          @click="setPublic()"
        >
          公開する
        </button>
      </template>
    </div>
  </header>
</template>

<script>
import { SAVE_STATUS_SAVING, SAVE_STATUS_SAVED } from '../store/status'
import { SET_FORM_PUBLIC, SET_FORM_PRIVATE, SAVE_FORM } from '../store/editor'
import AppBadge from '../../v2/components/AppBadge.vue'

export default {
  components: {
    AppBadge
  },
  computed: {
    save_status() {
      return this.$store.state.status.save_status
    },
    is_saving() {
      return this.save_status === SAVE_STATUS_SAVING
    },
    request_queued_count() {
      return this.$store.state.status.request_queued_count
    },
    is_saved() {
      return this.save_status === SAVE_STATUS_SAVED
    },
    is_unexpected_error() {
      return this.$store.state.status.is_unexpected_error
    },
    validation_error() {
      return this.$store.state.status.validation_error
    },
    preview_url() {
      const form_id = this.$store.state.editor.form.id
      return `/staff/forms/${form_id}/preview`
    },
    is_public() {
      return this.$store.state.editor.form.is_public
    }
  },
  methods: {
    save() {
      this.$store.dispatch(`editor/${SAVE_FORM}`)
    },
    setPublic() {
      if (
        window.confirm(
          '公開しますか？\n公開しても受付期間外の場合、団体は回答できません。'
        )
      ) {
        this.$store.commit(`editor/${SET_FORM_PUBLIC}`)
        this.save()
      }
    },
    setPrivate() {
      if (window.confirm('非公開にしますか？')) {
        this.$store.commit(`editor/${SET_FORM_PRIVATE}`)
        this.save()
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.editor-header {
  align-items: center;
  background: $color-behind-text;
  border-bottom: 1px solid $color-border;
  display: flex;
  justify-content: space-between;
  &__status {
    &__saved {
      animation: saved 3s linear both;

      @keyframes saved {
        from,
        90% {
          opacity: 1;
          visibility: visible;
        }
        to {
          opacity: 0;
          visibility: hidden;
        }
      }
    }
  }
}
</style>
