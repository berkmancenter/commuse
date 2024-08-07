<template>
  <div class="admin-users">
    <h3 class="is-size-3 has-text-weight-bold mb-4">Users</h3>

    <div class="mb-4">
      <ActionButton class="mr-2 mb-2" buttonText="Import users from CSV" @click="importUsersFromCsvModalOpen()" :icon="fileIcon"></ActionButton>
      <ActionButton class="mr-2 mb-2" buttonText="Delete users" @click="() => deleteUsersConfirm(selectedUsers)" :icon="minusIcon"></ActionButton>
      <ActionButton class="mr-2 mb-2" buttonText="Set ReIntake status" @click="() => setReintakeStatusModalOpen(selectedUsers)" :icon="reintakeIcon"></ActionButton>
      <ActionButton class="mr-2 mb-2" buttonText="Set active affiliation" @click="() => setActiveAffiliationModalOpen(selectedUsers)" :icon="affiliationIcon"></ActionButton>
    </div>

    <div class="admin-users-search mb-4">
      <input
        type="text"
        v-model="searchTerm"
        placeholder="Search"
        class="input"
      >
      <span><img :src="searchIcon"></span>
    </div>

    <form class="form">
      <admin-table :tableClasses="['admin-users-table']">
        <thead>
          <tr class="no-select no-break">
            <th data-sort-method="none" class="no-sort">
              <input type="checkbox" ref="toggleAllCheckbox" @click="toggleAll()">
            </th>
            <th>First name</th>
            <th>Last name</th>
            <th>Email</th>
            <th>ReIntake</th>
            <th>Invitation code</th>
            <th>Created</th>
            <th>Last login</th>
            <th>Admin</th>
            <th data-sort-method="none" class="no-sort">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in filteredUsers" :key="user.id" class="no-break">
            <td class="admin-table-selector">
              <input type="checkbox" v-model="user.selected">
            </td>
            <td>{{ user.first_name }}</td>
            <td>{{ user.last_name }}</td>
            <td>{{ user.email }}</td>
            <td>{{ setReintakeStatuses[user.reintake] }}</td>
            <td>
              <VTooltip distance="10" placement="left" v-if="user.invitation_code">
                <a class="button" @click="searchTerm = user.invitation_code">{{ user.invitation_code }}</a>

                <template #popper>
                  Click to filter by this code
                </template>
              </VTooltip>
            </td>
            <td>{{ user.created_at }}</td>
            <td>{{ user.last_login }}</td>
            <td class="admin-users-table-is-admin">
              <div class="is-hidden">{{ user.groups.includes('admin') }}</div>
              <Booler :value="user.groups.includes('admin')" />
            </td>
            <td>
              <div class="admin-table-actions">
                <router-link :to="{ name: 'user-profile-admin.index', params: { id: user.id } }" title="Edit user profile">
                  <Icon :src="editIcon" />
                </router-link>
                <router-link :to="{ name: 'people.details', params: { id: user.people_id } }" target="_blank" title="Show user profile">
                  <Icon :src="userIcon" />
                </router-link>
                <a title="Change role" @click.prevent="setUserRoleModalOpen(user)">
                  <Icon :src="toggleAdminIcon" />
                </a>
                <a title="Delete user" @click.prevent="deleteUsersConfirm([user])">
                  <Icon :src="minusIcon" />
                </a>
              </div>
            </td>
          </tr>
          <tr v-if="users.length === 0">
            <td colspan="4">No users found.</td>
          </tr>
        </tbody>
      </admin-table>
    </form>
  </div>

  <Modal
    v-model="importUsersCsvModalStatus"
    title="Import users from CSV"
    :focusOnConfirm="true"
    class="admin-users-import-csv"
    @confirm="importUsersFromCsv()"
    @cancel="importUsersCsvModalStatus = false"
  >
    <div><a target="_blank" :href="`${apiUrl}/api/admin/users/csvImportTemplate`">Download CSV import file structure</a></div>

    <div class="field">
      <div class="control">
        <input type="file" accept=".csv" ref="importUsersCsvModalFileInput">
        <div class="my-2">
          <button class="button" type="button" @click="$refs.importUsersCsvModalFileInput.click()">
            <Icon :src="fileIcon" :interactive="false" />
            Choose file
          </button>
        </div>
      </div>
    </div>
  </Modal>

  <Modal
    v-model="setUserRoleModalStatus"
    title="Set user role"
    :focusOnConfirm="true"
    @confirm="setUserRole()"
    @cancel="setUserRoleModalStatus = false"
  >
    <div class="mb-2">Set a new user role for <span class="has-text-weight-bold">{{ setUserRoleModalCurrent.email }}</span>.</div>

    <div class="field">
      <div class="control" v-for="(option, index) in roles" :key="index">
        <label class="radio">
          <input
            type="radio"
            :value="option"
            v-model="setUserRoleCurrentRole"
            class="mb-2"
          >
          {{ option }}
        </label>
      </div>
    </div>
  </Modal>

  <Modal
    v-model="deleteUserModalStatus"
    title="Delete user"
    @confirm="deleteUsers()"
    @cancel="deleteUserModalStatus = false"
  >
    Are you sure you delete <span class="has-text-weight-bold">{{ deleteUserModalCurrent.map((u) => u.email).join(', ') }}</span>?
  </Modal>

  <Modal
    v-model="setReintakeStatusModalStatus"
    title="Set Reintake status"
    :focusOnConfirm="true"
    class="admin-users-set-reintake-status"
    @confirm="setReintakeStatus()"
    @cancel="setReintakeStatusModalStatus = false"
  >
    Choose a status that will be set to selected users after confirmation:

    <div class="field mt-2">
      <div class="control" v-for="(option, index) in setReintakeStatuses" :key="index">
        <label class="radio">
          <input
            type="radio"
            v-model="setReintakeStatusSelected"
            :value="index"
            class="mb-2"
          >
          {{ option }}
        </label>
      </div>
    </div>
  </Modal>

  <Modal
    v-model="setActiveAffiliationModalStatus"
    title="Set active affiliation"
    :focusOnConfirm="true"
    class="admin-users-set-active-affiliation"
    @confirm="setActiveAffiliation()"
    @cancel="setActiveAffiliationModalStatus = false"
  >
    Choose an affiliation that will be set to selected users as their active affilication.
    <br>
    Select unset to remove the active affiliation for selected users.

    <div class="field mt-2">
      <div class="control" v-for="(option, index) in setActiveAffiliationModalOptions" :key="index">
        <label class="radio">
          <input
            type="radio"
            v-model="setActiveAffiliationModalOptionsSelected"
            :value="index"
            class="mb-2"
          >
          {{ option }}
        </label>
      </div>
    </div>

    <div class="field mt-2" v-show="setActiveAffiliationModalOptionsSelected === 'new_affiliation'">
      <CustomField
        :label="activeAffiliateField.title"
        :hide-title="true"
        :description="activeAffiliateField.description"
        :type="activeAffiliateField.input_type"
        :machine-name="activeAffiliateField.machine_name"
        :metadata="activeAffiliateField.metadata"
        :field-data="activeAffiliateField"
        :value="$store.state.admin.activeAffiliationModalValue[activeAffiliateField.machine_name]"
        :storeObject="$store.state.admin.activeAffiliationModalValue"
        :auto-populate-first-item="true"
        :force-one-item="true"
        ref="activeAffiliateFieldRef"
      ></CustomField>
    </div>
  </Modal>
</template>

<script>
  import Icon from '@/components/Shared/Icon.vue'
  import Booler from '@/components/Shared/Booler.vue'
  import minusIcon from '@/assets/images/minus.svg'
  import clipboardIcon from '@/assets/images/clipboard.svg'
  import addWhiteIcon from '@/assets/images/add_white.svg'
  import toggleAdminIcon from '@/assets/images/toggle_admin.svg'
  import userIcon from '@/assets/images/user.svg'
  import fileIcon from '@/assets/images/file.svg'
  import editIcon from '@/assets/images/edit.svg'
  import searchIcon from '@/assets/images/search.svg'
  import reintakeIcon from '@/assets/images/reintake.svg'
  import affiliationIcon from '@/assets/images/affiliation.svg'
  import AdminTable from '@/components/Admin/AdminTable.vue'
  import ActionButton from '@/components/Shared/ActionButton.vue'
  import Modal from '@/components/Shared/Modal.vue'
  import CustomField from '@/components/CustomFields/CustomField.vue'

  export default {
    name: 'AdminUsers',
    components: {
      Icon,
      AdminTable,
      Booler,
      ActionButton,
      Modal,
      CustomField,
    },
    data() {
      return {
        minusIcon,
        addWhiteIcon,
        clipboardIcon,
        toggleAdminIcon,
        fileIcon,
        userIcon,
        editIcon,
        searchIcon,
        reintakeIcon,
        affiliationIcon,
        users: [],
        roles: [
          'user',
          'admin',
        ],
        apiUrl: import.meta.env.VITE_API_URL,
        setUserRoleModalStatus: false,
        setUserRoleModalCurrent: null,
        setUserRoleCurrentRole: 'user',
        importUsersCsvModalStatus: false,
        deleteUserModalStatus: false,
        deleteUserModalCurrent: [],
        searchTerm: '',
        setReintakeStatusModalStatus: false,
        setReintakeStatuses: {
          not_required: 'Not required',
          required: 'Required',
          accepted: 'Accepted',
          denied: 'Denied',
        },
        setReintakeStatusSelected: 'not_required',
        setReintakeStatusCurrent: [],
        setActiveAffiliationModalStatus: false,
        setActiveAffiliationCurrent: [],
        profileStructure: [],
        setActiveAffiliationModalOptions: {
          'new_affiliation': 'New affiliation',
          'unset': 'Unset',
        },
        setActiveAffiliationModalOptionsSelected: 'new_affiliation',
      }
    },
    created() {
      this.initialDataLoad()
    },
    computed: {
      selectedUsers() {
        return this.users
          .filter(user => user.selected)
      },
      filteredUsers() {
        const searchTerm = this.searchTerm.toLowerCase();

        return this.users.filter((user) => {
          const searchText = `${user.first_name} ${user.last_name} ${user.email} ${user.invitation_code}`.toLowerCase()

          return searchText.includes(searchTerm)
        })
      },
      activeAffiliateField() {
        let group = this.profileStructure.find(field => field.machine_name === 'affiliation')

        return group.custom_fields.find(field => field.machine_name === 'activeAffiliation')
      },
    },
    methods: {
      initialDataLoad() {
        this.loadUsers()
        this.loadProfileStructure()
      },
      async loadUsers() {
        this.mitt.emit('spinnerStart')

        const users = await this.$store.dispatch('admin/fetchUsers')

        this.users = users

        this.mitt.emit('spinnerStop')
      },
      async loadProfileStructure() {
        this.mitt.emit('spinnerStart')

        let profileStructure = await this.$store.dispatch('user/fetchProfileStructure')

        this.profileStructure = profileStructure

        this.mitt.emit('spinnerStop')
      },
      toggleAll() {
        const newStatus = this.$refs.toggleAllCheckbox.checked

        this.users.map(user => (user.selected = newStatus, user))
      },
      deleteUsersConfirm(users) {
        if (users.length === 0) {
          this.awn.warning('No users selected.')

          return
        }

        this.deleteUserModalCurrent = users
        this.deleteUserModalStatus = true
      },
      async deleteUsers(users) {
        this.mitt.emit('spinnerStart')

        const usersIds = this.deleteUserModalCurrent.map(user => user.id)
        const response = await this.$store.dispatch('admin/deleteUsers', usersIds)
        const data = await response.json()

        if (response.ok) {
          this.awn.success(data.message)
          this.loadUsers()
        } else {
          this.awn.warning(data.message)
        }

        this.$refs.toggleAllCheckbox.checked = false
        this.users.map(user => (user.selected = false, user))

        this.deleteUserModalStatus = false
        this.mitt.emit('spinnerStop')
        this.$store.dispatch('people/setPeopleMarkReload', true)
      },
      setUserRoleModalOpen(user) {
        this.setUserRoleModalStatus = true
        this.setUserRoleModalCurrent = user
      },
      async setUserRole() {
        this.mitt.emit('spinnerStart')

        const response = await this.$store.dispatch('admin/changeUserRole', {
          users: [this.setUserRoleModalCurrent.id],
          role: this.setUserRoleCurrentRole,
        })

        if (response.ok) {
          this.awn.success('User role have been updated.')
          this.loadUsers()
        } else {
          this.awn.warning('Something went wrong, try again.')
        }

        this.setUserRoleModalStatus = false
        this.mitt.emit('spinnerStop')
      },
      importUsersFromCsvModalOpen() {
        this.importUsersCsvModalStatus = true
      },
      async importUsersFromCsv() {
        this.mitt.emit('spinnerStart')

        const file = this.$refs.importUsersCsvModalFileInput.files[0]

        if (file) {
          const response = await this.$store.dispatch('admin/importUsersFromCsv', file)

          if (response.ok) {
            this.loadUsers()
            const data = await response.json()
            this.awn.success(data.message)
          } else {
            this.awn.warning('Something went wrong, try again.')
          }

          this.importUsersCsvModalStatus = false
        } else {
          this.awn.warning('No file selected.')
        }

        this.mitt.emit('spinnerStop')
        this.$store.dispatch('people/setPeopleMarkReload', true)
      },
      setReintakeStatusModalOpen(users) {
        if (users.length === 0) {
          this.awn.warning('No users selected.')

          return
        }

        this.setReintakeStatusCurrent = users
        this.setReintakeStatusModalStatus = true
      },
      async setReintakeStatus() {
        this.mitt.emit('spinnerStart')

        const response = await this.$store.dispatch('admin/setReintakeStatus', {
          users: this.setReintakeStatusCurrent.map(user => user.id),
          status: this.setReintakeStatusSelected,
        })

        if (response.ok) {
          this.awn.success('ReIntake status has been changed for selected users.')
          this.loadUsers()
        } else {
          this.awn.warning('Something went wrong, try again.')
        }

        this.setReintakeStatusModalStatus = false
        this.mitt.emit('spinnerStop')
      },
      setActiveAffiliationModalOpen(users) {
        if (users.length === 0) {
          this.awn.warning('No users selected.')

          return
        }

        this.$store.dispatch('admin/setActiveAffiliationModalValue', {})
        this.setActiveAffiliationCurrent = users
        this.setActiveAffiliationModalStatus = true
        this.setActiveAffiliationModalOptionsSelected = 'new_affiliation'
      },
      async setActiveAffiliation() {
        let affiliation

        if (this.setActiveAffiliationModalOptionsSelected === 'new_affiliation') {
          const validation = this.$refs.activeAffiliateFieldRef.validate()

          if (validation.status === false) {
            this.awn.warning(validation.message)

            return
          }

          affiliation = this.$store.state.admin.activeAffiliationModalValue[this.activeAffiliateField.machine_name]
        } else {
          affiliation = 'unset'
        }

        this.mitt.emit('spinnerStart')

        const response = await this.$store.dispatch('admin/setActiveAffiliation', {
          users: this.setActiveAffiliationCurrent.map(user => user.id),
          affiliation: affiliation,
        })

        if (response.ok) {
          this.awn.success('Active affiliation has been updated.')
          this.loadUsers()
        } else {
          this.awn.warning('Something went wrong, try again.')
        }

        this.setActiveAffiliationModalStatus = false
        this.mitt.emit('spinnerStop')
      },
    },
  }
</script>

<style lang="scss">
  .admin-users-import-csv {
    input {
      display: none;
    }
  }

  .admin-users-search {
    position: relative;
    max-width: 300px;

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
</style>
