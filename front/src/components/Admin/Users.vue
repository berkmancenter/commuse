<template>
  <div class="content admin-users">
    <h4 class="is-size-4">Users</h4>

    <form class="form">
      <super-admin-filter :users="users" @change="superAdminFilterChanged" />

      <admin-table :tableClasses="['admin-users-table']">
        <thead>
          <tr class="no-select">
            <th data-sort-method="none" class="no-sort">
              <input type="checkbox" ref="toggleAllCheckbox" @click="toggleAll()">
            </th>
            <th>Email</th>
            <th>Joined</th>
            <th data-sort-method="none" class="no-sort">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in filteredItems" :key="user.id">
            <td class="admin-table-selector">
              <input type="checkbox" v-model="user.selected">
            </td>
            <td>{{ user.email }}</td>
            <td class="no-break">{{ user.joined }}</td>
            <td class="admin-table-actions">
              <div class="admin-table-actions">
                <a title="Change role" @click.prevent="changeUsersRole(user)">
                  <Icon :src="toggleAdminIcon" />
                </a>
              </div>
            </td>
          </tr>
          <tr v-if="filteredItems.length === 0">
            <td colspan="4">No users found.</td>
          </tr>
        </tbody>
      </admin-table>
    </form>
  </div>

  <div ref="adminUsersSetRoleTemplate" class="is-hidden">
      <div class="content admin-users-set-role">
        <div class="is-size-5 mb-4">Choose role to set</div>

        <div class="field">
          <div class="control" v-for="(option, index) in roles" :key="index">
            <label class="radio">
              <input
                type="radio"
                name="adminUsersSetRole"
                :value="option"
                class="mb-2"
              >
              {{ option }}
            </label>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
  import Icon from '@/components/Shared/Icon.vue'
  import minusIcon from '@/assets/images/minus.svg'
  import yesIcon from '@/assets/images/yes.svg'
  import noIcon from '@/assets/images/no.svg'
  import toggleAdminIcon from '@/assets/images/toggle_admin.svg'
  import Swal from 'sweetalert2'
  import AdminTable from '@/components/Admin/AdminTable.vue'
  import SuperAdminFilter from '@/components/Admin/SuperAdminFilter.vue'

  export default {
    name: 'AdminUsers',
    components: {
      Icon,
      AdminTable,
      SuperAdminFilter,
    },
    data() {
      return {
        minusIcon,
        yesIcon,
        noIcon,
        toggleAdminIcon,
        users: [],
        filteredItems: [],
        roles: [
          'admin',
          'user',
        ],
        setRoleSelectedRole: 'user',
      }
    },
    created() {
      //this.initialDataLoad()
    },
    methods: {
      initialDataLoad() {
        this.loadUsers()
      },
      async loadUsers() {
        this.mitt.emit('spinnerStart')

        const users = await this.$store.dispatch('admin/fetchUsers')

        this.users = users

        this.mitt.emit('spinnerStop')
      },
      removeUser(user) {
        const that = this

        Swal.fire({
          title: 'Removing user',
          html: `Are you sure to remove ${user.email}?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: this.colors.main,
        }).then(async (result) => {
          if (result.isConfirmed) {
            this.mitt.emit('spinnerStart')

            const response = await this.$store.dispatch('admin/deleteUsers', {
              users: [user.id],
            })

            if (response.ok) {
              this.awn.success('Users have been removed.')
              that.loadUsers()
            } else {
              this.awn.warning('Something went wrong, try again.')
            }

            this.mitt.emit('spinnerStop')
          }
        })
      },
      toggleAll() {
        const newStatus = this.$refs.toggleAllCheckbox.checked

        this.users
          .map((user) => {
            user.selected = newStatus

            return user
          })
      },
      changeUsersRole(user) {
        const templateElementSelector = '.swal2-html-container .admin-users-set-role'

        Swal.fire({
          icon: null,
          showCancelButton: true,
          confirmButtonText: 'Set',
          confirmButtonColor: this.colors.main,
          html: this.$refs.adminUsersSetRoleTemplate.innerHTML,
          didOpen: () => {
            document.querySelector(templateElementSelector).querySelector('input').checked = true
          },
        }).then(async (result) => {
          if (result.isConfirmed) {
            this.mitt.emit('spinnerStart')

            const response = await this.$store.dispatch('admin/changeUsersRole', {
              users: [user.id],
              role: document.querySelector(templateElementSelector).querySelector('input:checked').value,
            })

            if (response.ok) {
              this.awn.success('User role have been updated.')
              this.loadUsers()
            } else {
              this.awn.warning('Something went wrong, try again.')
            }

            this.mitt.emit('spinnerStop')
          }
        })
      },
      superAdminFilterChanged(users) {
        this.filteredItems = users
      },
    },
  }
</script>

<style lang="scss"></style>
