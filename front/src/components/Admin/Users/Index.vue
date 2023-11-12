<template>
  <div class="admin-users">
    <h3 class="is-size-3 has-text-weight-bold mb-4">Users</h3>

    <form class="form">
      <admin-table :tableClasses="['admin-users-table']">
        <thead>
          <tr class="no-select no-break">
            <th data-sort-method="none" class="no-sort">
              <input type="checkbox" ref="toggleAllCheckbox" @click="toggleAll()">
            </th>
            <th>Firstname</th>
            <th>Lastname</th>
            <th>Email</th>
            <th>Created</th>
            <th>Last login</th>
            <th>Admin</th>
            <th data-sort-method="none" class="no-sort">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id" class="no-break">
            <td class="admin-table-selector">
              <input type="checkbox" v-model="user.selected">
            </td>
            <td>{{ user.first_name }}</td>
            <td>{{ user.last_name }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.created_at }}</td>
            <td>{{ user.last_login }}</td>
            <td class="admin-users-table-is-admin">
              <Booler :value="user.groups.includes('admin')" />
            </td>
            <td class="admin-table-actions">
              <a title="Change role" @click.prevent="changeUserRole(user)">
                  <Icon :src="toggleAdminIcon" />
                </a>
              <a title="Delete user" @click.prevent="deleteUser(user)">
                <Icon :src="minusIcon" />
              </a>
            </td>
          </tr>
          <tr v-if="users.length === 0">
            <td colspan="4">No users found.</td>
          </tr>
        </tbody>
      </admin-table>
    </form>
  </div>

  <div ref="adminUserSetRoleTemplate" class="is-hidden">
    <div class="content admin-users-set-role">
      <div class="is-size-5 mb-4">Choose role to set</div>

      <div class="field">
        <div class="control" v-for="(option, index) in roles" :key="index">
          <label class="radio">
            <input
              type="radio"
              name="adminUserSetRole"
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
  import Booler from '@/components/Shared/Booler.vue'
  import minusIcon from '@/assets/images/minus.svg'
  import clipboardIcon from '@/assets/images/clipboard.svg'
  import addWhiteIcon from '@/assets/images/add_white.svg'
  import toggleAdminIcon from '@/assets/images/toggle_admin.svg'
  import Swal from 'sweetalert2'
  import AdminTable from '@/components/Admin/AdminTable.vue'

  export default {
    name: 'AdminUsers',
    components: {
      Icon,
      AdminTable,
      Booler,
    },
    data() {
      return {
        minusIcon,
        addWhiteIcon,
        clipboardIcon,
        toggleAdminIcon,
        users: [],
        roles: [
          'user',
          'admin',
        ],
        apiUrl: import.meta.env.VITE_API_URL,
      }
    },
    created() {
      this.initialDataLoad()
    },
    methods: {
      initialDataLoad() {
        this.loadUsers()
      },
      async loadUsers() {
        this.mitt.emit('spinnerStart')

        const users = await this.$store.dispatch('app/fetchUsers')

        this.users = users

        this.mitt.emit('spinnerStop')
      },
      toggleAll() {
        const newStatus = this.$refs.toggleAllCheckbox.checked

        this.users
          .map((user) => {
            user.selected = newStatus

            return user
          })
      },
      deleteUser(user) {
        const that = this

        Swal.fire({
          title: 'Removing user',
          html: `Are you sure to remove <strong>${user.email}</strong>?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: this.colors.main,
        }).then(async (result) => {
          if (result.isConfirmed) {
            this.mitt.emit('spinnerStart')

            const response = await this.$store.dispatch('app/deleteUsers', [user.id])
            const data = await response.json()

            if (response.ok) {
              this.awn.success(data.message)
              that.loadUsers()
            } else {
              this.awn.warning(data.message)
            }

            this.mitt.emit('spinnerStop')
          }
        })
      },
      changeUserRole(user) {
        const templateElementSelector = '.swal2-html-container .admin-users-set-role'

        Swal.fire({
          icon: null,
          showCancelButton: true,
          confirmButtonText: 'Set',
          confirmButtonColor: this.colors.main,
          html: this.$refs.adminUserSetRoleTemplate.innerHTML,
          didOpen: () => {
            document.querySelector(templateElementSelector).querySelector('input').checked = true
          },
        }).then(async (result) => {
          if (result.isConfirmed) {
            this.mitt.emit('spinnerStart')

            const response = await this.$store.dispatch('app/changeUserRole', {
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
    },
  }
</script>

<style lang="scss"></style>
