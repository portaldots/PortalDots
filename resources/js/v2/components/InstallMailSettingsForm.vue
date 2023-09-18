<script setup>
// このコンポーネントは、yarn dev での実行時は正常に動作しないことがある。
// その場合、 yarn build でビルドしてデバッグする。

import Axios from "axios";
import { ref, watch } from "vue";
import AppInfoBox from "./AppInfoBox.vue";
import AppContainer from "./AppContainer.vue";

const props = defineProps({
  csrfToken: {
    type: String,
    required: true,
  },
  updateSettingsPath: {
    type: String,
    required: true,
  },
  sendTestPath: {
    type: String,
    required: true,
  },
  nextScreenPath: {
    type: String,
    required: true,
  },
});

const isSubmitting = ref(false);
const submittingStatus = ref(""); // "saving" | "test-sending" | ""
const form = ref(null);
const errorMessage = ref("");

watch(isSubmitting, (value) => {
  if (form.value) {
    // フォーム無効化
    for (const element of form.value.elements) {
      element.disabled = value;
    }
  }
});

const handleSubmit = async () => {
  isSubmitting.value = true;
  errorMessage.value = "";

  const formData = new FormData(form.value);

  const axios = Axios.create({
    headers: {
      "X-Requested-With": "XMLHttpRequest",
      "Content-Type": "application/json",
      "X-XSRF-TOKEN": props.csrfToken,
    },
  });

  try {
    submittingStatus.value = "saving";

    await axios.patch(
      props.updateSettingsPath,
      Object.fromEntries(formData.entries())
    );

    submittingStatus.value = "test-sending";

    // 連続してリクエストを送信すると開発環境で不具合が発生することがあるため、0.5s 待つ
    await new Promise((resolve) => setTimeout(resolve, 500));

    await axios.post(props.sendTestPath);

    await new Promise((resolve) => setTimeout(resolve, 500));

    window.location.href = props.nextScreenPath;
  } catch (error) {
    errorMessage.value =
      error.response && error.response.data && error.response.data.message
        ? error.response.data.message
        : "不明なエラーが発生しました。もう一度お試しください。";
  } finally {
    submittingStatus.value = "";
    isSubmitting.value = false;
  }
};
</script>

<template>
  <form
    method="post"
    action="/rehtsjriodf"
    ref="form"
    @submit.prevent="handleSubmit"
  >
    <slot />
    <AppContainer medium>
      <div
        class="pt-spacing-lg"
        v-if="errorMessage !== '' || submittingStatus !== ''"
      >
        <AppInfoBox danger v-if="errorMessage !== ''">{{
          errorMessage
        }}</AppInfoBox>
        <AppInfoBox primary v-if="submittingStatus !== ''">
          <template v-if="submittingStatus === 'saving'">
            メール配信の設定を保存中…
          </template>
          <template v-if="submittingStatus === 'test-sending'">
            テストメールを送信中…
          </template>
        </AppInfoBox>
      </div>
    </AppContainer>
  </form>
</template>
