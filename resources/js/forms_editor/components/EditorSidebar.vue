<template>
  <div class="editor-sidebar">
    <div class="editor-sidebar__header text-white">
      ツールパレット
    </div>
    <div class="editor-sidebar__body">
      <div class="editor-sidebar__tools">
        <button
          v-for="tool in tools"
          :key="tool.type"
          class="btn editor-sidebar__tool border-light"
          @click="add_question(tool.type)"
        >
          <i
            :class="`${tool.icon} fa-fw text-muted editor-sidebar__tool__icon`"
          ></i>
          <span class="editor-sidebar__tool__label">{{ tool.label }}</span>
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
    tools() {
      return [
        {
          type: 'heading',
          icon: 'fas fa-heading',
          label: 'セクション見出し'
        },
        {
          type: 'text',
          icon: 'fas fa-grip-lines',
          label: '一行入力'
        },
        {
          type: 'number',
          icon: 'fas fa-dice',
          label: '整数入力'
        },
        {
          type: 'textarea',
          icon: 'fas fa-align-justify',
          label: '複数行入力'
        },
        {
          type: 'radio',
          icon: 'far fa-dot-circle',
          label: '単一選択(ラジオボタン)'
        },
        {
          type: 'select',
          icon: 'far fa-list-alt',
          label: '単一選択(ドロップダウン)'
        },
        {
          type: 'checkbox',
          icon: 'far fa-check-square',
          label: '複数選択(チェックボックス)'
        },
        {
          type: 'upload',
          icon: 'far fa-file',
          label: 'ファイルアップロード'
        }
      ]
    }
  }
}
</script>

<style lang="scss" scoped>
$editor-sidebar-padding: 1rem;

.editor-sidebar {
  background: #fff;
  bottom: 0;
  box-shadow: -0.1rem 0 0.1rem rgba(0, 0, 0, 0.07);
  display: flex;
  flex-direction: column;
  position: fixed;
  right: 0;
  top: $app-navbar-height + $editor-header-height;
  width: $editor-sidebar-width;
  z-index: 10;
  &__header {
    background: $color-muted;
    padding: $editor-sidebar-padding;
  }
  &__body {
    flex: 1;
    -webkit-overflow-scrolling: touch;
    overflow-x: hidden;
    overflow-y: scroll;
    padding: $editor-sidebar-padding;
  }
  &__tool {
    align-items: center;
    box-shadow: 0 0.1rem 0.1rem rgba(0, 0, 0, 0.07);
    display: flex;
    margin-bottom: $editor-sidebar-padding;
    padding: $editor-sidebar-padding;
    text-align: left;
    width: 100%;
    &:last-child {
      margin-bottom: 0;
    }
    &:hover:not(:disabled) {
      background: #f2f2f2;
    }
    &__icon {
      font-size: 1.25rem;
      margin-right: $editor-sidebar-padding / 2;
    }
  }
}
</style>
