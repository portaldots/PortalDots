<template>
    <div class="edit-panel">
        <div class="form-group row" v-if="show_required_switch">
            <span class="col-sm-2 col-form-label">回答必須か</span>
            <div class="col-sm-10">
                <label class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" v-model="is_required" :disabled="is_deleting">
                    <span class="custom-control-label">この設問への回答は必須</span>
                </label>
            </div>
        </div>
        <label class="form-group row" v-if="label_name">
            <span class="col-sm-2 col-form-label">{{ label_name }}</span>
            <div class="col-sm-10">
                <input type="text" class="form-control" v-model="name" @blur="save" :disabled="is_deleting" />
            </div>
        </label>
        <label class="form-group row" v-if="label_description">
            <span class="col-sm-2 col-form-label">{{ label_description }}</span>
            <div class="col-sm-10">
                <textarea class="form-control" v-model="description" @blur="save" :disabled="is_deleting" />
            </div>
        </label>
        <label class="form-group row" v-if="label_number_min">
            <span class="col-sm-2 col-form-label">{{ label_number_min }}</span>
            <div class="col-sm-10">
                <input type="number" min="0" class="form-control" v-model="number_min" @blur="save" :disabled="is_deleting" />
            </div>
        </label>
        <label class="form-group row" v-if="label_number_max">
            <span class="col-sm-2 col-form-label">{{ label_number_max }}</span>
            <div class="col-sm-10">
                <input type="number" min="0" class="form-control" v-model="number_max" @blur="save" :disabled="is_deleting" />
            </div>
        </label>
        <label class="form-group row" v-if="show_allowed_types">
            <span class="col-sm-2 col-form-label">許可される拡張子(<code>|</code>区切りで指定)</span>
            <div class="col-sm-10">
                <input type="text" class="form-control" v-model="allowed_types" @blur="save" :disabled="is_deleting" />
                <small class="form-text text-muted mb-0">
                    画像アップロードを許可したい場合 : <code>png|jpg|jpeg|gif</code> と入力。
                </small>
            </div>
        </label>
        <div class="row mb-2">
            <div class="offset-sm-2 col-sm-10 text-right">
                <button
                    class="btn btn-link text-danger p-0"
                    @click="deleteQuestion"
                    :disabled="is_deleting"
                >
                    この項目を削除
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    import { UPDATE_QUESTION, SAVE_QUESTION, DELETE_QUESTION } from '../../store/editor';
    import { SAVE_STATUS_SAVING } from '../../store/status';

    export default {
        props: {
            question: {
                required: true,
            },
            show_required_switch: {
                required: false,
                default: true,
            },
            label_name: {
                required: false,
                default: '設問名',
            },
            label_description: {
                required: false,
                default: '説明',
            },
            label_number_min: {
                required: false,
                default: '最小文字数',
            },
            label_number_max: {
                required: false,
                default: '最大文字数',
            },
            show_allowed_types: {
                required: false,
                default: false,
            },
            show_options: {
                required: false,
                default: false,
            }
        },
        data() {
            return {
                is_deleting: false,
            };
        },
        methods: {
            save() {
                this.$store.dispatch('editor/' + SAVE_QUESTION, this.question.id);
            },
            deleteQuestion() {
                if (window.confirm('設問を削除すると、この設問に対する回答も全て削除されます。本当に削除しますか？')) {
                    this.is_deleting = true;
                    this.$store.dispatch('editor/' + DELETE_QUESTION, this.question.id);
                }
            }
        },
        // TODO: 変更点がない場合、saveメソッドが実行されないようにする
        computed: {
            name: {
                get() {
                    return this.question.name;
                },
                set(new_value) {
                    this.$store.commit('editor/' + UPDATE_QUESTION, {
                        id: this.question.id,
                        key: 'name',
                        value: new_value,
                    });
                }
            },
            description: {
                get() {
                    return this.question.description;
                },
                set(new_value) {
                    this.$store.commit('editor/' + UPDATE_QUESTION, {
                        id: this.question.id,
                        key: 'description',
                        value: new_value,
                    });
                }
            },
            is_required: {
                get() {
                    return this.question.is_required;
                },
                set(new_value) {
                    this.$store.commit('editor/' + UPDATE_QUESTION, {
                        id: this.question.id,
                        key: 'is_required',
                        value: new_value,
                    });
                    this.save();
                }
            },
            number_min: {
                get() {
                    return this.question.number_min;
                },
                set(new_value) {
                    this.$store.commit('editor/' + UPDATE_QUESTION, {
                        id: this.question.id,
                        key: 'number_min',
                        value: new_value,
                    });
                }
            },
            number_max: {
                get() {
                    return this.question.number_max;
                },
                set(new_value) {
                    this.$store.commit('editor/' + UPDATE_QUESTION, {
                        id: this.question.id,
                        key: 'number_max',
                        value: new_value,
                    });
                }
            },
            allowed_types: {
                get() {
                    return this.question.allowed_types;
                },
                set(new_value) {
                    this.$store.commit('editor/' + UPDATE_QUESTION, {
                        id: this.question.id,
                        key: 'allowed_types',
                        value: new_value,
                    });
                }
            }
        }
    };
</script>
