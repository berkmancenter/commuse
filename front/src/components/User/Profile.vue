<template>
  <div class="user-profile">
    <h3 class="is-size-3 has-text-weight-bold mb-4">My profile</h3>

    <form @submit.prevent="saveProfile">
      <nav class="panel">
        <p class="panel-heading">
          Profile status
        </p>
        <div class="panel-block">
          <div class="notification is-warning" v-if="!$store.state.app.userProfile.publicProfile">
            Your profile is currently set to private and will not show in the people page. To allow users of this platform to view your profile, please check the 'List my information in the people page.' checkbox.
          </div>

          <div class="field">
            <label class="label">List my information in the people page</label>
            <div class="control">
              <div class="control">
                <input type="checkbox" v-model="$store.state.app.userProfile.publicProfile">
              </div>
            </div>
          </div>
        </div>
      </nav>

      <nav class="panel">
        <p class="panel-heading">
          My Information
        </p>
        <div class="panel-block">
          <div class="field user-profile-image">
            <label class="label">Profile image</label>
            <div class="control">
              <img :src="`${apiUrl}/api/files/get/${$store.state.app.userProfile.imageUrl}`" v-if="$store.state.app.userProfile.imageUrl">
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
            <label class="label">Prefix</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.prefix">
            </div>
          </div>

          <div class="field">
            <label class="label">First name</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.firstName">
            </div>
          </div>

          <div class="field">
            <label class="label">Middle name</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.middleName">
            </div>
          </div>

          <div class="field">
            <label class="label">Last name</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.lastName">
            </div>
          </div>

          <div class="field">
            <label class="label">Preferred Name</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.preferredName">
            </div>
          </div>

          <div class="field">
            <label class="label">Preferred Pronouns</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.preferredPronouns">
            </div>
          </div>

          <div class="field">
            <label class="label">Bio</label>
            <div class="control">
              <div class="control">
                <textarea class="textarea" v-model="$store.state.app.userProfile.bio"></textarea>
              </div>
            </div>
          </div>
        </div>
      </nav>

      <nav class="panel">
        <p class="panel-heading">
          Contact Information
        </p>
        <div class="panel-block">
          <div class="field">
            <label class="label">Mobile Phone Number</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.mobilePhoneNumber">
            </div>
          </div>

          <div class="field">
            <label class="label">Email</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.email">
            </div>
          </div>
        </div>
      </nav>

      <nav class="panel">
        <p class="panel-heading">
          BKC Affiliation
        </p>
        <div class="panel-block">
          <div class="box" v-for="(affiliation, index) in $store.state.app.userProfile.affiliation">
            <span title="Remove item" @click="removeAffiliation(index)"><Icon :src="minusIcon" /></span>

            <div class="field">
              <label class="label">Type of affiliation</label>
              <div class="control">
                <div class="control">
                  <VueMultiselect
                    id="affiliation"
                    v-model="affiliation.position"
                    :multiple="true"
                    :options="affiliationOptions"
                    placeholder="Select"
                  >
                  </VueMultiselect>
                </div>
              </div>

              <label class="label">From</label>
              <div class="control">
                <div class="control">
                  <VueMultiselect
                    id="affiliation"
                    v-model="affiliation.from"
                    :multiple="false"
                    :options="affiliationYearsOptions"
                    placeholder="Select"
                  >
                  </VueMultiselect>
                </div>
              </div>

              <label class="label">To</label>
              <div class="control">
                <div class="control">
                  <VueMultiselect
                    id="affiliation"
                    v-model="affiliation.to"
                    :multiple="false"
                    :options="affiliationYearsOptions"
                    placeholder="Select"
                  >
                  </VueMultiselect>
                </div>
              </div>
            </div>
          </div>

          <span title="Add more" @click="addEmptyAffiliation"><Icon :src="addIcon" /></span>
        </div>
      </nav>

      <nav class="panel">
        <p class="panel-heading">
          Interests
        </p>
        <div class="panel-block">
          <div class="field">
            <label class="label">Interested In</label>
            <div class="control">
              <div class="control">
                <VueMultiselect
                  id="interestedIn"
                  v-model="$store.state.app.userProfile.interestedIn"
                  :multiple="true"
                  :taggable="true"
                  :options="interests"
                  tag-placeholder="Add"
                  placeholder="Search or add new"
                  @tag="addProfilePropertyOption"
                >
                </VueMultiselect>
              </div>
            </div>
          </div>

          <div class="field">
            <label class="label">Knowledgeable In</label>
            <div class="control">
              <div class="control">
                <VueMultiselect
                  id="knowledgeableIn"
                  v-model="$store.state.app.userProfile.knowledgeableIn"
                  :multiple="true"
                  :taggable="true"
                  :options="[]"
                  tag-placeholder="Add"
                  placeholder="Search or add new"
                  @tag="addProfilePropertyOption"
                >
                </VueMultiselect>
              </div>
            </div>
          </div>

          <div class="field">
            <label class="label">Working Groups</label>
            <div class="control">
              <div class="control">
                <VueMultiselect
                  id="workingGroups"
                  v-model="$store.state.app.userProfile.workingGroups"
                  :multiple="true"
                  :taggable="true"
                  :options="[]"
                  tag-placeholder="Add"
                  placeholder="Search or add new"
                  @tag="addProfilePropertyOption"
                >
                </VueMultiselect>
              </div>
            </div>
          </div>

          <div class="field">
            <label class="label">Projects</label>
            <div class="control">
              <div class="control">
                <VueMultiselect
                  id="projects"
                  v-model="$store.state.app.userProfile.projects"
                  :multiple="true"
                  :taggable="true"
                  :options="[]"
                  tag-placeholder="Add"
                  placeholder="Search or add new"
                  @tag="addProfilePropertyOption"
                >
                </VueMultiselect>
              </div>
            </div>
          </div>
        </div>
      </nav>

      <nav class="panel">
        <p class="panel-heading">
          Online Presence
        </p>
        <div class="panel-block">
          <div class="field">
            <label class="label">Website link</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.websiteLink">
            </div>
          </div>

          <div class="field">
            <label class="label">Facebook link</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.facebookLink">
            </div>
          </div>

          <div class="field">
            <label class="label">Twitter link</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.twitterLink">
            </div>
          </div>

          <div class="field">
            <label class="label">Mastodon link</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.mastodonLink">
            </div>
          </div>

          <div class="field">
            <label class="label">Instagram link</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.instagramLink">
            </div>
          </div>

          <div class="field">
            <label class="label">Snapchat link</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.snapchatLink">
            </div>
          </div>

          <div class="field">
            <label class="label">LinkedIn link</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.linkedinLink">
            </div>
          </div>

          <div class="field">
            <label class="label">Other link</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.otherLink">
            </div>
          </div>
        </div>
      </nav>

      <nav class="panel">
        <p class="panel-heading">
          Location Information
        </p>
        <div class="panel-block">
          <p class="mb-4">We ask for this information so that we can let you know of any events in your locality and tailor the news feed to events in your locality.</p>

          <div class="field">
            <label class="label">Home City</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.homeCity">
            </div>
          </div>

          <div class="field">
            <label class="label">Home State/Province</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.homeStateProvince">
            </div>
          </div>

          <div class="field">
            <label class="label">Home Country</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.homeCountry">
            </div>
          </div>
        </div>
      </nav>

      <nav class="panel">
        <p class="panel-heading">
          Professional Information
        </p>
        <div class="panel-block">
          <div class="field">
            <label class="label">Employer Name</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.employerName">
            </div>
          </div>

          <div class="field">
            <label class="label">Job Title</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.jobTitle">
            </div>
          </div>

          <div class="field">
            <label class="label">Industry</label>
            <div class="control">
              <input class="input" type="text" v-model="$store.state.app.userProfile.industry">
            </div>
          </div>
        </div>
      </nav>

      <div class="field is-grouped">
        <div class="control">
          <button class="button">Save</button>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
  import { keysToCamelCase, keysToSnakeCase } from '@/lib/keys_converting.js'
  import VueMultiselect from 'vue-multiselect'
  import Icon from '@/components/Shared/Icon.vue'
  import addIcon from '@/assets/images/add.svg'
  import minusIcon from '@/assets/images/minus_light.svg'

  export default {
    name: 'UserProfile',
    data() {
      return {
        apiUrl: import.meta.env.VITE_API_URL,
        interests: [],
        affiliationOptions: ['BKC Staff', 'BKC Faculty Board', 'BKC Affiliate', 'BKC Faculty Associate', 'BKC Fellow', 'RSM Fellow', 'RSM Scholar', 'Assembly Fellow', 'Other'],
        addIcon,
        minusIcon,
      }
    },
    components: {
      VueMultiselect,
      Icon,
    },
    created() {
      this.initialDataLoad()
    },
    computed: {
      affiliationYearsOptions() {
        return ['Now'].concat(Array.from(Array(new Date().getFullYear() - 1995 + 1), (_, index) => new Date().getFullYear() - index))
      },
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
        this.loadProfile()
        this.loadInterests()
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
      addProfilePropertyOption (newOption, key) {
        this.$store.dispatch('app/addProfilePropertyOption', {
          key: key,
          newOption: newOption,
        })
      },
      async loadProfile() {
        let profile = await this.$store.dispatch('app/fetchProfile')

        if (profile.length === 0) {
          return
        }

        profile = keysToCamelCase(profile)

        this.$store.dispatch('app/setUserProfile', profile)
      },
      async loadInterests() {
        let interests = await this.$store.dispatch('app/fetchInterests')

        this.interests = interests
      },
      addEmptyAffiliation() {
        this.$store.dispatch('app/addEmptyAffiliation')
      },
      removeAffiliation(index) {
        this.$store.dispatch('app/removeAffiliation', index)
      },
    }
  }
</script>

<style lang="scss">
  .user-profile {
    .field {
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

    .panel-block {
      flex-direction: column;
      align-items: normal;
      padding-bottom: 1rem;
    }

    input[type=checkbox] {
      transform: scale(2);
      margin-left: 0.5rem;
      cursor: pointer;
    }
  }
</style>
