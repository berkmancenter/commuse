<template>
  <div class="people-section-details">
    <div class="people-section-details-image">
      <div class="people-section-details-image-image">
        <img :src="`${apiUrl}/api/files/get/${person.image_url}`">
      </div>
      <div class="people-section-details-image-content">
        <div class="is-flex is-align-items-center">
          <img class="people-section-details-icon" :src="emailIcon">
          {{ valOrNot(person.mobile_phone_number) }}
        </div>

        <div>
          <a :href="`mailto:${person.email}`" class="is-flex is-align-items-center">
            <img class="people-section-details-icon" :src="phoneIcon">
            {{ valOrNot(person.email) }}
          </a>
        </div>

        <div class="is-flex is-align-items-center">
          <img class="people-section-details-icon" :src="homeIcon">
          {{ valOrNot(address) }}
        </div>
      </div>
    </div>

    <div class="box people-section-details-content">
      <div class="is-size-2 mb-2 p-4 people-section-person-full-name">
        <div v-if="!person.preferred_name">
          {{ person.prefix }} {{ person.first_name }} {{ person.middle_name }} {{ person.last_name }}
        </div>
        <div v-if="person.preferred_name">
          <div>{{ person.preferred_name }}</div>
        </div>
        <div class="is-size-6" v-if="person.preferred_pronouns">({{ person.preferred_pronouns }})</div>
      </div>

      <div class="people-section-details-content-interests">
        <nav class="panel">
          <p class="panel-heading">
            Areas of Interest
          </p>
          <div class="panel-block">
            <div class="content">
              <table class="table">
                <tr>
                  <th>Interested In</th>
                  <td>
                    <div class="tags are-medium are-light">
                      <span class="tag" v-for="item in person.interested_in">{{ item }}</span>
                    </div>
                  </td>
                </tr>
                <tr>
                  <th>Knowledgeable In</th>
                  <td>
                    <div class="tags are-medium are-light">
                      <span class="tag" v-for="item in person.knowledgeable_in">{{ item }}</span>
                    </div>
                  </td>
                </tr>
                <tr>
                  <th>Working Groups</th>
                  <td>
                    <div class="tags are-medium are-light">
                      <span class="tag" v-for="item in person.working_groups">{{ item }}</span>
                    </div>
                  </td>
                </tr>
                <tr>
                  <th>Projects</th>
                  <td>
                    <div class="tags are-medium are-light">
                      <span class="tag" v-for="item in person.projects">{{ item }}</span>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
          </div>
        </nav>
      </div>

      <nav class="panel people-section-details-content-bio" v-if="person.bio">
        <p class="panel-heading">
          Bio
        </p>
        <div class="panel-block">
          <div class="content" v-html="person.bio"></div>
        </div>
      </nav>

      <div class="people-section-details-content-other">
        <nav class="panel">
          <p class="panel-heading">
            Professional Information
          </p>
          <div class="panel-block">
            <div class="content">
              <table class="table">
                <tr>
                  <th>Employer Name</th>
                  <td>{{ valOrNot(person.employer_name) }}</td>
                </tr>
                <tr>
                  <th>Job Title</th>
                  <td>{{ valOrNot(person.job_title) }}</td>
                </tr>
                <tr>
                  <th>Industry</th>
                  <td>{{ valOrNot(person.industry) }}</td>
                </tr>
              </table>
            </div>
          </div>
        </nav>

        <nav class="panel">
          <p class="panel-heading">
            Online Presence
          </p>
          <div class="panel-block">
            <div class="content">
              <table class="table">
                <tr>
                  <th>Personal Website</th>
                  <td>{{ valOrNot(person.website_link) }}</td>
                </tr>
                <tr>
                  <th>X (Ex-Twitter)</th>
                  <td>{{ valOrNot(person.twitter_link) }}</td>
                </tr>
                <tr>
                  <th>Mastodon</th>
                  <td>{{ valOrNot(person.mastodon_link) }}</td>
                </tr>
                <tr>
                  <th>Linkedin</th>
                  <td>{{ valOrNot(person.linkedin_link) }}</td>
                </tr>
                <tr>
                  <th>Facebook</th>
                  <td>{{ valOrNot(person.facebook_link) }}</td>
                </tr>
                <tr>
                  <th>Instagram</th>
                  <td>{{ valOrNot(person.instagram_link) }}</td>
                </tr>
                <tr>
                  <th>Snapchat</th>
                  <td>{{ valOrNot(person.snapchat_link) }}</td>
                </tr>
                <tr>
                  <th>Other</th>
                  <td>{{ valOrNot(person.other_link) }}</td>
                </tr>

              </table>
            </div>
          </div>
        </nav>
      </div>
    </div>
  </div>
</template>

<script>
  import emailIcon from '@/assets/images/email.svg'
  import phoneIcon from '@/assets/images/phone.svg'
  import homeIcon from '@/assets/images/home.svg'

  export default {
    name: 'PersonDetails',
    data() {
      return {
        person: {},
        apiUrl: import.meta.env.VITE_API_URL,
        phoneIcon: phoneIcon,
        emailIcon: emailIcon,
        homeIcon: homeIcon,
      }
    },
    computed: {
      address() {
        return [this.person.home_city, this.person.home_state_province, this.person.home_country].filter(n => n).join(', ')
      },
    },
    created() {
      this.initialDataLoad()
    },
    methods: {
      async initialDataLoad() {
        const person = await this.$store.dispatch('app/fetchPerson', this.$route.params.id)

        this.person = person
      },
      valOrNot(val) {
        return val || 'N/A'
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

    .people-section-details-image {
      max-width: 300px;
      padding: 1rem;
      background-color: var(--super-light-color);
      margin: 0 auto;

      .people-section-details-image-image {
        img {
          border-radius: 10px;
        }
      }

      .people-section-details-image-content {
        padding: 1rem;
        overflow-wrap: anywhere;
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
      @media screen and (min-width: 700px) {
        th {
          width: 250px;
        }
      }

      td {
        overflow-wrap: anywhere;
      }
    }

    .panel-block {
      > * {
        flex-grow: 1;
      }
    }
  }
</style>
