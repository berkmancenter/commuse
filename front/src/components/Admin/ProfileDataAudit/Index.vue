<template>
  <div class="admin-profile-data-audit">
    <h3 class="is-size-3 has-text-weight-bold mb-4">Profile data audit</h3>

    <admin-table :tableClasses="['admin-data-audit-table']">
      <thead>
        <tr class="no-select">
          <th data-sort-method="none" class="no-sort">Changed user</th>
          <th data-sort-method="none" class="no-sort">Changed by</th>
          <th data-sort-method="none" class="no-sort">Changes</th>
          <th data-sort-method="none" class="no-sort">Date</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="auditDataItem in auditData" :key="auditData.id" class="no-break">
          <td class="no-break">
            <div>{{ auditDataItem.audited_first_name }}</div>
            <div>{{ auditDataItem.audited_last_name }}</div>
          </td>
          <td class="no-break">
            <div>{{ auditDataItem.changed_by_first_name }}</div>
            <div>{{ auditDataItem.changed_by_last_name }}</div>
          </td>
          <td>
            <table>
              <tr><th colspan="2">New/changed</th></tr>
              <tr v-for="(changeNew, key) in auditDataItem.changes.new">
                <td class="no-break">{{ key }}</td>
                <td>{{ changeNew }}</td>
              </tr>
            </table>

            <table>
              <tr><th colspan="2">Old/removed</th></tr>
              <tr v-for="(changeNew, key) in auditDataItem.changes.old">
                <td class="no-break">{{ key }}</td>
                <td>{{ changeNew }}</td>
              </tr>
            </table>
          </td>
          <td class="no-break">{{ auditDataItem.created_at }}</td>
        </tr>
        <tr v-if="auditData.length === 0">
          <td colspan="4">No data to show.</td>
        </tr>
      </tbody>
    </admin-table>
  </div>
</template>

<script>
  import Icon from '@/components/Shared/Icon.vue'
  import searchIcon from '@/assets/images/search.svg'
  import AdminTable from '@/components/Admin/AdminTable.vue'
  import VueMultiselect from 'vue-multiselect'

  export default {
    name: 'AdminDataAudit',
    components: {
      Icon,
      AdminTable,
      VueMultiselect,
    },
    data() {
      return {
        searchIcon,
        auditData: [],
      }
    },
    created() {
      this.loadData()
    },
    methods: {
      async loadData() {
        this.mitt.emit('spinnerStart')

        let auditData = null
        try {
          auditData = await this.$store.dispatch('app/fetchProfileDataAuditData')
        } catch (error) {
          this.mitt.emit('spinnerStop')
          return
        }

        this.auditData = auditData

        this.mitt.emit('spinnerStop')
      },
    },
  }
</script>

<style lang="scss">
  .admin-profile-data-audit {
    tr {
      white-space: unset;
    }
  }
</style>
