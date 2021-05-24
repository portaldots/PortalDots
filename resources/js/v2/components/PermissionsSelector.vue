<template>
  <div>
    <ListViewCard noBorder>
      <div class="pb-spacing-md">
        <template v-for="badge in permissionBadges">
          <AppBadge primary :key="badge">
            {{ badge }}
          </AppBadge>
          {{ ' ' }}
        </template>
        <strong
          class="text-danger"
          v-if="!permissions || permissions.length === 0"
        >
          利用可能な機能なし
        </strong>
      </div>
      <div class="pb-spacing-md">
        <SearchInput
          v-model="searchKeyword"
          placeholder="権限を検索…"
          preventEnter
        />
      </div>
    </ListViewCard>
    <ListViewBaseItem class="selector">
      <template v-if="inputName">
        <input
          type="hidden"
          :name="`${inputName}[]`"
          v-for="permission in permissions"
          :value="permission"
          :key="permission"
        />
      </template>
      <div class="selector-inner">
        <table class="selector-table">
          <thead class="selector-table__head">
            <tr class="selector-table__head__row">
              <th class="selector-table__head__cell"></th>
              <th class="selector-table__head__cell">権限名</th>
              <th class="selector-table__head__cell">説明</th>
            </tr>
          </thead>
          <tbody class="selector-table__body">
            <tr
              v-for="definedPermission in filteredPermissions"
              :key="definedPermission.identifier"
              class="selector-table__body__row"
            >
              <td class="selector-table__body__cell">
                <input
                  type="checkbox"
                  :id="`permission_${definedPermission.identifier}`"
                  :value="definedPermission.identifier"
                  v-model="permissions"
                />
              </td>
              <td class="selector-table__body__cell">
                <label
                  class="selector-table__label"
                  :for="`permission_${definedPermission.identifier}`"
                >
                  <strong class="selector-table__display-name">
                    {{ definedPermission.display_name }}
                  </strong>
                  <span class="selector-table__sub-name">
                    識別名 : {{ definedPermission.identifier }} 、 短縮名 :
                    {{ definedPermission.display_short_name }}
                  </span>
                </label>
              </td>
              <td class="selector-table__body__cell">
                <label
                  class="selector-table__label"
                  :for="`permission_${definedPermission.identifier}`"
                >
                  {{ definedPermission.description_html }}
                </label>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </ListViewBaseItem>
  </div>
</template>

<script>
import AppBadge from './AppBadge.vue'
import ListViewBaseItem from './ListViewBaseItem.vue'
import ListViewCard from './ListViewCard.vue'
import SearchInput from './SearchInput.vue'

export default {
  components: {
    ListViewCard,
    ListViewBaseItem,
    SearchInput,
    AppBadge
  },
  props: {
    inputName: {
      type: String,
      default: null
    },
    definedPermissions: {
      type: Object,
      required: true
    },
    defaultPermissions: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      permissions: [],
      searchKeyword: ''
    }
  },
  mounted() {
    this.permissions = this.defaultPermissions
  },
  computed: {
    permissionBadges() {
      return Object.values(this.definedPermissions)
        .filter((permission) =>
          this.permissions.includes(permission.identifier)
        )
        .map((permission) => permission.display_short_name)
    },
    filteredPermissions() {
      const keyword = this.searchKeyword.trim()
      return Object.values(this.definedPermissions).filter(
        (permission) =>
          permission.identifier.includes(keyword) ||
          permission.display_name.includes(keyword) ||
          permission.display_short_name.includes(keyword)
      )
    }
  }
}
</script>

<style lang="scss" scoped>
.selector {
  $scrollbar-width: 16px;

  padding: 0;
  &-table {
    display: block;
    width: 100%;
    &__head {
      border-bottom: 2px solid $color-border;
      display: block;
      padding-right: $scrollbar-width;
      &__row {
        display: grid;
        grid-template-columns: 3rem 1fr 1fr;
      }
      &__cell {
        display: block;
        padding: $spacing-sm $spacing-md;
        padding-left: 0;
        text-align: left;
        &:first-child {
          padding: $spacing-md;
        }
      }
    }
    &__body {
      display: block;
      height: 60vh;
      overflow: auto;
      &::-webkit-scrollbar {
        width: $scrollbar-width;
      }
      &::-webkit-scrollbar-thumb {
        background-color: $color-muted-3;
        border: 4px solid $color-behind-text;
        border-radius: 9999px;
        width: $scrollbar-width;
        &:hover {
          background-color: $color-muted-2;
        }
      }
      &__row {
        display: grid;
        grid-template-columns: 3rem 1fr 1fr;
        &:hover {
          background: $color-bg-light;
        }
      }
      &__cell {
        border-bottom: 1px solid $color-border;
        display: block;
        height: 100%;
        &:first-child {
          border: none;
          padding: $spacing-md;
        }
      }
    }
    &__display-name {
      display: block;
      font-weight: bold;
      margin-bottom: $spacing-xs;
    }
    &__sub-name {
      color: $color-muted;
      display: block;
      font-size: 80%;
    }
    &__label {
      display: block;
      height: 100%;
      padding: $spacing-md;
      padding-left: 0;
    }
  }
}
</style>
