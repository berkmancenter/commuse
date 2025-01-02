<template>
  <div class="user-profile">
    <form class="commuse-blocks" @submit.prevent="saveProfile">
      <div class="user-profile-header is-flex is-align-items-center">
        <h3 class="is-size-3 has-text-weight-bold">Edit profile</h3>
        <ActionButton class="ml-2" buttonText="Save" :icon="saveIcon" :button="true" :disabled="loading" :working="savingProfile"></ActionButton>
        <router-link :to="{ name: 'people.details', params: { id: $store.state.user.userProfile.id } }" v-if="$store.state.user.userProfile.id">
          <ActionButton class="ml-2" buttonText="Show profile" :icon="userIcon" :button="true" :disabled="loading"></ActionButton>
        </router-link>
      </div>

      <SkeletonPatternLoader :loading="loading">
        <template v-slot:content>
          <div class="user-profile-form-fields">
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
                    <img :src="`${apiUrl}/api/files/get/${$store.state.user.userProfile.image_url}`" v-if="$store.state.user.userProfile.image_url">
                    <input ref="userProfileImageInput" type="file" accept=".jpg, .png, .jpeg, .gif" @change="uploadProfileImage()">
                    <div class="my-2">
                      <div>
                        <ActionButton buttonText="Upload new profile image" :icon="uploadProfileImageIcon" @click="openUploadProfileImage()"></ActionButton>
                      </div>
                      <div>
                        <ActionButton class="mt-2" buttonText="Remove current image" :icon="removeImage" @click="removeProfileImage()"></ActionButton>
                      </div>
                    </div>
                  </div>
                </div>

                <CustomField
                  label="Prefix"
                  type="short_text"
                  v-bind:value="$store.state.user.userProfile.prefix"
                  v-on:update:value="$store.state.user.userProfile.prefix = $event"
                  :ref="el => fields['prefix'] = el"
                  :storeObject="$store.state.user.userProfile"
                ></CustomField>

                <CustomField
                  label="First name"
                  type="short_text"
                  v-bind:value="$store.state.user.userProfile.first_name"
                  v-on:update:value="$store.state.user.userProfile.first_name = $event"
                  :ref="el => fields['first_name'] = el"
                  :storeObject="$store.state.user.userProfile"
                ></CustomField>

                <CustomField
                  label="Middle name"
                  type="short_text"
                  v-bind:value="$store.state.user.userProfile.middle_name"
                  v-on:update:value="$store.state.user.userProfile.middle_name = $event"
                  :ref="el => fields['middle_name'] = el"
                  :storeObject="$store.state.user.userProfile"
                ></CustomField>

                <CustomField
                  label="Last name"
                  type="short_text"
                  v-bind:value="$store.state.user.userProfile.last_name"
                  v-on:update:value="$store.state.user.userProfile.last_name = $event"
                  :ref="el => fields['last_name'] = el"
                  :storeObject="$store.state.user.userProfile"
                ></CustomField>

                <CustomField
                  label="Preferred pronouns"
                  type="short_text"
                  v-bind:value="$store.state.user.userProfile.preferred_pronouns"
                  v-on:update:value="$store.state.user.userProfile.preferred_pronouns = $event"
                  :ref="el => fields['preferred_pronouns'] = el"
                  :storeObject="$store.state.user.userProfile"
                ></CustomField>

                <CustomField
                  :label="customField.title"
                  :description="customField.description"
                  :type="customField.input_type"
                  v-bind:value="$store.state.user.userProfile[customField.machine_name]"
                  v-on:update:value="$store.state.user.userProfile[customField.machine_name] = $event"
                  :ref="el => fields[customField.machine_name] = el"
                  :storeObject="$store.state.user.userProfile"
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
                  v-bind:value="$store.state.user.userProfile.mobile_phone_number"
                  v-on:update:value="$store.state.user.userProfile.mobile_phone_number = $event"
                  :ref="el => fields['mobile_phone_number'] = el"
                  :storeObject="$store.state.user.userProfile"
                ></CustomField>

                <CustomField
                  label="Email"
                  type="short_text"
                  v-bind:value="$store.state.user.userProfile.email"
                  v-on:update:value="$store.state.user.userProfile.email = $event"
                  :ref="el => fields['email'] = el"
                  :storeObject="$store.state.user.userProfile"
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
                  :value="$store.state.user.userProfile[customField.machine_name]"
                  @update:value="$store.state.user.userProfile[customField.machine_name] = $event"
                  :ref="el => fields[customField.machine_name] = el"
                  :storeObject="$store.state.user.userProfile"
                  v-for="customField in customGroup.custom_fields"
                ></CustomField>
              </div>
            </div>
          </div>
        </template>

        <template v-slot:skeleton>
          <div class="ssc-card ssc-wrapper mb-4">
            <div class="mb-5 ssc-head-line"></div>
            <div class="mb ssc-line w-20 mb"></div>
            <div class="ssc-square w-20 mb"></div>
            <div class="mb ssc-head-line w-40 mb"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="mb ssc-head-line"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="mb ssc-head-line"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="mb ssc-head-line"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="mb ssc-head-line"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="ssc-square"></div>
          </div>

          <div class="ssc-card ssc-wrapper mb-4" v-for="n in 2" :key="n">
            <div class="mb-5 ssc-head-line"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="mb ssc-head-line"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="mb ssc-head-line"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="mb ssc-head-line"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="ssc-square"></div>
          </div>
        </template>
      </SkeletonPatternLoader>
    </form>
  </div>
</template>

<script>
  import CustomField from '@/components/CustomFields/CustomField.vue'
  import ActionButton from '@/components/Shared/ActionButton.vue'
  import SkeletonPatternLoader from '@/components/Shared/SkeletonPatternLoader.vue'
  import saveIcon from '@/assets/images/save.svg'
  import userIcon from '@/assets/images/user.svg'
  import uploadProfileImageIcon from '@/assets/images/profile_image.svg'
  import removeImage from '@/assets/images/remove_image.svg'

  export default {
    name: 'UserProfile',
    data() {
      return {
        apiUrl: import.meta.env.VITE_API_URL,
        profileLoaded: false,
        profileStructureLoaded: false,
        profileStructure: [],
        saveIcon,
        userIcon,
        uploadProfileImageIcon,
        removeImage,
        fields: {},
        loading: true,
        savingProfile: false,
      }
    },
    components: {
      CustomField,
      ActionButton,
      SkeletonPatternLoader,
    },
    created() {
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

        this.savingProfile = true
        this.$store.state.user.userProfile['update_search_index'] = true
        const response = await this.$store.dispatch('user/saveProfile', this.$store.state.user.userProfile)

        this.$store.dispatch('people/setPeopleMarkReload', true)

        this.awn.success(response.message)
        this.savingProfile = false
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
          let profileId = this.$route.params.id

          if (!profileId) {
            profileId = 'current'
          }

          try {
            const response = await this.$store.dispatch('user/uploadProfileImage', { 
              file: this.$refs.userProfileImageInput.files[0],
              id: profileId,
            })

            const profile = this.$store.state.user.userProfile
            const data = await response
            profile.image_url = data.image
            this.$store.dispatch('user/setUserProfile', profile)
            this.$store.dispatch('people/setPeopleMarkReload', true)
            this.awn.success('Profile image has been saved.')
          } catch (error) {
            this.awn.warning('Something went wrong, try again.')
            return
          }
        }
      },
      async removeProfileImage() {
        let profileId = this.$route.params.id

        if (!profileId) {
          profileId = 'current'
        }

        try {
          const response = await this.$store.dispatch('user/removeProfileImage', { 
            file: this.$refs.userProfileImageInput.files[0],
            id: profileId,
          })

          const profile = this.$store.state.user.userProfile
          await response
          profile.image_url = ''
          this.$store.dispatch('user/setUserProfile', profile)
          this.$store.dispatch('people/setPeopleMarkReload', true)
          this.awn.success('Profile image has been removed.')
        } catch (error) {
          this.awn.warning('Something went wrong, try again.')
          return
        }
      },
      async loadProfile() {
        let profileId = this.$route.params.id

        if (!profileId) {
          profileId = 'current'
        }

        let profile = await this.$store.dispatch('user/fetchProfile', profileId)
        this.profileLoaded = true

        if (profile.length === 0) {
          return
        }

        this.$store.dispatch('user/setUserProfile', profile)

        if (this.profileStructureLoaded) {
          this.loading = false
        }
      },
      async loadProfileStructure() {
        let profileStructure = await this.$store.dispatch('user/fetchProfileStructure')

        this.profileStructure = profileStructure
        this.profileStructureLoaded = true

        if (this.profileLoaded) {
          this.loading = false
        }
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

      @media all and (max-width: 700px) {
        padding-top: 7rem;
      }
    }

    .user-profile-image {
      img {
        max-width: 200px;
      }

      input {
        display: none;
      }
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

      @media all and (max-width: 700px) {
        display: block !important;

        > button {
          margin-left: 0 !important;
        }
      }
    }

    .user-profile-subtitle {
      font-size: 0.8em;
      font-weight: normal;
      color: #800000;
      margin: 0.5rem 0;
    }

    .ssc {
      padding-top: 5rem;
    }
  }
</style>
