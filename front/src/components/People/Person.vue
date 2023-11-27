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
        <img class="lazy" :data-src="`${apiUrl}/api/files/get/${person.image_url}`">
      </div>
    </router-link>

    <div class="is-size-6" v-if="person.home_city">
        {{ person.home_city }}
    </div>

    <div class="is-size-6" v-if="person.home_state">
        {{ person.home_state }}
    </div>

    <div class="is-size-6" v-if="person.home_country">
        {{ person.home_country }}
    </div>

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
  export default {
    name: 'Person',
    data() {
      return {
        apiUrl: import.meta.env.VITE_API_URL,
      }
    },
    props: {
      person: {
        type: Object,
        required: true
      }
    },
  }
</script>

<style lang="scss">
  .people-section-person {
    overflow: hidden;

    .person-section-social {
      img {
        display: block;
        width: 2rem;
        height: 2rem;
      }
    }

    .people-section-person-full-name {
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
  }
</style>
