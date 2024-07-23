<template>
  <div class="user-profile" v-if="profileLoaded && profileStructureLoaded">
    <form class="form-commuse-blocks" @submit.prevent="saveProfile">
      <div class="user-profile-header is-flex is-align-items-center">
        <h3 class="is-size-3 has-text-weight-bold">Edit profile</h3>
        <ActionButton class="ml-2" buttonText="Save" :icon="saveIcon" :button="true"></ActionButton>
      </div>

      <div class="user-profile-form-fields">
        <div class="panel">
          <p class="panel-heading">
            Profile Status
          </p>
          <div class="panel-block">
            <div class="notification is-warning" v-if="!$store.state.app.userProfile.public_profile">
              Your profile is currently set to private and will not show in the people page. To allow users of this platform to view your profile, please check the 'Make my People Portal profile visible to other users of the People Portal' checkbox.
            </div>

            <div class="field">
              <label class="label">Make my People Portal profile visible to other users of the People Portal</label>
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
            <div class="user-profile-subtitle"
                 v-if="profileStructure?.filter((group) => { return group['machine_name'] != 'my_information' }).description"
                 v-html="profileStructure?.filter((group) => { return group['machine_name'] != 'my_information' }).description"
            >
            </div>
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

            <CustomField
              label="Prefix"
              type="short_text"
              v-bind:value="$store.state.app.userProfile.prefix"
              v-on:update:value="$store.state.app.userProfile.prefix = $event"
              :ref="el => fields['prefix'] = el"
              :storeObject="$store.state.app.userProfile"
            ></CustomField>

            <CustomField
              label="First name"
              type="short_text"
              v-bind:value="$store.state.app.userProfile.first_name"
              v-on:update:value="$store.state.app.userProfile.first_name = $event"
              :ref="el => fields['first_name'] = el"
              :storeObject="$store.state.app.userProfile"
            ></CustomField>

            <CustomField
              label="Middle name"
              type="short_text"
              v-bind:value="$store.state.app.userProfile.middle_name"
              v-on:update:value="$store.state.app.userProfile.middle_name = $event"
              :ref="el => fields['middle_name'] = el"
              :storeObject="$store.state.app.userProfile"
            ></CustomField>

            <CustomField
              label="Last name"
              type="short_text"
              v-bind:value="$store.state.app.userProfile.last_name"
              v-on:update:value="$store.state.app.userProfile.last_name = $event"
              :ref="el => fields['last_name'] = el"
              :storeObject="$store.state.app.userProfile"
            ></CustomField>

            <CustomField
              label="Preferred pronouns"
              type="short_text"
              v-bind:value="$store.state.app.userProfile.preferred_pronouns"
              v-on:update:value="$store.state.app.userProfile.preferred_pronouns = $event"
              :ref="el => fields['preferred_pronouns'] = el"
              :storeObject="$store.state.app.userProfile"
            ></CustomField>

            <CustomField
              :label="customField.title"
              :description="customField.description"
              :type="customField.input_type"
              v-bind:value="$store.state.app.userProfile[customField.machine_name]"
              v-on:update:value="$store.state.app.userProfile[customField.machine_name] = $event"
              :ref="el => fields[customField.machine_name] = el"
              :storeObject="$store.state.app.userProfile"
              v-for="customField in myInformationCustomFields"
            ></CustomField>
          </div>
        </div>

        <div class="panel">
          <p class="panel-heading">
            Contact Information
            <div class="user-profile-subtitle"
                 v-if="profileStructure?.filter((group) => { return group['machine_name'] != 'contact_information' }).description"
                 v-html="profileStructure?.filter((group) => { return group['machine_name'] != 'contact_information' }).description"
            >
            </div>
          </p>
          <div class="panel-block">
            <CustomField
              label="Phone"
              type="short_text"
              v-bind:value="$store.state.app.userProfile.mobile_phone_number"
              v-on:update:value="$store.state.app.userProfile.mobile_phone_number = $event"
              :ref="el => fields['mobile_phone_number'] = el"
              :storeObject="$store.state.app.userProfile"
            ></CustomField>

            <CustomField
              label="Email"
              type="short_text"
              v-bind:value="$store.state.app.userProfile.email"
              v-on:update:value="$store.state.app.userProfile.email = $event"
              :ref="el => fields['email'] = el"
              :storeObject="$store.state.app.userProfile"
            ></CustomField>
          </div>
        </div>

        <div class="panel" v-for="customGroup in customGroups">
          <p class="panel-heading">
            {{ customGroup.title }}
            <div class="user-profile-subtitle"
                 v-if="customGroup.description"
                 v-html="customGroup.description"
            >
            </div>
          </p>
          <div class="panel-block">
            <CustomField
              :label="customField.title"
              :description="customField.description"
              :type="customField.input_type"
              :machine-name="customField.machine_name"
              :metadata="customField.metadata"
              :field-data="customField"
              :value="$store.state.app.userProfile[customField.machine_name]"
              @update:value="$store.state.app.userProfile[customField.machine_name] = $event"
              :ref="el => fields[customField.machine_name] = el"
              :storeObject="$store.state.app.userProfile"
              v-for="customField in customGroup.custom_fields"
            ></CustomField>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
  import CustomField from '@/components/CustomFields/CustomField.vue'
  import StickyElement from 'vue-sticky-element'
  import ActionButton from '@/components/Shared/ActionButton.vue'
  import saveIcon from '@/assets/images/save.svg'

  export default {
    name: 'UserProfile',
    data() {
      return {
        apiUrl: import.meta.env.VITE_API_URL,
        profileLoaded: false,
        profileStructureLoaded: false,
        profileStructure: [],
        saveIcon,
        fields: {},
      }
    },
    components: {
      CustomField,
      StickyElement,
      ActionButton,
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
          ?.filter((group) => { return group['machine_name'] != 'my_information' && group['machine_name'] != 'contact_information' && group['machine_name'] != 'multi_fields_group' })
      },
    },
    methods: {
      async saveProfile() {
        if (this.validate() === false) {
          return
        }

        this.mitt.emit('spinnerStart')

        const response = await this.$store.dispatch('app/saveProfile', this.$store.state.app.userProfile)

        this.mitt.emit('spinnerStop')

        this.$store.dispatch('app/setPeopleMarkReload', true)

        if (response.ok) {
          this.awn.success('Profile has been saved.')
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

          let profileId = this.$route.params.id

          if (!profileId) {
            profileId = 'current'
          }

          const response = await this.$store.dispatch('app/uploadProfileImage', { 
            file: this.$refs.userProfileImageInput.files[0],
            id: profileId,
          })

          this.mitt.emit('spinnerStop')

          if (response.ok) {
            const profile = this.$store.state.app.userProfile
            const data = await response.json()
            profile.image_url = data.image
            this.$store.dispatch('app/setUserProfile', profile)
            this.$store.dispatch('app/setPeopleMarkReload', true)
            this.awn.success('Profile image has been saved.')
          } else {
            this.awn.warning('Something went wrong, try again.')
          }
        }
      },
      async loadProfile() {
        let profileId = this.$route.params.id

        if (!profileId) {
          profileId = 'current'
        }

        let profile = await this.$store.dispatch('app/fetchProfile', profileId)
        this.profileLoaded = true

        this.mitt.emit('spinnerStop')

        if (profile.length === 0) {
          return
        }

        this.$store.dispatch('app/setUserProfile', profile)
      },
      async loadProfileStructure() {
        let profileStructure = await this.$store.dispatch('app/fetchProfileStructure')

        this.profileStructure = profileStructure
        this.profileStructureLoaded = true

        this.mitt.emit('spinnerStop')
      },
      validate() {
        const errorMessages = []
        let valid = true

        for (const [key, el] of Object.entries(this.fields)) {
          const result = el.validate()

          if (result.status === false) {
            valid = false
            errorMessages.push(result.message)
          }
        }

        if (valid === false) {
          this.awn.warning(errorMessages.join('<br>'))
        }

        return valid
      },
    },
    watch: {
      '$route.params.id': function() {
        this.loadProfile()
      },
    },
  }
</script>

<style lang="scss">
  .user-profile {
    label {
      user-select: none;
    }

    .user-profile-form-fields {
      position: relative;
      padding-top: 5rem;
      z-index: 1;
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

    .user-profile-header {
      position: fixed;
      background-color: #ffffff;
      width: 100%;
      z-index: 2;
      margin-top: calc(-2rem + 8px);
      padding: 1rem 0;
      padding-left: 1rem;
      border-bottom: 2px solid var(--greyish-color);
    }

    .user-profile-subtitle {
      font-size: 0.8em;
      font-weight: normal;
      color: #800000;
      margin: 0.5rem 0;
    }
  }
</style>
