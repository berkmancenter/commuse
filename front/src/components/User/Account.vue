<template>
  <div class="account-section">
    <h3 class="is-size-3 has-text-weight-bold mb-4">Account settings</h3>


    <div v-if="!loading">
      <form class="form-commuse-blocks mb-4" @submit.prevent="changePassword">
        <div class="panel">
          <p class="panel-heading">
            People Portal Profile Status
          </p>
          <div class="panel-block">
            <div class="notification is-warning" v-if="!$store.state.user.userProfile.public_profile">
              {{ $store.state.systemSettings?.publicSystemSettings?.PublicProfileWarningInAccountSettings?.value }}
            </div>

            <div class="field">
              <label class="label" for="account-section-public-profile">{{ $store.state.systemSettings.publicSystemSettings?.PublicProfileCheckboxLabel?.value }}</label>
              <div class="control">
                <div class="control">
                  <input type="checkbox" id="account-section-public-profile" v-model="$store.state.user.userProfile.public_profile" @change="updateProfileStatus()">
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
              <label class="label" for="account-section-password">Password</label>
              <div class="control">
                <div class="control">
                  <input class="input" id="account-section-password" type="password" v-model="passwordData.password">
                </div>
              </div>
            </div>

            <div class="field">
              <label class="label" for="account-section-password-confirm">Confirm password</label>
              <div class="control">
                <div class="control">
                  <input class="input" id="account-section-password-confirm" type="password" v-model="passwordData.password_confirm">
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

    <div class="ssc form-commuse-blocks" v-if="loading">
      <div class="ssc-card ssc-wrapper mb-4" v-for="n in 2" :key="n">
        <div class="ssc-head-line mb-4"></div>
        <div class="ssc-square"></div>
      </div>
    </div>
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
        },
        loading: true,
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

        const response = await this.$store.dispatch('user/changePassword', this.passwordData)
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
      },
      async updateProfileStatus() {
        const response = await this.$store.dispatch('user/savePublicProfileStatus', {
          public_profile: this.$store.state.user.userProfile.public_profile,
        })

        this.$store.dispatch('people/setPeopleMarkReload', true)

        if (response.ok) {
          this.awn.success('Profile status has been updated.')
        } else {
          this.awn.warning('Something went wrong, try again.')
        }
      },
      async loadProfileStatus() {
        let profileStatus = await this.$store.dispatch('user/fetchProfileStatus')

        this.$store.dispatch('user/setUserProfileStatus', profileStatus)

        this.loading = false
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
