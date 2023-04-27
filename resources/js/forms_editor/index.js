import "bootstrap";
import { createApp } from "vue";
import EditorApp from "./EditorApp.vue";
import store from "./store";

const app = createApp(EditorApp);
app.use(store);
app.mount("#forms-editor-container");
