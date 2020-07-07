<template>
  <AppContainer class="footer">
    <div class="footer__credit">
      <template v-if="$slots.default"> <slot /> • </template>
      Powered by
      <a
        href="https://github.com/portal-dots/PortalDots"
        class="footer__link"
        target="_blank"
        rel="noopener"
      >
        PortalDots
      </a>
    </div>
  </AppContainer>
</template>

<script>
import AppContainer from './AppContainer.vue'

export default {
  components: {
    AppContainer
  }
}
</script>

<style lang="scss" scoped>
.container {
  flex: none;
}

.footer {
  border-top: 1px solid $color-border;
  margin-top: $spacing-md;
  text-align: center;
  &__credit {
    color: $color-muted-2;
    margin-top: $spacing-lg;

    @media screen and (max-width: $breakpoint-bottom-tabs) {
      padding-bottom: $spacing-lg;
    }
  }
  &__link {
    color: $color-muted-2;
    font-weight: 600;
    transition: linear 0.1s;
    &:hover,
    &:focus {
      color: darken($color-muted-2, 10%);
      text-decoration: none;
    }
  }
}
</style>
