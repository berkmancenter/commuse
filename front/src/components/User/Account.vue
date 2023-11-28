<template>
  <div class="account-section">
    <h3 class="is-size-3 has-text-weight-bold mb-4">Account</h3>

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
    },
  }
</script>

<style lang="scss">
  .account-section {}
</style>
