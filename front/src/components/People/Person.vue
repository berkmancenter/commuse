<template>
  <div class="people-section-person">
    <router-link :to="'/people/' + person.id">
      <div class="is-size-5 mb-2 people-section-person-full-name">
        <div>{{ person.prefix }}</div>
        <div v-if="person.first_name">{{ person.first_name }}</div>
        <div v-if="person.middle_name">{{ person.middle_name }}</div>
        <div>{{ person.last_name }}</div>
        <div class="is-size-6" v-if="person.preferred_pronouns">({{ person.preferred_pronouns }})</div>
      </div>

      <div class="people-section-person-avatar">
        <img class="lazy" :data-src="person.image_url" v-if="person.image_url">
        <img :src="profileFallbackImage" v-if="!person.image_url">
      </div>
    </router-link>

    <VTooltip distance="10" placement="left" class="people-section-person-location">
      <div class="mt-2">
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

    <div class="mt-2 people-section-person-social is-flex">
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
  $person: '.people-section-person';

  #{$person} {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background-color: #FAFAFA;
    border: 1px solid var(--greyish-color);
    padding: 1rem;
    border-radius: 5%;
    transition: box-shadow 0.3s ease, transform 0.3s ease;

    &:hover {
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
      transform: translateY(-10px);
    }

    &-social {
      min-height: 2rem;
      display: flex;
      justify-content: center;

      img {
        display: block;
        width: 2rem;
        height: 2rem;

        &:hover {
          transform: scale(1.1);
        }
      }
    }

    &-full-name {
      min-height: 115px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;

      > div {
        text-align: center;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: 100%;
      }
    }

    &-avatar {
      display: flex;
      justify-content: center;

      img {
        width: 100%;
        border-radius: 50%;
        object-fit: cover;
        aspect-ratio: 1 / 1;
      }
    }

    &-bio {
      overflow-wrap: break-word;
      word-wrap: break-word;
      word-break: break-word;
      hyphens: auto;
    }

    &-interests {
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

    &-location {
      display: inline-block;
      padding-top: 1rem;
      text-align: center;
    }
  }
</style>
