<template>
  <div class="user-profile">
    <h3 class="is-size-3 has-text-weight-bold mb-4">My profile</h3>

    <form class="form-commuse-blocks" @submit.prevent="saveProfile">
      <div class="panel">
        <p class="panel-heading">
          Profile Status
        </p>
        <div class="panel-block">
          <div class="notification is-warning" v-if="!$store.state.app.userProfile.public_profile">
            Your profile is currently set to private and will not show in the people page. To allow users of this platform to view your profile, please check the 'List my information in the people page' checkbox.
          </div>

          <div class="field">
            <label class="label">List my information in the people page</label>
            <div class="control">
              <div class="control">
                <input type="checkbox" v-model="$store.state.app.userProfile.public_profile">
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="panel">
        <p class="panel-heading">
          My Information
        </p>
        <div class="panel-block">
          <div class="field user-profile-image">
            <label class="label">Profile image</label>
            <div class="control">
              <img :src="`${apiUrl}/api/files/get/${$store.state.app.userProfile.image_url}`" v-if="$store.state.app.userProfile.image_url">
              <input ref="userProfileImageInput" type="file" accept=".jpg, .png, .jpeg, .gif" @change="uploadProfileImage()">
              <div class="my-2">
                <button class="button" type="button" @click="openUploadProfileImage()">
                  <img src="@/assets/images/profile_image.svg">
                  Upload new profile image
                </button>
              </div>
            </div>
          </div>

          <ProfileField
            label="Prefix"
            type="short_text"
            v-bind:value="$store.state.app.userProfile.prefix"
            v-on:update:value="$store.state.app.userProfile.prefix = $event"
          ></ProfileField>

          <ProfileField
            label="First Name"
            type="short_text"
            v-bind:value="$store.state.app.userProfile.first_name"
            v-on:update:value="$store.state.app.userProfile.first_name = $event"
          ></ProfileField>

          <ProfileField
            label="Middle Name"
            type="short_text"
            v-bind:value="$store.state.app.userProfile.middle_name"
            v-on:update:value="$store.state.app.userProfile.middle_name = $event"
          ></ProfileField>

          <ProfileField
            label="Last Name"
            type="short_text"
            v-bind:value="$store.state.app.userProfile.last_name"
            v-on:update:value="$store.state.app.userProfile.last_name = $event"
          ></ProfileField>

          <ProfileField
            label="Preferred Pronouns"
            type="short_text"
            v-bind:value="$store.state.app.userProfile.preferred_pronouns"
            v-on:update:value="$store.state.app.userProfile.preferred_pronouns = $event"
          ></ProfileField>

          <ProfileField
            label="Bio"
            type="long_text"
            v-bind:value="$store.state.app.userProfile.bio"
            v-on:update:value="$store.state.app.userProfile.bio = $event"
          ></ProfileField>

          <ProfileField
            :label="customField.title"
            :type="customField.input_type"
            v-bind:value="$store.state.app.userProfile[customField.machine_name]"
            v-on:update:value="$store.state.app.userProfile[customField.machine_name] = $event"
            v-for="customField in myInformationCustomFields"
          ></ProfileField>
        </div>
      </div>

      <div class="panel">
        <p class="panel-heading">
          Contact Information
        </p>
        <div class="panel-block">
          <ProfileField
            label="Phone"
            type="short_text"
            v-bind:value="$store.state.app.userProfile.mobile_phone_number"
            v-on:update:value="$store.state.app.userProfile.mobile_phone_number = $event"
          ></ProfileField>

          <ProfileField
            label="Email"
            type="short_text"
            v-bind:value="$store.state.app.userProfile.email"
            v-on:update:value="$store.state.app.userProfile.email = $event"
          ></ProfileField>
        </div>
      </div>

      <div class="panel" v-for="customGroup in customGroups">
        <p class="panel-heading">
          {{ customGroup.title }}
        </p>
        <div class="panel-block">
          <ProfileField
            :label="customField.title"
            :type="customField.input_type"
            :machine-name="customField.machine_name"
            :metadata="customField.metadata"
            v-bind:value="$store.state.app.userProfile[customField.machine_name]"
            v-on:update:value="$store.state.app.userProfile[customField.machine_name] = $event"
            v-for="customField in customGroup.custom_fields"
          ></ProfileField>
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
  import ProfileField from './ProfileField.vue'

  export default {
    name: 'UserProfile',
    data() {
      return {
        apiUrl: import.meta.env.VITE_API_URL,
        profileStructure: [],
      }
    },
    components: {
      ProfileField,
    },
    created() {
      this.mitt.emit('spinnerStart', 2)
      this.initialDataLoad()
    },
    computed: {
      myInformationCustomFields() {
        return this.profileStructure
          ?.filter((group) => { return group['machine_name'] == 'my_information' })[0]
          ?.custom_fields ?? []
      },
      customGroups() {
        return this.profileStructure
          ?.filter((group) => { return group['machine_name'] != 'my_information' && group['machine_name'] != 'contact_information' })
      },
    },
    methods: {
      async saveProfile() {
        this.mitt.emit('spinnerStart')

        const response = await this.$store.dispatch('app/saveProfile', this.$store.state.app.userProfile)

        this.mitt.emit('spinnerStop')

        if (response.ok) {
          this.awn.success('Your profile has been saved.')
        } else {
          this.awn.warning('Something went wrong, try again.')
        }
      },
      async initialDataLoad() {
        this.loadProfile()
        this.loadProfileStructure()
      },
      openUploadProfileImage() {
        this.$refs.userProfileImageInput.click()
      },
      async uploadProfileImage() {
        if (this.$refs.userProfileImageInput.files[0]) {
          this.mitt.emit('spinnerStart')

          const response = await this.$store.dispatch('app/uploadProfileImage', this.$refs.userProfileImageInput.files[0])

          this.mitt.emit('spinnerStop')

          if (response.ok) {
            const profile = this.$store.state.app.userProfile
            const data = await response.json()
            profile.image_url = data.image
            this.$store.dispatch('app/setUserProfile', profile)
            this.awn.success('Your profile image has been saved.')
          } else {
            this.awn.warning('Something went wrong, try again.')
          }
        }
      },
      async loadProfile() {
        let profile = await this.$store.dispatch('app/fetchProfile')

        this.mitt.emit('spinnerStop')

        if (profile.length === 0) {
          return
        }

        this.$store.dispatch('app/setUserProfile', profile)
      },
      async loadProfileStructure() {
        let profileStructure = await this.$store.dispatch('app/fetchProfileStructure')

        this.profileStructure = profileStructure

        this.mitt.emit('spinnerStop')
      },
    }
  }
</script>

<style lang="scss">
  .user-profile {
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

    input[type=checkbox] {
      transform: scale(2);
      margin-left: 0.5rem;
      cursor: pointer;
    }
  }
</style>
