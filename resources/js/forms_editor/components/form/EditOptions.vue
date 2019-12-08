<template>
    <div class="edit-options">
        <draggable
            tag="ul"
            v-model="options"
        >
            <li v-for="options">

            </li>
        </draggable>
    </div>
</template>

<script>
    import draggable from 'vuedraggable';
    import { DRAG_START, DRAG_END, UPDATE_QUESTION } from "../../store/editor";
    import { SAVE_STATUS_SAVING } from '../../store/status';

    export default {
        components: {
            draggable,
        },
        computed: {
            props: {
                question: {
                    required: true,
                }
            },
            options: {
                get() {
                    return this.question.options;
                },
                set(new_value) {
                    this.$store.commit('editor/' + UPDATE_QUESTION, {
                        id: this.questions.id,
                        key: 'options',
                        value:
                    });
                }
            },
        },
        methods: {
            question_component_name(question_type) {
                return 'Question' + question_type.charAt(0).toUpperCase() + question_type.slice(1);
            },
            on_drag_start() {
                this.$store.dispatch('editor/' + DRAG_START);
            },
            on_drag_end() {
                this.$store.dispatch('editor/' + DRAG_END);
            }
        }
    };
</script>

<style lang="scss" scoped>
    .editor-content {
        padding: 3rem;

        &__no-question {
            padding: 3rem;
            text-align: center;
        }
    }

    .editor-preview {
        width: 100%;
        max-width: 960px;
        margin: 0 auto;
        background: #fff;
        box-shadow: 0 .1rem .1rem rgba(0, 0, 0, 0.07);
    }

    .ghost {
        opacity: .5;
    }

    .flip-list-move {
        transition: transform 0.5s;
    }

    .no-move {
        transition: transform 0s;
    }
</style>
