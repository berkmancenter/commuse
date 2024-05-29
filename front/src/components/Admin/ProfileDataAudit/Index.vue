<template>
  <div class="admin-profile-data-audit">
    <h3 class="is-size-3 has-text-weight-bold mb-4">Profile data audit</h3>

    <div class="mb-4" v-if="!$route.params.id">
      <ActionButton buttonText="Reintake changes" @click="setJustReintake()" :active="justReintake" :icon="reintakeIcon"></ActionButton>
      <ActionButton class="ml-2" buttonText="Waiting for review" @click="setJustReview()" :active="justReview" :icon="reviewIcon"></ActionButton>
    </div>

    <div class="mb-4" v-if="$route.params.id">
      <ActionButton buttonText="Show all audit records" @click="$router.push({ name: 'admin-profile-data-audit.index' })"></ActionButton>
    </div>

    <admin-table :tableClasses="['admin-data-audit-table']">
      <thead>
        <tr class="no-select">
          <th data-sort-method="none" class="no-sort">Changed user</th>
          <th data-sort-method="none" class="no-sort">Changed by</th>
          <th data-sort-method="none" class="no-sort">Changes</th>
          <th data-sort-method="none" class="no-sort">Review</th>
          <th data-sort-method="none" class="no-sort">Date</th>
          <th data-sort-method="none" class="no-sort">Actions</th>
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
          <td width="70%">
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
          <td class="no-break">{{ auditDataItem.review }}</td>
          <td class="no-break">{{ auditDataItem.created_at }}</td>
          <td>
            <div class="admin-table-actions" v-if="auditDataItem.review === 'required'">
              <a title="Accept changes" @click.prevent="acceptAuditRecord(auditDataItem)">
                <Icon :src="acceptIcon" />
              </a>
              <a title="Request further details" @click.prevent="requestChangesAuditRecord(auditDataItem)">
                <Icon :src="denyIcon" />
              </a>
            </div>
          </td>
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
  import reintakeIcon from '@/assets/images/reintake.svg'
  import reviewIcon from '@/assets/images/review.svg'
  import acceptIcon from '@/assets/images/accept.svg'
  import denyIcon from '@/assets/images/deny.svg'
  import AdminTable from '@/components/Admin/AdminTable.vue'
  import VueMultiselect from 'vue-multiselect'
  import ActionButton from '@/components/Shared/ActionButton.vue'

  export default {
    name: 'AdminDataAudit',
    components: {
      Icon,
      AdminTable,
      VueMultiselect,
      ActionButton,
    },
    data() {
      return {
        searchIcon,
        reintakeIcon,
        reviewIcon,
        acceptIcon,
        denyIcon,
        auditData: [],
        justReintake: false,
        justReview: false,
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
          auditData = await this.$store.dispatch('app/fetchProfileDataAuditData', {
            justReintake: this.justReintake,
            justReview: this.justReview,
            auditId: this.$route.params.id ?? false
          })
        } catch (error) {
          this.mitt.emit('spinnerStop')
          return
        }

        this.auditData = auditData

        this.mitt.emit('spinnerStop')
      },
      setJustReintake() {
        this.justReintake = !this.justReintake
        this.loadData()
      },
      setJustReview() {
        this.justReview = !this.justReview
        this.loadData()
      },
      async acceptAuditRecord(auditRecord) {
        this.mitt.emit('spinnerStart')

        let response = ''
        try {
          response = await this.$store.dispatch('app/acceptProfileAuditRecord', {
            id: auditRecord.id,
          })
          response = await response.json()
        } catch (error) {
          this.mitt.emit('spinnerStop')
          return
        }

        this.mitt.emit('spinnerStop')

        this.awn.success(response.message)

        this.loadData()
      },
      async requestChangesAuditRecord(auditRecord) {
        this.mitt.emit('spinnerStart')

        try {
          await this.$store.dispatch('app/requestChangesProfileAuditRecord', {
            id: auditRecord.id,
          })
        } catch (error) {
          this.mitt.emit('spinnerStop')
          return
        }

        this.mitt.emit('spinnerStop')

        this.awn.success('Audit record changes have been requested.')

        this.loadData()
      },
    },
    watch: {
      '$route.params.id': function() {
        this.loadData()
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
