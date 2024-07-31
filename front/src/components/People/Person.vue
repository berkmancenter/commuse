<template>
  <div class="people-section-person">
    <router-link :to="'/people/' + person.id">
      <div class="is-size-5 mb-2 people-section-person-full-name">
        <div>
          <div>{{ person.prefix }}</div>
          <div><span>{{ person.first_name }}</span> <span v-if="person.middle_name">{{ person.middle_name }}</span></div>
          <div>{{ person.last_name }}</div>
        </div>
        <div class="is-size-6" v-if="person.preferred_pronouns">({{ person.preferred_pronouns }})</div>
      </div>

      <div class="people-section-avatar">
        <img class="lazy" :data-src="`${apiUrl}/api/files/get/${person.image_url}`" v-if="person.image_url">
        <img :src="profileFallbackImage" v-if="!person.image_url">
      </div>
    </router-link>

    <VTooltip distance="10" placement="left" class="people-section-person-location">
      <div>
        <div class="is-size-6" v-if="personAddress.city">
          {{ personAddress.city }}
        </div>

        <div class="is-size-6" v-if="personAddress.state">
          {{ personAddress.state }}
        </div>

        <div class="is-size-6" v-if="personAddress.country">
          {{ personAddress.country }}
        </div>
      </div>

      <template #popper>
        {{ locationTooltipLabel }}
      </template>
    </VTooltip>

    <div class="mt-2 person-section-social is-flex">
      <div v-if="person.twitter_link" class="mr-2">
        <a :href="person.twitter_link" target="_blank">
          <img src="@/assets/images/twitter.svg">
        </a>
      </div>
      <div v-if="person.linkedin_link" class="mr-2">
        <a :href="person.linkedin_link" target="_blank">
          <img src="@/assets/images/linkedin.svg">
        </a>
      </div>
      <div v-if="person.mastodon_link" class="mr-2">
        <a :href="person.mastodon_link" target="_blank">
          <img src="@/assets/images/mastodon.svg">
        </a>
      </div>
    </div>
  </div>
</template>

<script>
  import profileFallbackImage from '@/assets/images/profile_fallback.png'

  export default {
    name: 'Person',
    data() {
      return {
        apiUrl: import.meta.env.VITE_API_URL,
        profileFallbackImage,
      }
    },
    props: {
      person: {
        type: Object,
        required: true
      }
    },
    computed: {
      personAddress() {
        if (this.person.current_city || this.person.current_state || this.person.current_country) {
          return {
            city: this.person.current_city,
            state: this.person.current_state,
            country: this.person.current_country,
          }
        } else {
          return {
            city: this.person.home_city,
            state: this.person.home_state,
            country: this.person.home_country,
          }
        }
      },
      locationTooltipLabel() {
        if (this.person.current_city || this.person.current_state || this.person.current_country) {
          return 'Current Location'
        } else {
          return 'Permanent Residence'
        }
      },
    }
  }
</script>

<style lang="scss">
  .people-section-person {
    overflow: hidden;
    background-color:   #FAFAFA;
    border: 1px solid var(--greyish-color);
    padding: 1rem;
    border-radius: 5%;
    transition: box-shadow 0.3s ease, transform 0.3s ease;

    &:hover {
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
      transform: translateY(-10px);
    }

    .person-section-social {
      img {
        display: block;
        width: 2rem;
        height: 2rem;
      }
    }

    .people-section-person-full-name {
      min-height: 90px;

      > div {
        word-wrap: break-word;
      }
    }

    .people-section-avatar {
      img {
        max-height: 200px;
      }
    }

    .people-section-bio {
      overflow-wrap: break-word;
      word-wrap: break-word;
      word-break: break-word;
      hyphens: auto;
    }

    .people-section-interests {
      display: flex;
      flex-wrap: wrap;

      > span {
        padding: .2rem .3rem;
        margin: .4rem .4rem .4rem 0;
        border: .1rem solid #000000;
        cursor: pointer;
        user-select: none;
      }
    }

    .people-section-person-location {
      display: inline-block;
    }
  }
</style>
