<template>
  <div class="account-section">
    <h3 class="is-size-3 has-text-weight-bold mb-4">Account settings</h3>


    <form class="form-commuse-blocks mb-4" @submit.prevent="changePassword">
      <div class="panel">
        <p class="panel-heading">
          People Portal Profile Status
        </p>
        <div class="panel-block">
          <div class="notification is-warning" v-if="!$store.state.app.userProfile.public_profile">
            {{ $store.state.app.publicSystemSettings.PublicProfileWarningInAccountSettings.value }}
          </div>

          <div class="field">
            <label class="label">{{ $store.state.app.publicSystemSettings.PublicProfileCheckboxLabel.value }}</label>
            <div class="control">
              <div class="control">
                <input type="checkbox" v-model="$store.state.app.userProfile.public_profile" @change="updateProfileStatus()">
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>

    <form class="form-commuse-blocks" @submit.prevent="changePassword">
      <div class="panel">
        <p class="panel-heading">
          Change Password
        </p>
        <div class="panel-block">
          <div class="field">
            <label class="label">Password</label>
            <div class="control">
              <div class="control">
                <input class="input" type="password" v-model="passwordData.password">
              </div>
            </div>
          </div>

          <div class="field">
            <label class="label">Confirm password</label>
            <div class="control">
              <div class="control">
                <input class="input" type="password" v-model="passwordData.password_confirm">
              </div>
            </div>
          </div>

          <div class="field is-grouped">
            <div class="control">
              <button class="button">Update password</button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
  export default {
    name: 'Account',
    data() {
      return {
        passwordData: {
          password: '',
          password_confirm: '',
        }
      }
    },
    created() {
      this.loadProfileStatus()
    },
    methods: {
      async changePassword() {
        if (this.passwordData.password != this.passwordData.password_confirm) {
          this.awn.warning('Password and confirmed password must be the same.')
          return
        }

        this.mitt.emit('spinnerStart')

        const response = await this.$store.dispatch('app/changePassword', this.passwordData)
        const data = await response.json()

        if (response.ok) {
          this.passwordData = {
            password: '',
            password_confirm: '',
          }
          this.awn.success(data.message)
        } else {
          this.awn.warning(data.message.join('<br>'))
        }

        this.mitt.emit('spinnerStop')
      },
      async updateProfileStatus() {
        this.mitt.emit('spinnerStart')

        const response = await this.$store.dispatch('app/saveProfile', {
          public_profile: this.$store.state.app.userProfile.public_profile,
        })

        this.$store.dispatch('app/setPeopleMarkReload', true)

        if (response.ok) {
          this.awn.success('Profile status has been updated.')
        } else {
          this.awn.warning('Something went wrong, try again.')
        }

        this.mitt.emit('spinnerStop')
      },
      async loadProfileStatus() {
        this.mitt.emit('spinnerStart')

        let profileStatus = await this.$store.dispatch('app/fetchProfileStatus')

        this.mitt.emit('spinnerStop')

        this.$store.dispatch('app/setUserProfileStatus', profileStatus)
      },
    },
  }
</script>

<style lang="scss">
  .account-section {
    input[type=checkbox] {
      transform: scale(2);
      margin-left: 0.5rem;
      cursor: pointer;
    }
  }
</style>
