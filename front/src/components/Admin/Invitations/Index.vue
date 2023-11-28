<template>
  <div class="admin-invitations">
    <h3 class="is-size-3 has-text-weight-bold mb-4">Invitations</h3>

    <div class="mb-4">
      <ActionButton buttonText="Create invitation" :onClick="openNewInvitationModal" :icon="addIcon"></ActionButton>
    </div>

    <form class="form">
      <admin-table :tableClasses="['admin-invitations-table']">
        <thead>
          <tr class="no-select">
            <th data-sort-method="none" class="no-sort">
              <input type="checkbox" ref="toggleAllCheckbox" @click="toggleAll()">
            </th>
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
            <td class="admin-table-selector">
              <input type="checkbox" v-model="invitation.selected">
            </td>
            <td class="admin-invitations-table-code"><a class="button is-light" title="Click to copy invitation url" @click="copyCodeUrlToClipboard(invitation.code)">{{ invitation.code }} <Icon :src="clipboardIcon" /></a></td>
            <td class="admin-invitations-table-used"><Booler :value="isValid(invitation)" /></td>
            <td class="no-break admin-invitations-table-type">{{ invitation.type }}</td>
            <td>{{ formattedTimestamp(invitation.expire) }}</td>
            <td>{{ formattedTimestamp(invitation.created_at) }}</td>
            <td class="admin-table-actions">
              <a title="Delete invitation" @click.prevent="deleteInvitation(invitation)">
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

    <div ref="saveInvitationTemplate" class="is-hidden">
      <div class="content" onsubmit="return false">
        <div class="is-size-5 mb-4">Create invitation</div>

        <form class="form admin-invitations-form">
          <div class="field">
            <label class="label" for="type">Type</label>
            <div class="control">
              <div class="select">
                <select id="form-invitation-type">
                  <option value="single">Single</option>
                  <option value="multiple">Multiple</option>
                </select>
              </div>
            </div>
          </div>

          <div class="field">
            <label class="label" for="expire">Expire</label>
            <div class="control">
              <input class="input" type="text" id="form-invitation-expire">
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
  import Icon from '@/components/Shared/Icon.vue'
  import Booler from '@/components/Shared/Booler.vue'
  import minusIcon from '@/assets/images/minus.svg'
  import clipboardIcon from '@/assets/images/clipboard.svg'
  import addIcon from '@/assets/images/add.svg'
  import Swal from 'sweetalert2'
  import AdminTable from '@/components/Admin/AdminTable.vue'
  import AirDatepicker from 'air-datepicker'
  import localeEn from 'air-datepicker/locale/en'
  import { formattedTimestamp } from '@/lib/time_stuff'
  import ActionButton from '@/components/Shared/ActionButton.vue'

  export default {
    name: 'AdminInvitations',
    components: {
      Icon,
      AdminTable,
      Booler,
      ActionButton,
    },
    data() {
      return {
        minusIcon,
        addIcon,
        clipboardIcon,
        invitations: [],
        apiUrl: import.meta.env.VITE_API_URL,
        formattedTimestamp: formattedTimestamp,
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
      openNewInvitationModal() {
        const templateElementSelector = '.swal2-html-container .admin-invitations-form'

        Swal.fire({
          icon: null,
          showCancelButton: true,
          confirmButtonText: 'Save',
          confirmButtonColor: this.colors.main,
          html: this.$refs.saveInvitationTemplate.innerHTML,
          didOpen: () => new AirDatepicker(`${templateElementSelector} #form-invitation-expire`, {
            locale: localeEn,
            timepicker: true,
            minutesStep: 5,
          }),
        }).then(async (result) => {
          if (result.isConfirmed) {
            this.mitt.emit('spinnerStart')

            const response = await this.$store.dispatch('app/saveInvitation', {
              type: document.querySelector(templateElementSelector).querySelector('#form-invitation-type').value,
              expire: document.querySelector(templateElementSelector).querySelector('#form-invitation-expire').value,
            })

            if (response.ok) {
              this.awn.success('Invitation has been created.')
              this.loadInvitations()
            } else {
              this.awn.warning('Something went wrong, try again.')
            }

            this.mitt.emit('spinnerStop')
          }
        })
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
      deleteInvitation(invitation) {
        const that = this

        Swal.fire({
          title: 'Removing invitation',
          html: `Are you sure to remove invitation <strong>${invitation.code}</strong>?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: this.colors.main,
        }).then(async (result) => {
          if (result.isConfirmed) {
            this.mitt.emit('spinnerStart')

            const response = await this.$store.dispatch('app/deleteInvitations', [invitation.id])
            const data = await response.json()

            if (response.ok) {
              this.awn.success(data.message)
              that.loadInvitations()
            } else {
              this.awn.warning(data.message)
            }

            this.mitt.emit('spinnerStop')
          }
        })
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
