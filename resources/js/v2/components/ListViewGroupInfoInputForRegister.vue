<template>
  <ListViewFormGroup>
    <template #label>登録種別</template>
    <div class="group-types">
      <label class="group-types__item">
        <input
          v-model="isIndividual"
          type="radio"
          :name="props.isIndividualInputName"
          :value="false"
        />
        団体
      </label>
      <label class="group-types__item">
        <input
          v-model="isIndividual"
          type="radio"
          :name="props.isIndividualInputName"
          :value="true"
        />
        個人
      </label>
    </div>
  </ListViewFormGroup>
  <template v-if="!isIndividual">
    <ListViewFormGroup :labelFor="props.groupNameInputName">
      <template #label>団体名</template>
      <input
        :id="props.groupNameInputName"
        type="text"
        class="form-control"
        :name="props.groupNameInputName"
        v-model="groupName"
        required
      />
    </ListViewFormGroup>
    <ListViewFormGroup :labelFor="props.groupNameYomiInputName">
      <template #label>団体名(よみ)</template>
      <input
        :id="props.groupNameYomiInputName"
        type="text"
        class="form-control"
        :name="props.groupNameYomiInputName"
        v-model="groupNameYomi"
        required
      />
    </ListViewFormGroup>
    <QuestionHeading name="団体責任者の情報">
      このフォームは団体責任者が入力してください。団体責任者以外の方は入力不要です。
    </QuestionHeading>
  </template>
</template>

<script setup>
import { ref } from "vue";
import ListViewFormGroup from "./ListViewFormGroup.vue";
import QuestionHeading from "./Forms/QuestionHeading.vue";

const props = defineProps({
  isIndividualInputName: {
    type: String,
    required: true,
  },
  groupNameInputName: {
    type: String,
    required: true,
  },
  groupNameYomiInputName: {
    type: String,
    required: true,
  },
  defaultIsIndividualValue: {
    type: Boolean,
    default: false,
  },
  defaultGroupNameValue: {
    type: String,
    default: "",
  },
  defaultGroupNameYomiValue: {
    type: String,
    default: "",
  },
});

const isIndividual = ref(props.defaultIsIndividualValue);
const groupName = ref(props.defaultGroupNameValue);
const groupNameYomi = ref(props.defaultGroupNameYomiValue);
</script>

<style lang="scss" scoped>
.group-types {
  display: flex;
  justify-content: center;
  gap: $spacing;

  &__item {
    display: flex;
    align-items: center;
    align-items: center;
    gap: 0.5rem;
    max-width: 100%;
  }
}
</style>
