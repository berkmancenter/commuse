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
</template>

<script>
  import Icon from '@/components/Shared/Icon.vue'
  import searchIcon from '@/assets/images/search.svg'
  import reintakeIcon from '@/assets/images/reintake.svg'
  import reviewIcon from '@/assets/images/review.svg'
  import processIcon from '@/assets/images/process.svg'
  import AdminTable from '@/components/Admin/AdminTable.vue'
  import VueMultiselect from 'vue-multiselect'
  import ActionButton from '@/components/Shared/ActionButton.vue'
  import Modal from '@/components/Shared/Modal.vue'
  import ChangesList from './ChangesList.vue'
  import { ClassicEditor, Bold, Essentials, Italic, Paragraph, Undo, Link } from 'ckeditor5'

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
        searchIcon,
        reintakeIcon,
        reviewIcon,
        processIcon,
        auditData: [],
        justReintake: false,
        justReview: false,
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
        processAuditRecordModalEmailTemplates: {},
        processAuditRecordModalEmailValues: {},
      }
    },
    created() {
      this.loadData()
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
      async loadData() {
        this.mitt.emit('spinnerStart')

        let processAuditRecordModalEmailTemplates = null
        try {
          processAuditRecordModalEmailTemplates = await this.$store.dispatch('app/fetchDataAuditEmailTemplates')
        } catch (error) {
          this.mitt.emit('spinnerStop')
          return
        }
        this.processAuditRecordModalEmailTemplates = processAuditRecordModalEmailTemplates

        let auditData = null
        try {
          auditData = await this.$store.dispatch('app/fetchProfileDataAuditData', {
            justReintake: this.justReintake,
            justReview: this.justReview,
          })
        } catch (error) {
          this.mitt.emit('spinnerStop')
          return
        }
        this.auditData = auditData

        if (this.$route.params.id) {
          let item = this.auditData.filter(auditDataItem => auditDataItem.id === this.$route.params.id)[0]
          if (item.review === 'required') {
            this.processAuditRecordModalOpen(item)
          }
        }

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
      async processAuditRecordModalOpen(auditRecord) {
        this.processAuditRecordModalCurrent = auditRecord
        this.processAuditRecordModalStatus = true
        this.setProcessAuditRecordModalEmailTemplates()
      },
      setProcessAuditRecordModalEmailTemplates() {
        if (this.processAuditRecordModalSelect === 'accept') {
          this.processAuditRecordModalEmailValues = {
            subject: this.processAuditRecordModalEmailTemplates['DataAuditUserEmailAcceptedSubject'].value,
            body: this.processAuditRecordModalEmailTemplates['DataAuditUserEmailAcceptedBody'].value,
          }
        } else {
          this.processAuditRecordModalEmailValues = {
            subject: this.processAuditRecordModalEmailTemplates['DataAuditUserEmailDeclinedSubject'].value,
            body: this.processAuditRecordModalEmailTemplates['DataAuditUserEmailDeclinedBody'].value,
          }
        }
      },
      async processAuditRecord(action) {
        this.mitt.emit('spinnerStart')

        let response = ''
        try {
          response = await this.$store.dispatch('app/processProfileAuditRecord', {
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

      th:hover {
        background-color: #fff;
      }
    }
  }
</style>
