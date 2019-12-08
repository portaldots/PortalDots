<template>
    <form-item class="form-header" :item_id="item_id" type_label="フォームの設定" :hide_handle="true">
        <template v-slot:content>
            <h1 class="form-header__name">{{ computed_name }}</h1>
            <div v-html="description_html" />
        </template>
        <template v-slot:edit-panel>
            <div class="form-group row">
                <label for="inputTitle" class="col-sm-2 col-form-label">タイトル</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control input-lg" id="inputTitle" v-model="name" @blur="save">
                </div>
            </div>
            <div class="form-group row">
                <label for="inputDescription" class="col-sm-2 col-form-label">説明</label>
                <div class="col-sm-10">
                    <textarea class="form-control" id="inputDescription" v-model="description" @blur="save" />
                </div>
            </div>
        </template>
    </form-item>
</template>

<script>
    import FormItem from './FormItem';
    import marked from 'marked';
    import { ITEM_HEADER, UPDATE_FORM, SAVE_FORM } from '../../store/editor';
    import { SAVE_STATUS_SAVING } from '../../store/status';

    export default {
        components: {
            FormItem,
        },
        methods: {
            save() {
                this.$store.dispatch('editor/' + SAVE_FORM);
            }
        },
        computed: {
            item_id() {
                return ITEM_HEADER;
            },
            name: {
                get() {
                    return this.$store.state.editor.form.name;
                },
                set(new_value) {
                    this.$store.commit('editor/' + UPDATE_FORM, {
                        key: 'name',
                        value: new_value,
                    });
                }
            },
            description: {
                get() {
                    return this.$store.state.editor.form.description;
                },
                set(new_value) {
                    this.$store.commit('editor/' + UPDATE_FORM, {
                        key: 'description',
                        value: new_value,
                    });
                }
            },
            computed_name() {
                const name = this.name;
                return name ? name : '(無題のフォーム)';
            },
            description_html() {
                const description = this.description;
                return marked(description ? description : '');
            }
        },
    };
</script>

<style lang="scss" scoped>
    .form-header {
        &__name {
            font-size: 1.5rem;
        }
    }
</style>
