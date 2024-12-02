<template>
  <div class="admin-users">
    <h3 class="is-size-3 has-text-weight-bold mb-4">Users</h3>

    <div class="mb-4">
      <ActionButton class="mr-2 mb-2" buttonText="Create new user" @click="createNewUserModalOpen()" :icon="newUserIcon"></ActionButton>
      <ActionButton class="mr-2 mb-2" buttonText="Import users from CSV" @click="importUsersFromCsvModalOpen()" :icon="fileIcon"></ActionButton>
      <ActionButton class="mr-2 mb-2" buttonText="Delete users" @click="() => deleteUsersConfirm(selectedUsers)" :icon="removeUserIcon"></ActionButton>
      <ActionButton class="mr-2 mb-2" buttonText="Set ReIntake status" @click="() => setReintakeStatusModalOpen(selectedUsers)" :icon="reintakeIcon"></ActionButton>
      <ActionButton class="mr-2 mb-2" buttonText="Set active affiliation" @click="() => setActiveAffiliationModalOpen(selectedUsers)" :icon="affiliationIcon"></ActionButton>
      <ActionButton class="mr-2 mb-2" buttonText="Set active status" @click="() => setActiveStatusModalOpen(selectedUsers)" :icon="activeIcon"></ActionButton>
    </div>

    <div class="mb-2">
      <SearchInput v-model="searchTerm" />
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
            <th class="admin-users-table-row-cell-narrow">Created</th>
            <th class="admin-users-table-row-cell-narrow">Last login</th>
            <th class="admin-users-table-row-cell-narrow">Active</th>
            <th class="admin-users-table-row-cell-narrow">Admin</th>
            <th data-sort-method="none" class="admin-users-table-row-cell-narrow no-sort"></th>
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
              <VTooltip distance="10" placement="left" v-if="user.invitation_code" class="admin-users-table-invitation">
                <a class="button" @click="searchTerm = user.invitation_code">{{ user.invitation_code }}</a>

                <template #popper>
                  Click to filter by this code
                </template>
              </VTooltip>
            </td>
            <td class="admin-users-table-row-cell-narrow">{{ user.created_at }}</td>
            <td class="admin-users-table-row-cell-narrow">{{ user.last_login }}</td>
            <td class="admin-users-table-is-active admin-users-table-row-cell-narrow">
              <div class="is-hidden">{{ user.active }}</div>
              <Booler :value="user.active" />
            </td>
            <td class="admin-users-table-is-admin admin-users-table-row-cell-narrow">
              <div class="is-hidden">{{ user.groups.includes('admin') }}</div>
              <Booler :value="user.groups.includes('admin')" />
            </td>
            <td class="admin-users-table-row-cell-narrow">
              <VDropdown>
                <div>
                  <a class="button">
                    <Icon :src="dropdownIcon" />
                  </a>
                </div>

                <template #popper>
                  <router-link class="dropdown-item" :to="{ name: 'user-profile-admin.index', params: { id: user.id } }">
                    <Icon :src="editIcon" :interactive="false" />
                    Edit user profile
                  </router-link>
                  <router-link class="dropdown-item" :to="{ name: 'people.details', params: { id: user.people_id } }" target="_blank">
                    <Icon :src="userIcon" :interactive="false" />
                    Show user profile
                  </router-link>
                  <a class="dropdown-item" @click.prevent="setUserRoleModalOpen(user)">
                    <Icon :src="toggleAdminIcon" :interactive="false" />
                    Change role
                  </a>
                  <a class="dropdown-item" @click.prevent="deleteUsersConfirm([user])">
                    <Icon :src="minusIcon" :interactive="false" />
                    Delete user
                  </a>
                </template>
              </VDropdown>
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
    :focusOnConfirm="false"
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
    :focusOnConfirm="false"
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

  <Modal
    v-model="setActiveStatusModalStatus"
    title="Set active status"
    @confirm="setActiveStatus()"
    @cancel="setActiveStatusModalStatus = false"
  >
    Set active status for selected users to:

    <div class="field mt-2">
      <div class="control">
        <div class="select">
          <select v-model="setActiveStatusModalStatusValue">
            <option value="not_active">Not active</option>
            <option value="active">Active</option>
          </select>
        </div>
      </div>
    </div>
  </Modal>

  <Modal
    v-model="createNewUserModalStatus"
    title="Create new user"
    @confirm="createNewUser()"
    @cancel="createNewUserModalStatus = false"
    :focusOnConfirm="false"
  >
    <form @submit.prevent="createNewUser()" ref="createNewUserForm">
      <div class="field mt-2">
        <div class="control">
          <label class="label">First name</label>

          <input type="text" class="input" v-model="createNewUserModalCurrent.first_name" required="true">
        </div>
      </div>

      <div class="field mt-2">
        <div class="control">
          <label class="label">Last name</label>

          <input type="text" class="input" v-model="createNewUserModalCurrent.last_name" required="true">
        </div>
      </div>

      <div class="field mt-2">
        <div class="control">
          <label class="label">Email</label>

          <input type="email" class="input" v-model="createNewUserModalCurrent.email" required="true">
        </div>
      </div>
    </form>
  </Modal>
</template>

<script>
  import Icon from '@/components/Shared/Icon.vue'
  import Booler from '@/components/Shared/Booler.vue'
  import AdminTable from '@/components/Admin/AdminTable.vue'
  import ActionButton from '@/components/Shared/ActionButton.vue'
  import Modal from '@/components/Shared/Modal.vue'
  import CustomField from '@/components/CustomFields/CustomField.vue'
  import SearchInput from '@/components/Shared/SearchInput.vue'

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
  import activeIcon from '@/assets/images/active.svg'
  import newUserIcon from '@/assets/images/new_user.svg'
  import removeUserIcon from '@/assets/images/remove_user.svg'
  import dropdownIcon from '@/assets/images/dropdown.svg'

  export default {
    name: 'AdminUsers',
    components: {
      Icon,
      AdminTable,
      Booler,
      ActionButton,
      Modal,
      CustomField,
      SearchInput,
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
        activeIcon,
        newUserIcon,
        removeUserIcon,
        dropdownIcon,
        profileStructure: [],
        users: [],
        roles: [
          'user',
          'admin',
        ],
        apiUrl: import.meta.env.VITE_API_URL,
        searchTerm: '',

        importUsersCsvModalStatus: false,

        deleteUserModalStatus: false,
        deleteUserModalCurrent: [],

        setUserRoleModalStatus: false,
        setUserRoleModalCurrent: null,
        setUserRoleCurrentRole: 'user',

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
        setActiveAffiliationModalOptions: {
          'new_affiliation': 'New affiliation',
          'unset': 'Unset',
        },
        setActiveAffiliationModalOptionsSelected: 'new_affiliation',

        setActiveStatusModalStatus: false,
        setActiveStatusModalStatusValue: 'not_active',

        createNewUserModalStatus: false,
        createNewUserModalCurrent: {
          first_name: '',
          last_name: '',
          email: '',
        },
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
        const users = await this.$store.dispatch('admin/fetchUsers')

        this.users = users
      },
      async loadProfileStructure() {
        let profileStructure = await this.$store.dispatch('user/fetchProfileStructure')

        this.profileStructure = profileStructure
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
      async deleteUsers() {
        let response = null
        try {
          const usersIds = this.deleteUserModalCurrent.map(user => user.id)
          response = await this.$store.dispatch('admin/deleteUsers', usersIds)
        } catch (error) {
          this.awn.warning('Something went wrong, try again.')
          return
        }

        this.awn.success(response.message)
        this.loadUsers()
        this.$refs.toggleAllCheckbox.checked = false
        this.users.map(user => (user.selected = false, user))
        this.deleteUserModalStatus = false
        this.$store.dispatch('people/setPeopleMarkReload', true)
      },
      setUserRoleModalOpen(user) {
        this.setUserRoleModalStatus = true
        this.setUserRoleModalCurrent = user
      },
      async setUserRole() {
        try {
          await this.$store.dispatch('admin/changeUserRole', {
            users: [this.setUserRoleModalCurrent.id],
            role: this.setUserRoleCurrentRole,
          })
        } catch (error) {
          this.awn.warning('Something went wrong, try again.')
          return
        }

        this.loadUsers()
        this.setUserRoleModalStatus = false
      },
      importUsersFromCsvModalOpen() {
        this.importUsersCsvModalStatus = true
      },
      async importUsersFromCsv() {
        const file = this.$refs.importUsersCsvModalFileInput.files[0]
        let response = null

        if (file) {
          try {
            response = await this.$store.dispatch('admin/importUsersFromCsv', file)
          } catch (error) {
            this.awn.warning('Something went wrong, try again.')
            return
          }
        } else {
          this.awn.warning('No file selected.')
        }

        this.loadUsers()
        this.awn.success(response.message)
        this.importUsersCsvModalStatus = false
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
        try {
          await this.$store.dispatch('admin/setReintakeStatus', {
            users: this.setReintakeStatusCurrent.map(user => user.id),
            status: this.setReintakeStatusSelected,
          })
        } catch (error) {
          this.awn.warning('Something went wrong, try again.')
          return
        }

        this.awn.success('ReIntake status has been changed for selected users.')
        this.loadUsers()
        this.setReintakeStatusModalStatus = false
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

        try {
          await this.$store.dispatch('admin/setActiveAffiliation', {
            users: this.setActiveAffiliationCurrent.map(user => user.id),
            affiliation: affiliation,
          })
        } catch (error) {
          this.awn.warning('Something went wrong, try again.')
          return
        }

        this.awn.success('Active affiliation has been updated.')
        this.loadUsers()
        this.setActiveAffiliationModalStatus = false
      },
      setActiveStatusModalOpen(users) {
        if (users.length === 0) {
          this.awn.warning('No users selected.')

          return
        }

        this.setActiveStatusModalStatusValue = 'not_active'
        this.setActiveStatusModalCurrent = users
        this.setActiveStatusModalStatus = true
      },
      async setActiveStatus() {
        try {
          await this.$store.dispatch('admin/setActiveStatus', {
            users: this.setActiveStatusModalCurrent.map(user => user.id),
            status: this.setActiveStatusModalStatusValue,
          })
        } catch (error) {
          this.awn.warning('Something went wrong, try again.')
          return
        }

        this.awn.success('Active status has been updated.')
        this.loadUsers()
        this.setActiveStatusModalStatus = false
      },
      createNewUserModalOpen() {
        this.createNewUserModalStatus = true
      },
      async createNewUser() {
        if (this.$refs.createNewUserForm.reportValidity() === false) {
          return
        }

        try {
          await this.$store.dispatch('admin/createNewUser', this.createNewUserModalCurrent)
        } catch (error) {
          this.awn.warning('Something went wrong, try again.')
          return
        }

        this.awn.success('New user has been created.')
        this.loadUsers()
        this.createNewUserModalStatus = false
        this.createNewUserModalCurrent = {
          first_name: '',
          last_name: '',
          email: '',
        }
      },
    },
  }
</script>

<style lang="scss">
  $cl: '.admin-users';

  #{$cl} {
    &-import-csv {
      input {
        display: none;
      }
    }

    &-table {
      &-invitation {
        a {
          width: 100px;
          overflow: hidden;
          text-overflow: ellipsis;
          display: block;
        }
      }

      &-row-cell-narrow {
        max-width: 120px;
        white-space: normal;
        word-break: normal !important;
      }
    }
  }
</style>
