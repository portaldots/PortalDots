<template>
  <div class="editor-sidebar">
    <div class="editor-sidebar__header">設問を追加</div>
    <div class="editor-sidebar__body">
      <div class="editor-sidebar__types">
        <button
          v-for="type in types"
          :key="type.value"
          class="btn editor-sidebar__type"
          @click="add_question(type.value)"
        >
          <i :class="`${type.icon} fa-fw editor-sidebar__type__icon`"></i>
          <span class="editor-sidebar__type__label">{{ type.label }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ADD_QUESTION } from '../store/editor'
import { SAVE_STATUS_SAVING } from '../store/status'

export default {
  methods: {
    async add_question(type) {
      await this.$store.dispatch(`editor/${ADD_QUESTION}`, type)
    }
  },
  computed: {
    is_saving() {
      return this.$store.state.status.save_status === SAVE_STATUS_SAVING
    },
    types() {
      return [
        {
          value: 'heading',
          icon: 'fas fa-heading',
          label: 'セクション見出し'
        },
        {
          value: 'text',
          icon: 'fas fa-grip-lines',
          label: '一行入力'
        },
        {
          value: 'number',
          icon: 'fas fa-dice',
          label: '整数入力'
        },
        {
          value: 'textarea',
          icon: 'fas fa-align-justify',
          label: '複数行入力'
        },
        {
          value: 'radio',
          icon: 'far fa-dot-circle',
          label: '単一選択(ラジオボタン)'
        },
        {
          value: 'select',
          icon: 'far fa-list-alt',
          label: '単一選択(ドロップダウン)'
        },
        {
          value: 'checkbox',
          icon: 'far fa-check-square',
          label: '複数選択(チェックボックス)'
        },
        {
          value: 'upload',
          icon: 'far fa-file',
          label: 'ファイルアップロード'
        },
        {
          value: 'table',
          icon: 'fas fa-table',
          label: 'テーブル'
        }
      ]
    }
  }
}
</script>

<style lang="scss" scoped>
@use "sass:math";

$editor-sidebar-padding: $spacing-md;

.editor-sidebar {
  background: $color-bg-surface-2;
  bottom: 0;
  box-shadow: -0.1rem 0 0.3rem -0.2rem $color-box-shadow;
  display: flex;
  flex-direction: column;
  position: fixed;
  right: 0;
  top: $editor-header-height;
  width: $editor-sidebar-width;
  z-index: 10;
  &__header {
    background: $color-bg-surface-3;
    border-bottom: 1px solid $color-border;
    color: $color-text;
    padding: $editor-sidebar-padding;
  }
  &__body {
    flex: 1;
    -webkit-overflow-scrolling: touch;
    overflow-x: hidden;
    overflow-y: scroll;
  }
  &__type {
    align-items: center;
    border: 0;
    border-radius: 0;
    color: $color-text;
    display: flex;
    padding: $spacing-s $editor-sidebar-padding;
    position: relative;
    text-align: left;
    width: 100%;
    &::before {
      border-bottom: 1px solid $color-border;
      bottom: 0;
      content: '';
      display: block;
      left: $editor-sidebar-padding;
      position: absolute;
      right: $editor-sidebar-padding;
    }
    &:last-child {
      margin-bottom: 0;
    }
    &:hover:not(:disabled) {
      background: $color-bg-light;
    }
    &__icon {
      color: $color-muted;
      font-size: 1.25rem;
      margin-right: math.div($editor-sidebar-padding, 2);
    }
  }
}
</style>
