<template>
    <header class="editor-header editor-header-styling">
        <div class="editor-header__title">
            申請フォームエディター
        </div>
        <div class="editor-header__status" v-if="!is_error">
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
        <div class="editor-header__status" v-else>
            <span class="text-danger editor-header__status__error">
                <i class="fas fa-exclamation-circle fa-fw"></i>
                エラーが発生しました
            </span>
        </div>
        <div class="editor-header__actions">
            <a class="btn btn-link" :href="preview_url" target="_blank">プレビュー</a>
            <button class="btn btn-primary" :disabled="is_saving">公開する</button>
        </div>
    </header>
</template>

<script>
    import { SAVE_STATUS_SAVING, SAVE_STATUS_SAVED } from "../store/status";

    export default {
        computed: {
            save_status() {
                return this.$store.state.status.save_status;
            },
            is_saving() {
                return this.save_status === SAVE_STATUS_SAVING;
            },
            request_queued_count() {
                return this.$store.state.status.request_queued_count;
            },
            is_saved() {
                return this.save_status === SAVE_STATUS_SAVED;
            },
            is_error() {
                return this.$store.state.status.is_error;
            },
            preview_url() {
                const form_id = this.$store.state.editor.form.id;
                return `/home_staff/applications/preview/${form_id}`;
            }
        }
    };
</script>

<style lang="scss" scoped>
    .editor-header {
        background: #fff;
        border-bottom: 1px solid rgba(0, 0, 0, .1);
        display: flex;
        justify-content: space-between;
        align-items: center;

        &__status {
            &__saved {
                animation: saved 3s linear both;

                @keyframes saved {
                    from,
                    90%{
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
