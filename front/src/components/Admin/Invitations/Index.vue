<template>
  <div class="admin-invitations">
    <h3 class="is-size-3 has-text-weight-bold mb-4">Invitations</h3>

    <div class="mb-4">
      <ActionButton buttonText="Create invitation" @click="createInvitationModalOpen()" :icon="addIcon"></ActionButton>
    </div>

    <form class="form">
      <admin-table :tableClasses="['admin-invitations-table']">
        <thead>
          <tr class="no-select">
            <th>Code</th>
            <th>Valid</th>
            <th>Type</th>
            <th>Expire</th>
            <th>Created</th>
            <th data-sort-method="none" class="no-sort">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="invitation in invitations" :key="invitation.id" class="no-break">
            <td class="admin-invitations-table-code"><a class="button is-light" title="Click to copy invitation url" @click="copyCodeUrlToClipboard(invitation.code)">{{ invitation.code }} <Icon :src="clipboardIcon" /></a></td>
            <td class="admin-invitations-table-used"><Booler :value="isValid(invitation)" /></td>
            <td class="no-break admin-invitations-table-type">{{ invitation.type }}</td>
            <td>{{ formattedTimestamp(invitation.expire) }}</td>
            <td>{{ formattedTimestamp(invitation.created_at) }}</td>
            <td class="admin-table-actions">
              <a title="Delete invitation" @click.prevent="deleteInvitationConfirm(invitation)">
                <Icon :src="minusIcon" />
              </a>
            </td>
          </tr>
          <tr v-if="invitations.length === 0">
            <td colspan="4">No invitations found.</td>
          </tr>
        </tbody>
      </admin-table>
    </form>
  </div>

  <Modal
    v-model="deleteInvitationModalStatus"
    title="Delete invitation"
    @confirm="deleteInvitation()"
    @cancel="deleteInvitationModalStatus = false"
  >
    Are you sure you delete the invitation?
  </Modal>

  <Modal
    v-model="createInvitationModalStatus"
    title="Create invitation"
    :focusOnConfirm="false"
    @confirm="createInvitation()"
    @cancel="createInvitationModalStatus = false"
  >
    <form class="form admin-invitations-form">
      <div class="field">
        <label class="label" for="type">Type</label>
        <div class="control">
          <div class="select">
            <select v-model="createInvitationCurrent.type">
              <option value="single">Single</option>
              <option value="multiple">Multiple</option>
            </select>
          </div>
        </div>
      </div>

      <div class="field">
        <label class="label" for="expire">Expire</label>
        <div class="control">
          <date-picker v-model:value="createInvitationCurrentExpire" format="MMMM D, Y" type="date" value-type="format" input-class="input" :clearable="false"></date-picker>
        </div>
      </div>
    </form>
  </Modal>
</template>

<script>
  import Icon from '@/components/Shared/Icon.vue'
  import Booler from '@/components/Shared/Booler.vue'
  import minusIcon from '@/assets/images/minus.svg'
  import clipboardIcon from '@/assets/images/clipboard.svg'
  import addIcon from '@/assets/images/add.svg'
  import AdminTable from '@/components/Admin/AdminTable.vue'
  import { formattedTimestamp } from '@/lib/time_stuff'
  import ActionButton from '@/components/Shared/ActionButton.vue'
  import Modal from '@/components/Shared/Modal.vue'

  export default {
    name: 'AdminInvitations',
    components: {
      Icon,
      AdminTable,
      Booler,
      ActionButton,
      Modal,
    },
    data() {
      return {
        minusIcon,
        addIcon,
        clipboardIcon,
        invitations: [],
        apiUrl: import.meta.env.VITE_API_URL,
        formattedTimestamp: formattedTimestamp,
        deleteInvitationModalStatus: false,
        deleteInvitationCurrent: null,
        createInvitationModalStatus: false,
        createInvitationCurrent: {},
        createInvitationCurrent: {
          type: 'single',
          expire: '',
        },
        createInvitationCurrentExpire: '',
      }
    },
    created() {
      this.initialDataLoad()
    },
    methods: {
      initialDataLoad() {
        this.loadInvitations()
      },
      async loadInvitations() {
        this.mitt.emit('spinnerStart')

        const invitations = await this.$store.dispatch('app/fetchInvitations')

        this.invitations = invitations

        this.mitt.emit('spinnerStop')
      },
      async createInvitationModalOpen() {
        this.createInvitationModalStatus = true
      },
      async createInvitation() {
        this.mitt.emit('spinnerStart')

        const response = await this.$store.dispatch('app/saveInvitation', {
          type: this.createInvitationCurrent.type,
          expire: this.createInvitationCurrentExpire,
        })

        if (response.ok) {
          this.awn.success('Invitation has been created.')
          this.loadInvitations()
        } else {
          this.awn.warning('Something went wrong, try again.')
        }

        this.createInvitationModalStatus = false
        this.mitt.emit('spinnerStop')
      },
      toggleAll() {
        const newStatus = this.$refs.toggleAllCheckbox.checked

        this.invitations
          .map((invitation) => {
            invitation.selected = newStatus

            return invitation
          })
      },
      copyCodeUrlToClipboard(code) {
        window.navigator.clipboard.writeText(`${this.apiUrl}/register?ic=${code}`)
        this.awn.success('Invitation link has been copied to the clipboard.')
      },
      deleteInvitationConfirm(invitation) {
        this.deleteInvitationModalStatus = true
        this.deleteInvitationCurrent = invitation
      },
      async deleteInvitation() {
        this.mitt.emit('spinnerStart')

        const response = await this.$store.dispatch('app/deleteInvitations', [this.deleteInvitationCurrent.id])
        const data = await response.json()

        if (response.ok) {
          this.awn.success(data.message)
          this.loadInvitations()
        } else {
          this.awn.warning(data.message)
        }

        this.deleteInvitationModalStatus = false
        this.mitt.emit('spinnerStop')
      },
      isValid(invitation) {
        if (invitation.expire && invitation.expire < Date.now()) {
          return false
        }

        if (invitation.type === 'single' && invitation.used === 't') {
          return false
        }

        return true
      },
    },
  }
</script>

<style lang="scss">
  .admin-invitations-table-code {
    width: 24rem;

    img {
      padding: 0;
      margin-left: 0.3rem;
    }
  }

  .admin-invitations-table-type {
    width: 5rem;
  }

  .admin-invitations-table-used {
    width: 2rem;
  }
</style>
