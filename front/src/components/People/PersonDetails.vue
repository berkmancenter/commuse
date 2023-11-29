<template>
  <div class="people-section-details">
    <div class="people-section-details-side">
      <div class="people-section-details-side-image">
        <img :src="imageSrc">
      </div>
      <div class="people-section-details-side-content">
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

        <div class="is-flex is-align-items-center" v-if="address">
          <img class="people-section-details-icon" :src="homeIcon">
          {{ address }}
        </div>

        <div class="people-section-details-side-affiliation" v-if="person?.bkc_affiliation?.length > 0">
          <div class="people-section-details-side-affiliation-item" v-for="affiliationItem in person.bkc_affiliation">
            <div class="people-section-details-side-affiliation-item-icon">
              <img class="people-section-details-icon" :src="affiliateIcon">
            </div>
            <div>
              {{ affiliationItem.tags.join(', ') }}, {{ affiliationItem.from }}-{{ affiliationItem.to }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="box people-section-details-content">
      <div class="is-size-4 mb-2 p-4 people-section-person-full-name">
        <div>
          {{ person.prefix }} {{ person.first_name }} {{ person.middle_name }} {{ person.last_name }}
        </div>
        <div class="is-size-6" v-if="person.preferred_pronouns">({{ person.preferred_pronouns }})</div>
      </div>

      <div class="people-section-details-content-other">
        <div class="panel" v-if="person.bio">
          <p class="panel-heading">
            Bio
          </p>
          <div class="panel-block">
            <div class="content" v-html="person.bio"></div>
          </div>
        </div>

        <PersonDetailsGroup :group="myInformationGroup" :person="person" custom-group-title="Additional Information" v-if="Object.keys(myInformationGroup).length > 0"></PersonDetailsGroup>
        <PersonDetailsGroup :group="group" :person="person" v-for="group in customGroups" :key="group.machine_name"></PersonDetailsGroup>
      </div>
    </div>
  </div>

  <div class="has-text-right mt-2 people-section-details-last-updated">Last updated: {{ person.updated_at }} UTC</div>
</template>

<script>
  import emailIcon from '@/assets/images/email.svg'
  import phoneIcon from '@/assets/images/phone.svg'
  import homeIcon from '@/assets/images/home.svg'
  import profileFallbackImage from '@/assets/images/profile_fallback.png'
  import affiliateIcon from '@/assets/images/affiliate.svg'
  import PersonDetailsGroup from '@/components/People/PersonDetailsGroup.vue'

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
        profileStructure: [],
      }
    },
    components: {
      PersonDetailsGroup,
    },
    computed: {
      address() {
        return [this.person.home_city, this.person.home_state, this.person.home_country].filter(n => n).join(', ')
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
        ?.filter((group) => { return !['my_information', 'contact_information', 'bkc_affiliation'].includes(group['machine_name']) })
      },
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
    },
  }
</script>

<style lang="scss">
  .people-section-details {
    max-width: 1200px;

    @media screen and (min-width: 1200px) {
      display: flex;
      gap: 2rem;
    }

    .people-section-details-side {
      max-width: 300px;
      padding: 1rem;
      background-color: var(--super-light-color);
      margin: 0 auto;

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
    }

    .people-section-details-content {
      padding-top: 0;
      flex: 1;

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
</style>
