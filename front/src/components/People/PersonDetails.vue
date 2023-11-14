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

        <div class="panel people-section-details-side-affiliation mt-2 pt-2" v-if="person.affiliation?.length > 0">
          <div class="panel-block">
            <div class="content">
              <div class="people-section-details-side-affiliation-item" v-for="affiliationItem in person.affiliation">
                <div class="people-section-details-side-affiliation-item-icon">
                  <img class="people-section-details-icon" :src="affiliateIcon">
                </div>
                <div>
                  {{ affiliationItem.position.join(', ') }}, {{ affiliationItem.from }}-{{ affiliationItem.to }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="box people-section-details-content">
      <div class="is-size-4 mb-2 p-4 people-section-person-full-name">
        <div v-if="!person.preferred_name">
          {{ person.prefix }} {{ person.first_name }} {{ person.middle_name }} {{ person.last_name }}
        </div>
        <div v-if="person.preferred_name">
          <div>{{ person.preferred_name }}</div>
        </div>
        <div class="is-size-6" v-if="person.preferred_pronouns">({{ person.preferred_pronouns }})</div>
      </div>

      <div class="people-section-details-content-interests">
        <div class="panel">
          <p class="panel-heading">
            Areas of Interest
          </p>
          <div class="panel-block">
            <div class="content">
              <div v-if="person.interested_in?.length" class="people-section-details-data-item">
                <div class="people-section-details-data-item-label">Interested In</div>
                <div>
                  <div class="tags are-medium are-light">
                    <span class="tag" v-for="item in person.interested_in">{{ item }}</span>
                  </div>
                </div>
              </div>

              <div v-if="person.knowledgeable_in?.length" class="people-section-details-data-item">
                <div class="people-section-details-data-item-label">Knowledgeable In</div>
                <div>
                  <div class="tags are-medium are-light">
                    <span class="tag" v-for="item in person.knowledgeable_in">{{ item }}</span>
                  </div>
                </div>
              </div>

              <div v-if="person.working_groups?.length" class="people-section-details-data-item">
                <div class="people-section-details-data-item-label">Working Groups</div>
                <div>
                  <div class="tags are-medium are-light">
                    <span class="tag" v-for="item in person.working_groups">{{ item }}</span>
                  </div>
                </div>
              </div>

              <div v-if="person.projects?.length" class="people-section-details-data-item">
                <div class="people-section-details-data-item-label">Projects</div>
                <div>
                  <div class="tags are-medium are-light">
                    <span class="tag" v-for="item in person.projects">{{ item }}</span>
                  </div>
                </div>
              </div>

              <div v-if="!person.interested_in?.length && !person.knowledgeable_in?.length && !person.working_groups?.length && !person.projects?.length">
                No information has been entered yet.
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="panel people-section-details-content-bio" v-if="person.bio">
        <p class="panel-heading">
          Bio
        </p>
        <div class="panel-block">
          <div class="content" v-html="person.bio"></div>
        </div>
      </div>

      <div class="people-section-details-content-other">
        <div class="panel">
          <p class="panel-heading">
            Professional Information
          </p>
          <div class="panel-block">
            <div class="content">
              <div v-if="person.employer_name" class="people-section-details-data-item">
                <div class="people-section-details-data-item-label">Employer Name</div>
                <div>{{ person.employer_name }}</div>
              </div>
              <div v-if="person.job_title" class="people-section-details-data-item">
                <div class="people-section-details-data-item-label">Job Title</div>
                <div>{{ person.job_title }}</div>
              </div>
              <div v-if="person.industry" class="people-section-details-data-item">
                <div class="people-section-details-data-item-label">Industry</div>
                <div>{{ person.industry }}</div>
              </div>

              <div v-if="!person.employer_name && !person.job_title && !person.industry">
                No information has been entered yet.
              </div>
            </div>
          </div>
        </div>

        <div class="panel">
          <p class="panel-heading">
            Online Presence
          </p>
          <div class="panel-block">
            <div class="content">
              <div v-if="person.website_link" class="people-section-details-data-item">
                <div class="people-section-details-data-item-label">Personal Website</div>
                <div><a :href="person.website_link">{{ person.website_link }}</a></div>
              </div>
              <div v-if="person.twitter_link" class="people-section-details-data-item">
                <div class="people-section-details-data-item-label">X (Ex-Twitter)</div>
                <div><a :href="person.twitter_link">{{ person.twitter_link }}</a></div>
              </div>
              <div v-if="person.mastodon_link" class="people-section-details-data-item">
                <div class="people-section-details-data-item-label">Mastodon</div>
                <div><a :href="person.mastodon_link">{{ person.mastodon_link }}</a></div>
              </div>
              <div v-if="person.linkedin_link" class="people-section-details-data-item">
                <div class="people-section-details-data-item-label">Linkedin</div>
                <div><a :href="person.linkedin_link">{{ person.linkedin_link }}</a></div>
              </div>
              <div v-if="person.facebook_link" class="people-section-details-data-item">
                <div class="people-section-details-data-item-label">Facebook</div>
                <div><a :href="person.facebook_link">{{ person.facebook_link }}</a></div>
              </div>
              <div v-if="person.instagram_link" class="people-section-details-data-item">
                <div class="people-section-details-data-item-label">Instagram</div>
                <div><a :href="person.instagram_link">{{ person.instagram_link }}</a></div>
              </div>
              <div v-if="person.snapchat_link" class="people-section-details-data-item">
                <div class="people-section-details-data-item-label">Snapchat</div>
                <div><a :href="person.snapchat_link">{{ person.snapchat_link }}</a></div>
              </div>
              <div v-if="person.other_link" class="people-section-details-data-item">
                <div class="people-section-details-data-item-label">Other</div>
                <div><a :href="person.other_link">{{ person.other_link }}</a></div>
              </div>

              <div v-if="!person.website_link && !person.twitter_link && !person.mastodon_link && !person.linkedin_link && !person.facebook_link && !person.instagram_link && !person.snapchat_link && !person.other_link">
                No information has been entered yet.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="has-text-right mt-2">Last updated: {{ person.updated_at }}</div>
</template>

<script>
  import emailIcon from '@/assets/images/email.svg'
  import phoneIcon from '@/assets/images/phone.svg'
  import homeIcon from '@/assets/images/home.svg'
  import profileFallbackImage from '@/assets/images/profile_fallback.png'
  import affiliateIcon from '@/assets/images/affiliate.svg'

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
      }
    },
    computed: {
      address() {
        return [this.person.home_city, this.person.home_state_province, this.person.home_country].filter(n => n).join(', ')
      },
      imageSrc() {
        let src = this.profileFallbackImage

        if (this.person.image_url) {
          src = `${this.apiUrl}/api/files/get/${this.person.image_url}`
        }

        return src
      }
    },
    created() {
      this.initialDataLoad()
    },
    methods: {
      async initialDataLoad() {
        const person = await this.$store.dispatch('app/fetchPerson', this.$route.params.id)

        this.person = person
      },
    },
  }
</script>

<style lang="scss">
  .people-section-details {
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
        background-color: #ffffff;

        .panel-heading {
          border-bottom: 1px solid var(--secondary-color);
          margin-bottom: 0.5rem;
        }

        .panel-heading, .panel-block {
          background-color: #ffffff;
        }

        .people-section-details-side-affiliation-item {
          border-bottom: 1px solid var(--secondary-color);
          padding-bottom: 0.5rem;
          margin-bottom: 1rem;
          display: flex;
          align-items: center;

          .people-section-details-side-affiliation-item-icon {
            flex-shrink: 0;
          }

          &:last-child {
            border-bottom: none;
            margin-bottom: 0;
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
        align-items: baseline;

        > nav {
          width: 50%;
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

    .people-section-details-data-item {
      padding-bottom: 0.5rem;
      margin-bottom: 0.5rem;
      border-bottom: 1px solid #dbdbdb;
      overflow-wrap: anywhere;

      &:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
      }

      .people-section-details-data-item-label {
        font-weight: bold;
        margin-bottom: 0.2rem;
      }
    }
  }
</style>
