<template>
  <div class="listview">
    <div class="listview-header" v-if="headerTitle || headerDescription">
      <h2 class="listview-header__title" v-if="headerTitle">
        {{ headerTitle }}
      </h2>
      <div class="listview-header__description" v-if="headerDescription">
        {{ headerDescription }}
      </div>
    </div>
    <div class="listview-body">
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    headerTitle: {
      type: String,
      default: null
    },
    headerDescription: {
      type: String,
      default: null
    }
  }
}
</script>

<style lang="scss" scoped>
.listview {
  margin-left: -$container-padding-x;
  margin-right: -$container-padding-x;
  padding: $spacing 0 $spacing-md;
  @media screen and (max-width: $breakpoint-listview-sm) {
    padding: $spacing-md 0;
  }
  &-header {
    padding: 0 $spacing $spacing-md;
    width: 100%;
    &__title {
      font-size: $font-size-lg;
      font-weight: bold;
      margin: 0;
    }
    &__description {
      color: $color-muted;
      margin: $spacing-xs 0 0;
    }
  }
  &-body {
    @media screen and (max-width: $breakpoint-listview-sm) {
      border-bottom: $listview-border;
      border-top: $listview-border;
    }
  }
}
</style>
