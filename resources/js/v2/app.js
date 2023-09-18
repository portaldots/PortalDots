import Turbolinks from "turbolinks";

import { createApp } from "vue";
import { GlobalEvents } from "vue-global-events";
import FloatingVue from "floating-vue";
import "floating-vue/dist/style.css";

import TurbolinksAdapter from "./vue-turbolinks";

import AppAccordion from "./components/AppAccordion.vue";
import AppFooter from "./components/AppFooter.vue";
import AppHeader from "./components/AppHeader.vue";
import AppInfoBox from "./components/AppInfoBox.vue";
import AppNavBar from "./components/AppNavBar.vue";
import AppNavBarBack from "./components/AppNavBarBack.vue";
import AppNavBarToggle from "./components/AppNavBarToggle.vue";
import AppTabs from "./components/AppTabs.vue";
import CardLink from "./components/CardLink.vue";
import CircleSelectorDropdown from "./components/CircleSelectorDropdown.vue";
import ContentIframe from "./components/ContentIframe.vue";
import AppContainer from "./components/AppContainer.vue";
import AppBadge from "./components/AppBadge.vue";
import AppChip from "./components/AppChip.vue";
import AppChipsContainer from "./components/AppChipsContainer.vue";
import AppDropdown from "./components/AppDropdown.vue";
import AppDropdownItem from "./components/AppDropdownItem.vue";
import AppearanceSettings from "./components/AppearanceSettings.vue";
import AppFixedFormFooter from "./components/AppFixedFormFooter.vue";
import ListView from "./components/ListView.vue";
import ListViewCard from "./components/ListViewCard.vue";
import ListViewItem from "./components/ListViewItem.vue";
import ListViewActionBtn from "./components/ListViewActionBtn.vue";
import ListViewEmpty from "./components/ListViewEmpty.vue";
import ListViewFormGroup from "./components/ListViewFormGroup.vue";
import ListViewPagination from "./components/ListViewPagination.vue";
import ListViewStudentIdAndUnivemailInput from "./components/ListViewStudentIdAndUnivemailInput.vue";
import TopAlert from "./components/TopAlert.vue";
import FormWithConfirm from "./components/FormWithConfirm.vue";
import HomeHeader from "./components/HomeHeader.vue";
import IconButton from "./components/IconButton.vue";
import InstallMailSettingsForm from "./components/InstallMailSettingsForm.vue";
import LayoutRow from "./components/LayoutRow.vue";
import LayoutColumn from "./components/LayoutColumn.vue";
import DataGrid from "./components/DataGrid.vue";
import StepsList from "./components/StepsList.vue";
import StepsListItem from "./components/StepsListItem.vue";
import TagsInput from "./components/TagsInput.vue";
import MarkdownEditor from "./components/MarkdownEditor.vue";
import SearchInput from "./components/SearchInput.vue";
import PermissionsSelector from "./components/PermissionsSelector.vue";
import UiPrimaryColorPicker from "./components/UiPrimaryColorPicker.vue";
import DataGridShortcutLink from "./components/DataGridShortcutLink.vue";

// Form Questions
import QuestionItem from "./components/Forms/QuestionItem.vue";
import QuestionHeading from "./components/Forms/QuestionHeading.vue";

import.meta.glob(["../../img/**"]);

// iOS で CSS の hover を有効にするハック
document.body.addEventListener("touchstart", () => {}, { passive: true });

Turbolinks.start();

// ページ移動時、ボタンやフォームコントロールを無効化する
window.addEventListener("beforeunload", () => {
  const inputs = document.querySelectorAll("input, select, textarea, button");
  /* eslint-disable no-restricted-syntax */
  for (const input of inputs) {
    input.disabled = "disabled";
  }
  /* eslint-enable */
});

if (
  window.navigator &&
  window.navigator.userAgentData &&
  window.navigator.userAgentData.platform &&
  window.navigator.userAgentData.platform.toLowerCase &&
  window.navigator.userAgentData.platform.toLowerCase() === "android"
) {
  document.querySelector("html").classList.add("env-android");
}

document.addEventListener("turbolinks:load", () => {
  const app = createApp({
    components: {
      GlobalEvents,
      AppAccordion,
      AppFooter,
      AppHeader,
      AppInfoBox,
      AppNavBar,
      AppNavBarBack,
      AppNavBarToggle,
      AppTabs,
      CardLink,
      CircleSelectorDropdown,
      ContentIframe,
      AppContainer,
      AppBadge,
      AppChip,
      AppChipsContainer,
      AppDropdown,
      AppDropdownItem,
      AppearanceSettings,
      AppFixedFormFooter,
      ListView,
      ListViewCard,
      ListViewItem,
      ListViewActionBtn,
      ListViewEmpty,
      ListViewFormGroup,
      ListViewPagination,
      ListViewStudentIdAndUnivemailInput,
      TopAlert,
      FormWithConfirm,
      HomeHeader,
      IconButton,
      InstallMailSettingsForm,
      LayoutRow,
      LayoutColumn,
      QuestionItem,
      QuestionHeading,
      DataGrid,
      StepsList,
      StepsListItem,
      TagsInput,
      MarkdownEditor,
      SearchInput,
      PermissionsSelector,
      UiPrimaryColorPicker,
      DataGridShortcutLink,
    },
    data() {
      return {
        isDrawerOpen: false,
      };
    },
    watch: {
      isDrawerOpen(newVal) {
        // アクセシビリティのため、適切な位置にフォーカスする
        if (newVal) {
          this.$refs.drawer.focus();
        } else {
          this.$refs.toggle.$el.focus();
        }
      },
    },
    mounted() {
      const loading = document.querySelector("#loading");
      loading.classList.add("is-done");
    },
    // unmounted() {
    //   const loading = document.querySelector('#loading')
    //   loading.classList.remove('is-done')
    // },
    methods: {
      toggleDrawer() {
        this.isDrawerOpen = !this.isDrawerOpen;
      },
      closeDrawer() {
        this.isDrawerOpen = false;
      },
      share(shareData) {
        if (navigator.share) {
          navigator.share(shareData);
        } else {
          window.alert("お使いのブラウザでは共有機能に対応していません");
        }
      },
    },
  });

  app.use(TurbolinksAdapter);
  app.use(FloatingVue, { delay: 400 });

  app.mount("#v2-app");
});
