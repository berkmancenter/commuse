<template>
  <div class="account-section">
    <h3 class="is-size-3 has-text-weight-bold mb-4">Account settings</h3>

    <SkeletonPatternLoader :loading="loading">
      <template v-slot:content>
        <form class="commuse-blocks mb-4" @submit.prevent="updateProfileStatus">
          <div class="panel">
            <p class="panel-heading">
              Profile status
            </p>
            <div class="panel-block">
              <div class="notification is-warning" v-if="!$store.state.user.userProfile.public_profile">
                {{ $store.state.systemSettings?.publicSystemSettings?.PublicProfileWarningInAccountSettings?.value }}
              </div>

              <div class="field">
                <label class="label" for="account-section-public-profile">{{ $store.state.systemSettings.publicSystemSettings?.PublicProfileCheckboxLabel?.value }}</label>
                <div class="control">
                  <div class="control">
                    <input type="checkbox" id="account-section-public-profile" v-model="$store.state.user.userProfile.public_profile">
                  </div>
                </div>
              </div>

              <div class="field is-grouped">
                <div class="control">
                  <ActionButton buttonText="Update profile status" :button="true" :working="savingProfileStatus"></ActionButton>
                </div>
              </div>
            </div>
          </div>
        </form>

        <form class="commuse-blocks" @submit.prevent="changePassword">
          <div class="panel">
            <p class="panel-heading">
              Change password
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
                  <ActionButton buttonText="Update password" :button="true" :working="savingPassword"></ActionButton>
                </div>
              </div>
            </div>
          </div>
        </form>
      </template>

      <template v-slot:skeleton>
        <div class="commuse-blocks">
          <div class="ssc-card ssc-wrapper mb-4" v-for="n in 2" :key="n">
            <div class="ssc-head-line mb-4"></div>
            <div class="ssc-square"></div>
          </div>
        </div>
      </template>
    </SkeletonPatternLoader>
  </div>
</template>

<script>
  import SkeletonPatternLoader from '@/components/Shared/SkeletonPatternLoader.vue'
  import ActionButton from '@/components/Shared/ActionButton.vue'

  export default {
    name: 'Account',
    components: {
      SkeletonPatternLoader,
      ActionButton,
    },
    data() {
      return {
        passwordData: {
          password: '',
          password_confirm: '',
        },
        loading: true,
        savingPassword: false,
        savingProfileStatus: false,
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

        this.savingPassword = true

        try {
          const response = await this.$store.dispatch('user/changePassword', this.passwordData)

          this.passwordData = {
            password: '',
            password_confirm: '',
          }

          this.awn.success(response.message)
        } catch (error) {
          this.awn.warning(error.message)
          return
        } finally {
          this.savingPassword = false
        }
      },
      async updateProfileStatus() {
        this.savingProfileStatus = true

        const response = await this.$store.dispatch('user/savePublicProfileStatus', {
          public_profile: this.$store.state.user.userProfile.public_profile,
        })

        this.$store.dispatch('people/setPeopleMarkReload', true)

        this.awn.success(response.message)

        this.savingProfileStatus = false
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
