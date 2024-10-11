<template>
  <div class="people-section-details">
    <div class="people-section-details-side">
      <SkeletonPatternLoader :loading="loading">
        <template v-slot:content>
          <div class="people-section-details-side-name-image">
            <div class="is-size-4 mb-4 people-section-person-full-name">
              <div>
                {{ person.prefix }} {{ person.first_name }} {{ person.middle_name }} {{ person.last_name }}
              </div>
              <div class="is-size-6" v-if="person.preferred_pronouns">({{ person.preferred_pronouns }})</div>
            </div>

            <div class="people-section-details-side-image">
              <img class="lazy" :src="`${apiUrl}/api/files/get/${this.person.image_url}`">
            </div>
          </div>

          <div class="people-section-details-side-content">
            <div class="people-section-details-side-content-items">
              <div class="is-flex is-align-items-center" v-if="person.mobile_phone_number">
                <img class="people-section-details-icon" :src="phoneIcon">
                {{ person.mobile_phone_number }}
              </div>

              <div v-if="person.email">
                <a :href="`mailto:${person.email}`" class="is-flex is-align-items-center">
                  <img class="people-section-details-icon" :src="emailIcon">
                  {{ person.email }}
                </a>
              </div>

              <VTooltip distance="10" placement="left" v-if="address">
                <div class="is-flex is-align-items-center">
                  <img class="people-section-details-icon" :src="homeIcon">
                  {{ address }}
                </div>

                <template #popper>
                  Permanent Residence
                </template>
              </VTooltip>

              <VTooltip distance="10" placement="left" v-if="currentAddress">
                <div class="is-flex is-align-items-center">
                  <img class="people-section-details-icon" :src="currentAddressIcon">
                  {{ currentAddress }}
                </div>

                <template #popper>
                  Current Location
                </template>
              </VTooltip>

              <VTooltip distance="10" placement="left" v-if="person?.affiliation?.length > 0">
                <div class="people-section-details-side-affiliation">
                  <div class="people-section-details-side-affiliation-item" v-for="affiliationItem in person.affiliation">
                    <div class="people-section-details-side-affiliation-item-icon">
                      <img class="people-section-details-icon" :src="affiliateIcon">
                    </div>
                    <div>
                      {{ affiliationItem.tags.join(', ') }}, {{ calendarDateFormat(affiliationItem.from) }}-{{ calendarDateFormat(affiliationItem.to) }}
                    </div>
                  </div>
                </div>

                <template #popper>
                  {{ affiliateFieldTitle('affiliation') }}(s)
                </template>
              </VTooltip>

              <VTooltip distance="10" placement="left" v-if="person?.activeAffiliation?.length > 0">
                <div class="people-section-details-side-affiliation">
                  <div class="people-section-details-side-affiliation-item" v-for="activeAffiliationItem in person.activeAffiliation">
                    <div class="people-section-details-side-affiliation-item-icon">
                      <img class="people-section-details-icon" :src="affiliateIcon">
                    </div>
                    <div>
                      {{ activeAffiliationItem.tags.join(', ') }}, {{ calendarDateFormat(activeAffiliationItem.from) }}-{{ calendarDateFormat(activeAffiliationItem.to) }}
                    </div>
                  </div>
                </div>

                <template #popper>
                  {{ affiliateFieldTitle('activeAffiliation') }}
                </template>
              </VTooltip>

              <div class="is-clearfix"></div>

              <div class="people-section-details-side-links">
                <div v-if="person.bio" class="people-section-details-side-link" @click="jumpToSection('group_bio')">Bio</div>
                <div v-if="hasFields(myInformationGroup)" class="people-section-details-side-link" @click="jumpToSection('group_additional_information')">Additional Information</div>
                <template v-for="group in customGroups" :key="group.machine_name">
                  <div v-if="hasFields(group)" class="people-section-details-side-link" @click="jumpToSection(`group_${group.machine_name}`)">{{ group.title }}</div>
                </template>
              </div>

              <div class="mt-2 people-section-details-last-updated">Last updated: {{ person.updated_at }} UTC</div>

              <div class="people-section-details-admin-actions" v-if="$store.state.user.currentUser.admin">
                <hr>

                <h4 class="is-size-5 mt-2">Admin</h4>

                <ActionButton class="mt-2" buttonText="Edit profile" @click="$router.push({ name: 'user-profile-admin.index', params: { id: person.user_id } })" :icon="editIcon"></ActionButton>
              </div>
            </div>
          </div>
        </template>

        <template v-slot:skeleton>
          <div class="content people-section-content">
            <div class="ssc-wrapper">
              <div class="ssc-head-line w-60 mb-4"></div>
              <div class="ssc-square people-section-details-side-ssc-avatar mb-4"></div>
              <div class="align-center flex justify-between mb-4" v-for="n in 4" :key="n">
                <div class="ssc-line w-20"></div>
                <div class="ssc-line w-70"></div>
              </div>
              <div class="ssc-head-line w-40 mb-4"></div>
              <div class="ssc-head-line w-60 mb-4"></div>
              <div class="ssc-head-line w-50 mb-4"></div>
              <div class="ssc-head-line w-70 mb-4"></div>
              <div class="ssc-line w-100"></div>
            </div>
          </div>
        </template>
      </SkeletonPatternLoader>
    </div>

    <div class="box people-section-details-content">
      <SkeletonPatternLoader :loading="loading">
        <template v-slot:content>
          <div class="notification is-warning" v-if="person.public_profile === 'f'">
            {{ $store.state.systemSettings.publicSystemSettings?.PublicProfileWarningShowProfile?.value }}
          </div>

          <div class="people-section-details-content-other">
            <div v-if="person.bio" ref="group_bio">
              <div class="panel">
                <p class="panel-heading">
                  Bio
                </p>
                <div class="panel-block">
                  <ShowMore :text="person.bio"></ShowMore>
                </div>
              </div>
            </div>

            <div v-if="hasFields(myInformationGroup)" ref="group_additional_information">
              <PersonDetailsGroup :group="myInformationGroup" :person="person" custom-group-title="Additional Information"></PersonDetailsGroup>
            </div>

            <template v-for="group in customGroups" :key="group.machine_name">
              <div :ref="`group_${group.machine_name}`" v-if="hasFields(group)">
                <PersonDetailsGroup :group="group" :person="person"></PersonDetailsGroup>
              </div>
            </template>
          </div>
        </template>

        <template v-slot:skeleton>
          <div class="ssc-wrapper">
            <div class="ssc-head-line w-100 mb-4"></div>
            <div class="ssc-square w-100 mb-6"></div>

            <div class="ssc-head-line w-100 mb-4"></div>
            <div class="ssc-line w-40 mb-4"></div>
            <div class="ssc-line w-100 mb-6"></div>

            <div class="ssc-head-line w-100 mb-4"></div>
            <div class="ssc-line w-40 mb-4"></div>
            <div class="ssc-line w-100 mb-4"></div>
            <div class="ssc-line w-100 mb-6"></div>

            <div class="ssc-head-line w-100 mb-4"></div>
            <div class="ssc-line w-40 mb-4"></div>
            <div class="flex is-align-items-flex-start mb-6">
              <div class="ssc-line mr-2 w-10"></div>
              <div class="ssc-line mr-2 w-20"></div>
              <div class="ssc-line mr-2 w-10"></div>
              <div class="ssc-line mr-2 w-15"></div>
              <div class="ssc-line mr-2 w-20"></div>
              <div class="ssc-line mr-2 w-10"></div>
              <div class="ssc-line mr-2 w-20"></div>
            </div>

            <div class="ssc-head-line w-100 mb-4"></div>
            <div class="ssc-line w-30 mb-4"></div>
            <div class="flex is-align-items-flex-start mb-4">
              <div class="ssc-line mr-2 w-20"></div>
              <div class="ssc-line mr-2 w-30"></div>
              <div class="ssc-line mr-2 w-10"></div>
              <div class="ssc-line mr-2 w-15"></div>
              <div class="ssc-line mr-2 w-20"></div>
            </div>
            <div class="flex is-align-items-flex-start mb-6">
              <div class="ssc-line mr-2 w-10"></div>
              <div class="ssc-line mr-2 w-20"></div>
              <div class="ssc-line mr-2 w-10"></div>
              <div class="ssc-line mr-2 w-15"></div>
              <div class="ssc-line mr-2 w-20"></div>
              <div class="ssc-line mr-2 w-10"></div>
              <div class="ssc-line mr-2 w-20"></div>
            </div>

            <div class="ssc-head-line w-100 mb-4"></div>
            <div class="ssc-line w-40 mb-4"></div>
            <div class="ssc-line w-100 mb-4"></div>
            <div class="ssc-line w-100 mb-4"></div>
            <div class="ssc-line w-100 mb-4"></div>
            <div class="ssc-line w-100 mb-4"></div>
            <div class="ssc-line w-100 mb-6"></div>
          </div>
        </template>
      </SkeletonPatternLoader>
    </div>
  </div>
</template>

<script>
  import emailIcon from '@/assets/images/email.svg'
  import phoneIcon from '@/assets/images/phone.svg'
  import homeIcon from '@/assets/images/home.svg'
  import profileFallbackImage from '@/assets/images/profile_fallback.png'
  import affiliateIcon from '@/assets/images/affiliate.svg'
  import currentAddressIcon from '@/assets/images/marker_map.svg'
  import editIcon from '@/assets/images/edit.svg'
  import PersonDetailsGroup from '@/components/People/PersonDetailsGroup.vue'
  import ShowMore from '@/components/Shared/ShowMore.vue'
  import ActionButton from '@/components/Shared/ActionButton.vue'
  import { compact, flatten } from 'lodash'
  import { getMultiFieldValue } from '@/lib/fields/multi.js'
  import { calendarDateFormat } from '@/lib/time_stuff.js'
  import LazyLoad from 'vanilla-lazyload'
  import SkeletonPatternLoader from '@/components/Shared/SkeletonPatternLoader.vue'

  export default {
    name: 'PersonDetails',
    data() {
      return {
        person: {},
        apiUrl: import.meta.env.VITE_API_URL,
        phoneIcon,
        emailIcon,
        homeIcon,
        editIcon,
        profileFallbackImage,
        affiliateIcon,
        currentAddressIcon,
        profileStructure: [],
        getMultiFieldValue,
        calendarDateFormat,
        lazyLoadInstance: null,
        loading: true,
      }
    },
    components: {
      PersonDetailsGroup,
      ShowMore,
      ActionButton,
      SkeletonPatternLoader,
    },
    created() {
      this.initialDataLoad()
    },
    unmounted() {
      this.lazyLoadInstance.destroy()
      this.lazyLoadInstance = null
    },
    computed: {
      address() {
        return [this.person.home_city, this.person.home_state, this.person.home_country].filter(n => n).join(', ')
      },
      currentAddress() {
        return [this.person.current_city, this.person.current_state, this.person.current_country].filter(n => n).join(', ')
      },
      myInformationGroup() {
        let group = this.profileStructure?.filter((group) => { return group['machine_name'] == 'my_information' })[0]

        if (!group) {
          return {}
        }

        let groupCustomFields = group.custom_fields.filter(item => item.title !== 'Bio')
        group.custom_fields = groupCustomFields

        return group
      },
      customGroups() {
        return this.profileStructure
          ?.filter((group) => { return !['my_information', 'contact_information', 'affiliation', 'location_current', 'location_information', 'multi_fields_group'].includes(group['machine_name']) })
      },
    },
    methods: {
      async initialDataLoad() {
        this.loadProfileStructure()
        await this.loadPerson()
        this.initImgLazyLoad()
        this.loading = false
      },
      async loadPerson() {
        const response = await this.$store.dispatch('people/fetchPerson', this.$route.params.id)

        if (response.status === 404) {
          this.$router.push({ name: 'pagenotfound.index' })

          return
        }

        this.person = await response
      },
      async loadProfileStructure() {
        let profileStructure = await this.$store.dispatch('user/fetchProfileStructure')

        this.profileStructure = profileStructure
      },
      hasFields(group) {
        return group?.custom_fields?.some(field => {
          if (field?.metadata?.isImportProfileImageLink) {
            return false
          }

          let fieldValue = compact(flatten([this.person[field.machine_name]]))
          if (field.input_type === 'multi') {
            fieldValue = getMultiFieldValue(field, this.person)
          }

          return fieldValue.length > 0
        })
      },
      jumpToSection(groupRef) {
        let elem = this.$refs[groupRef]

        if (Array.isArray(elem)) {
          elem = elem[0]
        }

        const y = elem.getBoundingClientRect().top + window.scrollY - 80

        window.scroll({
          top: y,
          behavior: 'instant'
        })
      },
      affiliateFieldTitle(fieldMachineName) {
        let title = ''

        this.profileStructure.some((group) => {
          return group.custom_fields.some((custom_field) => {
            if (custom_field.machine_name === fieldMachineName) {
              title = custom_field.title

              return true
            }

            return false
          });
        });

        return title
      },
      initImgLazyLoad() {
        this.lazyLoadInstance = new LazyLoad({
          callback_error: (img) => {
            img.setAttribute('src', this.profileFallbackImage)
          },
          unobserve_entered: true,
          unobserve_completed: true,
        })
        this.$nextTick(() => {
          if (this.lazyLoadInstance) {
            this.lazyLoadInstance.update()
          }
        })
      },
    },
  }
</script>

<style lang="scss">
  .people-section-details {
    max-width: 1200px;
    position: relative;

    .people-section-details-side {
      position: fixed;
      width: 300px;
      padding: 1rem;
      background-color: var(--super-light-color);
      overflow-y: auto;
      height: calc(100vh - 8rem);

      @media screen and (max-width: 1200px) {
        position: static;
        height: auto;
        width: 100%;
        max-width: unset;
        display: flex;
      }

      @media screen and (max-width: 768px) {
        display: block;
      }

      .people-section-details-side-image {
        img {
          border-radius: 10px;
        }
      }

      .people-section-details-side-content {
        padding-top: 0.5rem;
        overflow-wrap: anywhere;
      }

      .people-section-details-side-affiliation {
        .people-section-details-side-affiliation-item {
          display: flex;
          align-items: center;

          .people-section-details-side-affiliation-item-icon {
            flex-shrink: 0;
            display: flex;
            align-content: center;
          }
        }
      }

      .people-section-details-side-content-items {
        .v-popper--theme-tooltip {
          float: left;
          clear: both;
        }

        @media screen and (max-width: 1200px) {
          margin-left: 2rem;
        }

        @media screen and (max-width: 768px) {
          margin-left: 0;
        }
      }

      .people-section-details-side-ssc-avatar {
        height: 270px;
      }
    }

    .people-section-details-content {
      padding-top: 0;
      margin-left: calc(300px + 1rem);

      @media screen and (max-width: 1200px) {
        margin-left: 0;
        margin-top: 2rem;
      }

      .people-section-details-content-bio {
        white-space: pre-line;
      }
    }

    .people-section-details-icon {
      width: 2rem;
      height: 2rem;
      margin-right: 0.5rem;
    }

    .people-section-details-content-interests {
      margin-bottom: 1.5rem;
    }

    .people-section-details-content-other {
      @media screen and (min-width: 1200px) {
        display: flex;
        gap: 1rem;
        flex-direction: column;
      }

      @media screen and (max-width: 1199px) {
        .panel {
          margin-bottom: 1rem;
        }
      }
    }

    table {
      &:not(:last-child) {
        margin-bottom: 0;
      }

      @media screen and (min-width: 700px) {
        th {
          width: 250px;
        }
      }

      td,
      th {
        padding: 0;
      }

      td {
        overflow-wrap: anywhere;
      }

      th:only-child,
      td:only-child {
        border-bottom: none;
      }
    }

    .panel-block {
      > * {
        flex-grow: 1;
      }
    }

    ul {
      margin-top: 0
    }
  }

  .people-section-details-last-updated {
    max-width: 1200px;
  }

  .people-section-details-side-links {
    margin-top: 1rem;

    .people-section-details-side-link {
      padding: 0.5rem 1rem;
      background-color: #ffffff;
      margin-right: 0.5rem;
      margin-bottom: 0.5rem;
      border-radius: 10%;
      cursor: pointer;
      display: inline-flex;

      &:hover {
        box-shadow: 0 0.5em 1em -0.125em rgba(10, 10, 10, 0.1), 0 0px 0 1px rgba(10, 10, 10, 0.02);
      }
    }
  }

  .people-section-details-admin-actions {
    hr {
      background-color: #4a4a4a;
    }
  }
</style>
