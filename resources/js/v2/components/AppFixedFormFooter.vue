<template>
  <div class="fixed_form_footer">
    <slot />
  </div>
</template>

<script>
export default {};
</script>

<style lang="scss">
.fixed_form_footer {
  align-items: center;
  /* backdrop-filter: blur(10px); */
  bottom: 0;
  display: flex;
  justify-content: center;
  margin: $spacing-lg 0 0;
  padding: $spacing;
  position: sticky;
  z-index: $z-index-fixed-form-footer;
  background: linear-gradient(to bottom, transparent, $color-bg-base 80%);
}
</style>
