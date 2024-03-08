<template>
  <div class="people-section-details">
    <div class="people-section-details-side">
      <div class="is-size-4 mb-4 people-section-person-full-name">
        <div>
          {{ person.prefix }} {{ person.first_name }} {{ person.middle_name }} {{ person.last_name }}
        </div>
        <div class="is-size-6" v-if="person.preferred_pronouns">({{ person.preferred_pronouns }})</div>
      </div>

      <div class="people-section-details-side-image">
        <img :src="imageSrc">
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
                  {{ affiliationItem.tags.join(', ') }}, {{ affiliationItem.from }}-{{ affiliationItem.to }}
                </div>
              </div>
            </div>

            <template #popper>
              {{ affiliateFieldTitle }}(s)
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
        </div>
      </div>
    </div>

    <div class="box people-section-details-content">
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
  import PersonDetailsGroup from '@/components/People/PersonDetailsGroup.vue'
  import ShowMore from '@/components/Shared/ShowMore.vue'
  import { compact, flatten } from 'lodash'

  export default {
    name: 'PersonDetails',
    data() {
      return {
        person: {},
        apiUrl: import.meta.env.VITE_API_URL,
        phoneIcon,
        emailIcon,
        homeIcon,
        profileFallbackImage,
        affiliateIcon,
        currentAddressIcon,
        profileStructure: [],
      }
    },
    components: {
      PersonDetailsGroup,
      ShowMore,
    },
    computed: {
      address() {
        return [this.person.home_city, this.person.home_state, this.person.home_country].filter(n => n).join(', ')
      },
      currentAddress() {
        return [this.person.current_city, this.person.current_state, this.person.current_country].filter(n => n).join(', ')
      },
      imageSrc() {
        let src = this.profileFallbackImage

        if (this.person.image_url) {
          src = `${this.apiUrl}/api/files/get/${this.person.image_url}`
        }

        return src
      },
      myInformationGroup() {
        return this.profileStructure
          ?.filter((group) => { return group['machine_name'] == 'my_information' })[0] ?? {}
      },
      customGroups() {
        return this.profileStructure
        ?.filter((group) => { return !['my_information', 'contact_information', 'affiliation', 'location_current', 'location_information'].includes(group['machine_name']) })
      },
      affiliateFieldTitle() {
        let title = ''

        this.profileStructure.some((group) => {
          return group.custom_fields.some((custom_field) => {
            if (custom_field.machine_name === 'affiliation') {
              title = custom_field.title

              return true
            }

            return false
          });
        });

        return title
      }
    },
    created() {
      this.mitt.emit('spinnerStart', 2)
      this.initialDataLoad()
    },
    methods: {
      initialDataLoad() {
        this.loadPerson()
        this.loadProfileStructure()
      },
      async loadPerson() {
        const person = await this.$store.dispatch('app/fetchPerson', this.$route.params.id)

        this.person = person
        this.mitt.emit('spinnerStop')
      },
      async loadProfileStructure() {
        let profileStructure = await this.$store.dispatch('app/fetchProfileStructure')

        this.profileStructure = profileStructure
        this.mitt.emit('spinnerStop')
      },
      hasFields(group) {
        return group.custom_fields.some(field => {
          if (field?.metadata?.isImportProfileImageLink) {
            return false
          }

          const fieldValue = compact(flatten([this.person[field.machine_name]]))

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
    },
  }
</script>

<style lang="scss">
  .people-section-details {
    max-width: 1200px;
    position: relative;

    .people-section-details-side {
      position: fixed;
      max-width: 300px;
      padding: 1rem;
      background-color: var(--super-light-color);
      overflow-y: auto;
      height: calc(100vh - 8rem);

      @media screen and (max-width: 1200px) {
        margin: 0 auto;
        position: static;
        height: auto;
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
</style>
