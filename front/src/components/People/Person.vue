<template>
  <div class="people-section-person">
    <div class="is-size-4 mb-2 people-section-person-full-name">
      <div>{{ person.first_name }}</div>
      <div>{{ person.last_name }}</div>
    </div>

    <div class="is-size-5 mb-4">
      <div class="is-flex is-justify-content-center is-align-items-center" v-if="person.organization">
        {{ person.organization }}
      </div>
    </div>

    <div class="people-section-avatar">
      <img class="lazy" :data-src="person.image_url">
    </div>

    <div class="mt-2 people-section-topics">
      <span class="tag" v-for="topic in person.topics" :key="topic">{{ topic }}</span>
    </div>

    <div class="mt-2 person-section-social is-flex">
      <div v-if="person.bio" class="mr-2">
        <a :href="person.twitter" target="_blank">
          <img src="@/assets/images/twitter.svg">
        </a>
      </div>
      <div v-if="person.bio" class="mr-2">
        <a :href="person.linkedin" target="_blank">
          <img src="@/assets/images/linkedin.svg">
        </a>
      </div>
      <div v-if="person.bio" class="mr-2">
        <a :href="person.mastodon" target="_blank">
          <img src="@/assets/images/mastodon.svg">
        </a>
      </div>
    </div>

    <div class="is-size-6 mt-2 people-section-bio">
      {{ person.short_bio }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'Person',
  props: {
    person: {
      type: Object,
      required: true
    }
  }
};
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
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
      }
    }

    .people-section-avatar {
      img {
        max-width: 200px;
      }
    }

    .people-section-bio {
      overflow-wrap: break-word;
      word-wrap: break-word;
      word-break: break-word;
      hyphens: auto;
    }

    .people-section-topics {
      display: flex;
      flex-wrap: wrap;

      > span {
        padding: .2rem .3rem;
        margin: .4rem .4rem .4rem 0;
        border: .1rem solid #000000;
      }
    }
  }
</style>
