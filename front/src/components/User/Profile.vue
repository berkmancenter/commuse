<template>
  <div class="user-profile mb-4">
    <h3 class="is-size-2 mb-4">Your profile</h3>

    <form @submit.prevent="saveProfile">
      <div class="field user-profile-image">
        <label class="label">Profile image</label>
        <div class="control">
          <img :src="`${apiUrl}/api/files/get/${$store.state.app.userProfile.imageUrl}`">
          <input ref="userProfileImageInput" type="file" accept=".jpg, .png, .jpeg, .gif" @change="uploadProfileImage()">
          <div class="my-2">
            <button class="button" type="button" @click="openUploadProfileImage()">
              <img src="@/assets/images/profile_image.svg">
              Upload new profile image
            </button>
          </div>
        </div>
      </div>

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
      return {
        apiUrl: import.meta.env.VITE_API_URL,
      }
    },
    created() {
      this.initialDataLoad()
    },
    methods: {
      async saveProfile() {
        const response = await this.$store.dispatch('app/saveProfile', keysToSnakeCase(this.$store.state.app.userProfile))

        if (response.ok) {
          this.awn.success('Your profile has been saved.')
        } else {
          this.awn.warning('Something went wrong, try again.')
        }
      },
      async initialDataLoad() {
        let profile = await this.$store.dispatch('app/fetchProfile')

        profile = keysToCamelCase(profile)

        this.$store.dispatch('app/setUserProfile', profile)
      },
      openUploadProfileImage() {
        this.$refs.userProfileImageInput.click()
      },
      async uploadProfileImage() {
        if (this.$refs.userProfileImageInput.files[0]) {
          const response = await this.$store.dispatch('app/uploadProfileImage', this.$refs.userProfileImageInput.files[0])

          if (response.ok) {
            const profile = this.$store.state.app.userProfile
            const data = await response.json()
            profile.imageUrl = data.image
            this.$store.dispatch('app/setUserProfile', profile)
            this.awn.success('Your profile image has been saved.')
          } else {
            this.awn.warning('Something went wrong, try again.')
          }
        }
      },
    }
  }
</script>

<style lang="scss">
  .user-profile {
    input[type=text],
    textarea {
      width: 100%;
      max-width: 30%;
      min-width: unset;

      @media all and (max-width: 1600px) {
        max-width: 40%;
      }

      @media all and (max-width: 1300px) {
        max-width: 50%;
      }

      @media all and (max-width: 900px) {
        max-width: 70%;
      }

      @media all and (max-width: 700px) {
        max-width: 100%;
      }
    }

    label {
      user-select: none;
    }

    .user-profile-image {
      img {
        max-width: 200px;
      }

      button {
        img {
          height: 1rem;
          width: 1rem;
          margin-right: 0.5rem;
        }
      }

      input {
        display: none;
      }
    }
  }
</style>
