<template>
  <div
    class="form-item"
    :class="{
      'form-item--active': is_edit_panel_open,
      'form-item--drag': drag,
      'form-item--disable-edit': disable_edit
    }"
    :ref="`form_item_${item_id}`"
  >
    <div class="form-item__handle" v-if="!hide_handle">
      <i class="fas fa-grip-horizontal"></i>
    </div>
    <div class="form-item__content" @click="toggle_open_state">
      <div class="form-item__content__inner">
        <slot name="content" />
        <p class="text-muted" v-if="custom_form && disable_edit">
          <small>
            これは
            {{ custom_form.form_name }}
            に固有の項目です。編集・削除はできません。
          </small>
        </p>
      </div>
    </div>
    <div
      class="form-item__edit-panel"
      :class="{ 'form-item__edit-panel--open': is_edit_panel_open }"
    >
      <div class="form-item__edit-panel__type">
        {{ type_label }}
      </div>
      <slot name="edit-panel" />
    </div>
  </div>
</template>

<script>
import { TOGGLE_OPEN_STATE } from '../../store/editor'
import { SAVE_STATUS_SAVING } from '../../store/status'

export default {
  props: {
    item_id: {
      required: true
    },
    type_label: {
      type: String,
      required: true
    },
    hide_handle: {
      required: false,
      type: Boolean,
      default: false
    },
    disable_edit: {
      required: false,
      type: Boolean,
      default: false
    }
  },
  computed: {
    custom_form() {
      return this.$store.state.editor.form.custom_form
    },
    is_saving() {
      return this.$store.state.status.save_status === SAVE_STATUS_SAVING
    },
    drag() {
      return this.$store.state.editor.drag
    },
    is_edit_panel_open() {
      return this.$store.state.editor.open_item_id === this.item_id
    }
  },
  watch: {
    is_edit_panel_open(value) {
      if (value) {
        this.scroll_to_me()
      }
    }
  },
  created() {
    if (this.is_edit_panel_open) {
      this.scroll_to_me()
    }
  },
  methods: {
    toggle_open_state() {
      if (this.disable_edit) {
        return
      }

      this.$store.commit(`editor/${TOGGLE_OPEN_STATE}`, {
        item_id: this.item_id
      })
    },
    scroll_to_me() {
      this.$nextTick(() => {
        const top =
          this.$refs[`form_item_${this.item_id}`].getBoundingClientRect().top +
          window.scrollY -
          document.querySelector('.editor-header').getBoundingClientRect()
            .bottom -
          16
        window.scroll({
          top,
          behavior: 'smooth'
        })
      })
    }
  }
}
</script>

<style lang="scss" scoped>
$form-item-padding: 1.5rem;

.form-item {
  background: #fff;
  border: 1px solid transparent;
  border-left-width: 5px;
  box-shadow: none;
  position: relative;
  transition: 0.25s ease box-shadow, 0.25s ease z-index;
  z-index: 10;
  &__handle {
    color: #a7a7a7;
    cursor: move;
    display: none;
    left: 0;
    padding: 0.25rem;
    position: absolute;
    text-align: center;
    top: 0;
    width: 100%;
  }
  &:hover:not(&--active) {
    z-index: 20;
  }
  &:hover:not(&--drag):not(&--disable-edit),
  &--active {
    border: 1px solid #007bff;
    border-left-width: 5px;
    border-radius: 5px;
  }
  &:hover &__handle {
    display: block;
  }
  &--active {
    z-index: 15;
  }
  &__content {
    cursor: pointer;
    padding: $form-item-padding;
    &__inner {
      pointer-events: none;
      user-select: none;
    }
  }
  &--disable-edit &__content {
    cursor: auto;
  }
  &__edit-panel {
    background: lighten(#f8fafc, 1%);
    border-bottom: 1px solid #f8fafc;
    box-shadow: inset 0 0.3rem 0.25rem -0.2rem rgba(0, 0, 0, 0.07);
    display: none;
    overflow: hidden;
    padding: $form-item-padding;
    &--open {
      display: block;
    }
    &__type {
      border-bottom: 1px solid #ccc;
      font-weight: bold;
      margin-bottom: 1rem;
      padding-bottom: 0.5rem;
    }
  }
}
</style>
