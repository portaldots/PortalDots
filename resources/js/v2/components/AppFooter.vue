<template>
  <div class="footer">
    <div class="footer-line">
      <template v-if="$slots.default"> <slot /> • </template>
      Powered by
      <a
        href="https://www.portaldots.com"
        class="footer__link"
        target="_blank"
        rel="noopener noreferrer"
        >PortalDots</a
      >
    </div>
    <div class="footer-line">
      <a href="/support" class="footer__link">推奨動作環境</a>
    </div>
  </div>
</template>

<script>
export default {};
</script>

<style lang="scss" scoped>
.footer {
  color: $color-muted-2;
  font-size: 0.9rem;
  margin-top: $spacing-md;
  padding: $spacing $spacing-lg;
  text-align: center;
  &-line {
    margin: 0 0 $spacing-xs;
    &:last-child {
      margin: 0;
    }
  }
  &__link {
    color: $color-muted-2;
    font-weight: $font-bold;
    transition: linear 0.1s;
    &:hover,
    &:focus {
      text-decoration: underline;
    }
  }
}
</style>
