<template>
  <div class="user-profile">
    <h3 class="is-size-2 mb-4">Your profile</h3>

    <form @submit.prevent="saveProfile">
      <div class="field">
        <label class="label">First name</label>
        <div class="control">
          <input class="input" type="text" v-model="firstName">
        </div>
      </div>

      <div class="field">
        <label class="label">Last name</label>
        <div class="control">
          <input class="input" type="text" v-model="lastName">
        </div>
      </div>

      <div class="field">
        <label class="label">Short bio</label>
        <div class="control">
          <div class="control">
            <textarea class="textarea" v-model="shortBio"></textarea>
          </div>
        </div>
      </div>

      <div class="field">
        <label class="label">Full bio</label>
        <div class="control">
          <div class="control">
            <textarea class="textarea" v-model="fullBio"></textarea>
          </div>
        </div>
      </div>

      <div class="field">
        <label class="label">Continent</label>
        <div class="control">
          <input class="input" type="text" v-model="continent">
        </div>
      </div>

      <div class="field">
        <label class="label">Country</label>
        <div class="control">
          <input class="input" type="text" v-model="country">
        </div>
      </div>

      <div class="field">
        <label class="label">City</label>
        <div class="control">
          <input class="input" type="text" v-model="city">
        </div>
      </div>

      <div class="field">
        <label class="label">Twitter link</label>
        <div class="control">
          <input class="input" type="text" v-model="twitterLink">
        </div>
      </div>

      <div class="field">
        <label class="label">LinkedIn link</label>
        <div class="control">
          <input class="input" type="text" v-model="linkedinLink">
        </div>
      </div>

      <div class="field">
        <label class="label">Mastodon link</label>
        <div class="control">
          <input class="input" type="text" v-model="mastodonLink">
        </div>
      </div>

      <div class="field">
        <div class="control">
          <label class="checkbox">
            <input type="checkbox" v-model="publicProfile">
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
  export default {
    name: 'UserProfile',
    data() {
      return {
        firstName: '',
        lastName: '',
        shortBio: '',
        fullBio: '',
        continent: '',
        country: '',
        city: '',
        publicProfile: false,
        twitterLink: '',
        linkedinLink: '',
        mastodonLink: '',
      }
    },
    methods: {
      saveProfile() {
        const formData = {
          firstName: this.firstName,
          lastName: this.lastName,
          shortBio: this.shortBio,
          fullBio: this.fullBio,
          continent: this.continent,
          country: this.country,
          city: this.city,
          publicProfile: this.publicProfile,
          twitterLink: this.twitterLink,
          linkedinLink: this.linkedinLink,
          mastodonLink: this.mastodonLink,
        }

        console.log(formData)
      }
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
  }
</style>
