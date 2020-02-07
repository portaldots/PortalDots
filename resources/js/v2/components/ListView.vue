<template>
  <AppContainer
    class="listview"
    :narrow="containerNarrow"
    :medium="containerMedium"
  >
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
  </AppContainer>
</template>

<script>
import AppContainer from './AppContainer.vue'

export default {
  components: {
    AppContainer
  },
  props: {
    headerTitle: {
      type: String,
      default: null
    },
    headerDescription: {
      type: String,
      default: null
    },
    containerNarrow: {
      type: Boolean,
      default: false
    },
    containerMedium: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.listview {
  padding: $spacing $spacing $spacing-md;
  @media screen and (max-width: $breakpoint-listview-sm) {
    padding: 0;
  }
  &-header {
    padding: 0 $spacing $spacing-md;
    width: 100%;
    @media screen and (max-width: $breakpoint-listview-sm) {
      padding: $spacing $spacing $spacing-xs;
    }
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
