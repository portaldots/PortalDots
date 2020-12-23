<template>
  <div class="editor-content editor-content-styling">
    <div class="editor-body">
      <form-header />
      <div
        class="editor-content__permanent-questions-info"
        v-if="has_permanent_questions"
      >
        このフォームには「{{ form_name }}」固有の設問が含まれます。
        <button
          class="btn btn-primary btn-sm"
          @click="toggle_permanent_questions"
        >
          <template v-if="is_permanent_questions_visible">
            固有の設問を非表示
          </template>
          <template v-else>固有の設問を表示</template>
        </button>
      </div>
      <div v-if="has_permanent_questions && is_permanent_questions_visible">
        <component
          v-for="question in permanent_questions"
          :is="question_component_name(question.type)"
          :question_id="question.id"
          :key="question.id"
          :is_permanent="question.is_permanent || undefined"
          class="question question--permanent"
        />
      </div>
      <div
        class="editor-content__no-question text-muted"
        v-if="questions.length === 0"
      >
        <p class="mb-4">
          <i class="far fa-edit fa-3x"></i>
        </p>
        <p class="lead">右側の[設問を追加]から設問を追加しましょう</p>
        <p class="mb-0">
          このフォームには設問が1つもありません。[設問を追加]から設問を追加してください。
        </p>
      </div>
      <draggable
        tag="div"
        v-model="questions"
        :animation="200"
        ghostClass="ghost"
        handle=".form-item__handle"
        @start="on_drag_start"
        @end="on_drag_end"
      >
        <transition-group type="transition" :name="!drag ? 'flip-list' : 'no'">
          <component
            v-for="question in questions"
            :is="question_component_name(question.type)"
            :question_id="question.id"
            :key="question.id"
            :is_permanent="question.is_permanent || undefined"
            class="question"
          />
        </transition-group>
      </draggable>
    </div>
  </div>
</template>

<script>
import draggable from 'vuedraggable'
import FormHeader from './form/FormHeader.vue'
import QuestionText from './form/QuestionText.vue'
import QuestionHeading from './form/QuestionHeading.vue'
import QuestionTextarea from './form/QuestionTextarea.vue'
import QuestionNumber from './form/QuestionNumber.vue'
import QuestionUpload from './form/QuestionUpload.vue'
import QuestionRadio from './form/QuestionRadio.vue'
import QuestionSelect from './form/QuestionSelect.vue'
import QuestionCheckbox from './form/QuestionCheckbox.vue'
import { DRAG_START, DRAG_END, UPDATE_QUESTIONS_ORDER } from '../store/editor'
import { SAVE_STATUS_SAVING } from '../store/status'

export default {
  components: {
    draggable,
    FormHeader,
    QuestionText,
    QuestionHeading,
    QuestionTextarea,
    QuestionNumber,
    QuestionUpload,
    QuestionRadio,
    QuestionSelect,
    QuestionCheckbox
  },
  data() {
    return {
      is_permanent_questions_visible: false
    }
  },
  computed: {
    is_saving() {
      return this.$store.state.status.save_status === SAVE_STATUS_SAVING
    },
    drag() {
      return this.$store.state.editor.drag
    },
    permanent_questions() {
      return this.$store.state.editor.permanent_questions
    },
    has_permanent_questions() {
      return this.$store.state.editor.permanent_questions.length > 0
    },
    form_name() {
      return this.$store.state.editor.form.name
    },
    questions: {
      get() {
        return this.$store.state.editor.questions
      },
      set(new_value) {
        this.$store.dispatch(`editor/${UPDATE_QUESTIONS_ORDER}`, new_value)
      }
    }
  },
  methods: {
    question_component_name(question_type) {
      return `Question${question_type
        .charAt(0)
        .toUpperCase()}${question_type.slice(1)}`
    },
    toggle_permanent_questions() {
      this.is_permanent_questions_visible = !this.is_permanent_questions_visible
    },
    on_drag_start() {
      this.$store.dispatch(`editor/${DRAG_START}`)
    },
    on_drag_end() {
      this.$store.dispatch(`editor/${DRAG_END}`)
    }
  }
}
</script>

<style lang="scss" scoped>
.editor-content {
  padding: 3rem;
  &__no-question {
    padding: 3rem;
    text-align: center;
  }
  &__permanent-questions-info {
    background: $color-bg-light;
    display: flex;
    justify-content: space-between;
    padding: $spacing-md $spacing;
  }
}

.question {
  &--permanent {
    background: $color-bg-light;
  }
}

.editor-body {
  background: $color-bg-white;
  box-shadow: 0 0.1rem 0.1rem rgba(0, 0, 0, 0.07);
  margin: 0 auto;
  max-width: 960px;
  width: 100%;
}

.ghost {
  opacity: 0.5;
}

.flip-list-move {
  transition: transform 0.5s;
}

.no-move {
  transition: transform 0s;
}
</style>
