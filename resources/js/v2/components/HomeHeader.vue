<template>
  <header class="home-header">
    <AppContainer>
      <LayoutRow grid-template-columns="1fr 0.4fr">
        <div class="home-header-content">
          <h1 class="home-header-content__title"><slot name="title" /></h1>
          <p class="home-header-content__description">
            <slot name="description" />
          </p>
          <p class="home-header-content__admin"><slot name="admin" /></p>
        </div>
        <div class="home-header-buttons">
          <a :href="loginUrl" class="btn is-primary is-block"> ログイン </a>
          <a :href="registerUrl" class="btn is-secondary is-block">
            ユーザー登録
          </a>
        </div>
      </LayoutRow>
    </AppContainer>
  </header>
</template>

<script setup>
import AppContainer from "./AppContainer.vue";
import LayoutRow from "./LayoutRow.vue";

const props = defineProps({
  loginUrl: {
    type: String,
    required: true,
  },
  registerUrl: {
    type: String,
    required: true,
  },
});
</script>

<style lang="scss" scoped>
.home-header {
  background: $color-bg-surface;
  border-bottom: 1px solid $color-border;
  padding: $spacing-lg 0;
  &-content {
    display: flex;
    flex-direction: column;
    gap: $spacing-sm;
    &__title {
      font-size: 2rem;
      line-height: 1.4;
      font-weight: $font-bold;
      margin: 0;
    }
    &__description {
      margin: 0;
    }
    &__admin {
      color: $color-muted;
      font-size: 0.9rem;
      margin: 0;
    }
  }

  &-buttons {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: $spacing-md;
  }
}
</style>
