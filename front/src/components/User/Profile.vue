<template>
  <div class="user-profile">
    <h3 class="is-size-2 mb-4">Your profile</h3>

    <form @submit.prevent="saveProfile">
      <div class="field">
        <label class="label">First name</label>
        <div class="control">
          <input class="input" type="text" v-model="$store.state.app.userProfile.firstName">
        </div>
      </div>

      <div class="field">
        <label class="label">Last name</label>
        <div class="control">
          <input class="input" type="text" v-model="$store.state.app.userProfile.lastName">
        </div>
      </div>

      <div class="field">
        <label class="label">Short bio</label>
        <div class="control">
          <div class="control">
            <textarea class="textarea" v-model="$store.state.app.userProfile.shortBio"></textarea>
          </div>
        </div>
      </div>

      <div class="field">
        <label class="label">Full bio</label>
        <div class="control">
          <div class="control">
            <textarea class="textarea" v-model="$store.state.app.userProfile.bio"></textarea>
          </div>
        </div>
      </div>

      <div class="field">
        <label class="label">Continent</label>
        <div class="control">
          <input class="input" type="text" v-model="$store.state.app.userProfile.continent">
        </div>
      </div>

      <div class="field">
        <label class="label">Country</label>
        <div class="control">
          <input class="input" type="text" v-model="$store.state.app.userProfile.country">
        </div>
      </div>

      <div class="field">
        <label class="label">City</label>
        <div class="control">
          <input class="input" type="text" v-model="$store.state.app.userProfile.city">
        </div>
      </div>

      <div class="field">
        <label class="label">Twitter link</label>
        <div class="control">
          <input class="input" type="text" v-model="$store.state.app.userProfile.twitterUrl">
        </div>
      </div>

      <div class="field">
        <label class="label">LinkedIn link</label>
        <div class="control">
          <input class="input" type="text" v-model="$store.state.app.userProfile.linkedinUrl">
        </div>
      </div>

      <div class="field">
        <label class="label">Mastodon link</label>
        <div class="control">
          <input class="input" type="text" v-model="$store.state.app.userProfile.mastodonUrl">
        </div>
      </div>

      <div class="field">
        <div class="control">
          <label class="checkbox">
            <input type="checkbox" v-model="$store.state.app.userProfile.publicProfile">
            Public profile
          </label>
        </div>
      </div>

      <div class="field is-grouped">
        <div class="control">
          <button class="button">Save</button>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
  import { keysToCamelCase, keysToSnakeCase } from '@/lib/keysConverting.js'

  export default {
    name: 'UserProfile',
    data() {
      return {}
    },
    created() {
      this.initialDataLoad()
    },
    methods: {
      async saveProfile() {
        const response = await this.$store.dispatch('app/saveProfile', keysToSnakeCase(this.$store.state.app.userProfile))

        if (response.ok) {
          this.awn.success('User profile has been saved.')
        } else {
          this.awn.warning('Something went wrong, try again.')
        }
      },
      async initialDataLoad() {
        let profile = await this.$store.dispatch('app/fetchProfile')

        profile = keysToCamelCase(profile)

        console.log(profile)

        this.$store.dispatch('app/setUserProfile', profile)
      },
    }
  }
</script>

<style lang="scss">
  .user-profile {
    input[type=text],
    textarea {
      width: 100%;
      max-width: 400px;
      min-width: unset;
    }

    label {
      user-select: none;
    }
  }
</style>
