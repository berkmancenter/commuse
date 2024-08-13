<template>
  <div class="admin-profile-data-audit">
    <h3 class="is-size-3 has-text-weight-bold mb-2">Profile data audit</h3>

    <div class="mb-4" v-if="!$route.params.id">
      <ActionButton class="mt-2" buttonText="Reintake changes" @click="setJustReintake()" :active="justReintake" :icon="reintakeIcon"></ActionButton>
      <ActionButton class="mt-2 ml-2" buttonText="Waiting for review" @click="setJustReview()" :active="justReview" :icon="reviewIcon"></ActionButton>
      <ActionButton class="mt-2 ml-2" buttonText="Filter by field" @click="openFilterChangesModal()" :active="activeFilterChangesSelected.length > 0" :icon="filterIcon"></ActionButton>
    </div>

    <div class="admin-profile-data-audit-search mt-2 mb-4">
      <input
        type="text"
        v-model="searchQuery"
        placeholder="Search"
        class="input"
      >
      <span><img :src="searchIcon"></span>
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
            <changes-list :auditDataItem="auditDataItem"></changes-list>
          </td>
          <td class="no-break">{{ auditDataItem.review }}</td>
          <td class="no-break">{{ auditDataItem.created_at }}</td>
          <td>
            <div class="admin-table-actions" v-if="auditDataItem.review === 'required'">
              <a title="Process changes" @click.prevent="processAuditRecordModalOpen(auditDataItem)">
                <Icon :src="processIcon" />
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

  <vue-awesome-paginate
    :total-items="paginateTotalItems"
    :items-per-page="20"
    :max-pages-shown="5"
    :hidePrevNextWhenEnds="true"
    v-model="page"
    @click="paginateChangePage"
    class="mt-4"
    v-if="paginateTotalItems > 0"
  ></vue-awesome-paginate>

  <Modal
    v-model="processAuditRecordModalStatus"
    title="Process user data changes"
    :focusOnConfirm="false"
    :showConfirmButton="false"
    class="admin-users-process-audit-record-modal"
    @cancel="processAuditRecordModalStatus = false"
  >
    <div>
      You are reviewing changes of
      <span class="has-text-weight-bold">{{ processedPersonName }}</span>.

      <changes-list :auditDataItem="processAuditRecordModalCurrent"></changes-list>
    </div>

    <hr>

    <div class="mb-2 has-text-weight-bold">1. Select how to process the changes:</div>

    <div class="field">
      <div class="control">
        <div class="select">
          <select v-model="processAuditRecordModalSelect" @change="setProcessAuditRecordModalEmailTemplates()">
            <option value="accept">Accept</option>
            <option value="deny">Deny</option>
          </select>
        </div>
      </div>
    </div>

    <hr>

    <div class="mb-2 has-text-weight-bold">2. Customize user confirmation email and subject (if needed).</div>

    <div class="field">
      <div class="mb-2">Email subject</div>

      <div class="control">
        <input type="text" class="input" v-model="processAuditRecordModalEmailValues.subject" />
      </div>
    </div>

    <div class="field">
      <div class="mb-2">Email body</div>

      <div class="control">
        <ckeditor :editor="editor" v-model="processAuditRecordModalEmailValues.body" :config="editorConfig"></ckeditor>
      </div>
    </div>

    <div class="mb-2 has-text-weight-bold">3. Select an action.</div>

    <button class="button is-success" @click="processAuditRecord('process_send_email')">
      Accept/deny & send email
    </button>

    <button class="button ml-2 is-success" @click="processAuditRecord('process_quietly')">
      Accept/deny quietly
    </button>
  </Modal>

  <Modal
    v-model="changesFieldsModalStatus"
    title="Set fields to filter data changes"
    class="admin-users-filter-changes-modal"
    @confirm="changesFieldsModalStatusConfirm()"
    @cancel="changesFieldsModalStatus = false"
  >
    <VueMultiselect
      v-model="activeFilterChanges"
      :multiple="true"
      :taggable="false"
      :options="changesFields"
    >
    </VueMultiselect>
  </Modal>
</template>

<script>
  import Icon from '@/components/Shared/Icon.vue'
  import searchIcon from '@/assets/images/search.svg'
  import reintakeIcon from '@/assets/images/reintake.svg'
  import reviewIcon from '@/assets/images/review.svg'
  import processIcon from '@/assets/images/process.svg'
  import filterIcon from '@/assets/images/filter.svg'
  import AdminTable from '@/components/Admin/AdminTable.vue'
  import VueMultiselect from 'vue-multiselect'
  import ActionButton from '@/components/Shared/ActionButton.vue'
  import Modal from '@/components/Shared/Modal.vue'
  import ChangesList from './ChangesList.vue'
  import { ClassicEditor, Bold, Essentials, Italic, Paragraph, Undo, Link } from 'ckeditor5'
  import { orderBy, identity } from 'lodash'

  export default {
    name: 'AdminDataAudit',
    components: {
      Icon,
      AdminTable,
      VueMultiselect,
      ActionButton,
      Modal,
      ChangesList,
    },
    data() {
      return {
        componentRouteName: 'admin-profile-data-audit.index',
        searchIcon,
        reintakeIcon,
        reviewIcon,
        processIcon,
        filterIcon,
        auditData: [],
        editor: ClassicEditor,
        editorConfig: {
          plugins: [ Bold, Essentials, Italic, Paragraph, Undo, Link ],
          toolbar: [ 'undo', 'redo', '|', 'bold', 'italic', '|', 'link' ],
        },
        processAuditRecordModalStatus: false,
        processAuditRecordModalCurrent: null,
        processAuditRecordModalSelect: 'accept',
        processAuditRecordModalEmailSubject: '',
        processAuditRecordModalEmailBody: '',
        processAuditRecordModalEmailValues: {},
        paginateTotalItems: 0,
        changesFields: [],
        activeFilterChanges: [],
        changesFieldsModalStatus: false,
      }
    },
    created() {
      this.loadData(false)
      this.loadChangesFields()
    },
    computed: {
      processedPersonName() {
        let name = []

        if (this.processAuditRecordModalCurrent.audited_first_name) {
          name.push(this.processAuditRecordModalCurrent.audited_first_name)
        }

        if (this.processAuditRecordModalCurrent.audited_last_name) {
          name.push(this.processAuditRecordModalCurrent.audited_last_name)
        }

        return name.join(' ')
      },
    },
    methods: {
      async loadData(goToPageOne = true) {
        this.mitt.emit('spinnerStart')

        this.loadQueryParams()

        if (goToPageOne) {
          this.page = 1
          this.$router.replace({ name: this.componentRouteName, query: { ...this.$route.query, page: 1 }})
        }

        let auditData = null
        try {
          auditData = await this.$store.dispatch('admin/fetchProfileDataAuditData', {
            justReintake: this.justReintake,
            justReview: this.justReview,
            query: this.searchQuery,
            paginateCurrentPage: this.page,
            fields: this.activeFilterChangesSelected,
          })
        } catch (error) {
          this.mitt.emit('spinnerStop')
          return
        }

        this.auditData = auditData.items
        this.paginateTotalItems = auditData.metadata.total

        if (this.$route.params.id) {
          let item = this.auditData.filter(auditDataItem => auditDataItem.id === this.$route.params.id)[0]
          if (item.review === 'required') {
            this.processAuditRecordModalOpen(item)
          }
        }

        this.mitt.emit('spinnerStop')
      },
      async loadChangesFields() {
        this.mitt.emit('spinnerStart')

        let changesData = null
        try {
          changesData = await this.$store.dispatch('admin/fetchProfileDataAuditChangesFields')
        } catch (error) {
          this.mitt.emit('spinnerStop')
          return
        }

        this.changesFields = orderBy(changesData, [identity], ['asc'])

        this.mitt.emit('spinnerStop')
      },
      setJustReintake() {
        this.justReintake = !this.justReintake
        this.$router.push({ name: this.componentRouteName, query: { ...this.$route.query, justReintake: this.justReintake }})
      },
      setJustReview() {
        this.justReview = !this.justReview
        this.$router.push({ name: this.componentRouteName, query: { ...this.$route.query, justReview: this.justReview }})
      },
      async processAuditRecordModalOpen(auditRecord) {
        this.processAuditRecordModalCurrent = auditRecord
        this.processAuditRecordModalStatus = true
        this.setProcessAuditRecordModalEmailTemplates()
      },
      setProcessAuditRecordModalEmailTemplates() {
        if (this.processAuditRecordModalSelect === 'accept') {
          this.processAuditRecordModalEmailValues = {
            subject: this.$store.state.systemSettings.publicSystemSettings['DataAuditUserEmailAcceptedSubject'].value,
            body: this.$store.state.systemSettings.publicSystemSettings['DataAuditUserEmailAcceptedBody'].value,
          }
        } else {
          this.processAuditRecordModalEmailValues = {
            subject: this.$store.state.systemSettings.publicSystemSettings['DataAuditUserEmailDeclinedSubject'].value,
            body: this.$store.state.systemSettings.publicSystemSettings['DataAuditUserEmailDeclinedBody'].value,
          }
        }
      },
      async processAuditRecord(action) {
        this.mitt.emit('spinnerStart')

        let response = ''
        try {
          response = await this.$store.dispatch('admin/processProfileAuditRecord', {
            id: this.processAuditRecordModalCurrent.id,
            emailTemplate: this.processAuditRecordModalEmailValues,
            decision: this.processAuditRecordModalSelect,
            action,
          })
          response = await response.json()
        } catch (error) {
          this.mitt.emit('spinnerStop')
          return
        }

        this.mitt.emit('spinnerStop')

        this.awn.success(response.message)

        this.loadData()

        this.processAuditRecordModalStatus = false
      },
      paginateChangePage(page) {
        this.page = page
        this.$router.push({ name: this.componentRouteName, query: { ...this.$route.query, page: page }})
      },
      openFilterChangesModal() {
        this.activeFilterChanges = this.activeFilterChangesSelected
        this.changesFieldsModalStatus = true
      },
      async changesFieldsModalStatusConfirm() {
        this.activeFilterChangesSelected = this.activeFilterChanges
        await this.$router.push({ name: this.componentRouteName, query: { ...this.$route.query, fields: this.activeFilterChanges.join(',') }})
        this.changesFieldsModalStatus = false
      },
      loadQueryParams() {
        this.justReintake = this.$route.query.justReintake === 'true'
        this.justReview = this.$route.query.justReview === 'true'
        this.searchQuery = this.$route.query.searchQuery || ''
        this.page = parseInt(this.$route.query.page) || 1
        this.activeFilterChangesSelected = this.$route.query.fields ? this.$route.query.fields.split(',') : []
      },
    },
    watch: {
      '$route.params.id': function() {
        this.loadData()
      },
      '$route.query.searchQuery'() {
        this.loadData()
      },
      '$route.query.page'() {
        this.loadData(false)
      },
      '$route.query.justReintake'() {
        this.loadData()
      },
      '$route.query.justReview'() {
        this.loadData()
      },
      '$route.query.fields'() {
        this.loadData()
      },
      searchQuery(newVal) {
        this.$router.push({ name: this.componentRouteName, query: { ...this.$route.query, searchQuery: newVal }})
      },
    },
  }
</script>

<style lang="scss">
  .admin-profile-data-audit {
    .admin-profile-data-audit-search {
      position: relative;
      max-width: 300px;
      min-width: 200px;

      span {
        display: block;
        width: 1.5rem;
        position: absolute;
        top: 2px;
        bottom: 2px;
        right: 0.5rem;
        display: flex;
        pointer-events: none;
        background-color: #ffffff;
      }

      input {
        border-bottom: 2px solid var(--main-color);
        border-radius: 0;
      }

      input::placeholder {
        color: rgba(54, 54, 54, 0.8);
      }
    }

    tr {
      white-space: unset;

      th:hover {
        background-color: #fff;
      }
    }
  }

  .admin-users-filter-changes-modal {
    .multiselect__content-wrapper {
      position: static;
    }
  }
</style>
