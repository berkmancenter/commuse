<template>
  <div class="people-section">
    <h3 class="is-size-2 mb-4">People</h3>

    <div class="people-section-filters mb-4">
      <div>
        <input
          type="text"
          v-model="searchTerm"
          placeholder="Filter"
          class="input mb-4"
        >
      </div>
    </div>

    <div class="content people-section-content">
      <div class="people-section-person"
          v-for="(person, index) in filteredPeople"
          :key="person.id"
          :ref="'personRef_' + index">
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
          <img class="lazy"
              :data-src="person.image_url"
          >
        </div>

        <div class="mt-2 people-section-topics">
          <span
            class="tag"
            v-for="(topic) in person.topics"
          >
            {{ topic }}
          </span>
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
    </div>
  </div>
</template>

<script>
  import LazyLoad from 'vanilla-lazyload'

  export default {
    name: 'PeopleIndex',
    components: {},
    data() {
      return {
        lazyLoadInstance: null,
        searchTerm: '',
      }
    },
    computed: {
      filteredPeople() {
        const searchTerm = this.searchTerm.toLowerCase();

        return this.$store.state.app.people.filter((person) => {
          const searchText = `${person.first_name} ${person.last_name} ${person.short_bio} ${person.topics.join(' ')}`.toLowerCase();

          return searchText.includes(searchTerm);
        });
      },
    },
    created() {
      this.initialDataLoad()
      this.initLazyLoad()
    },
    mounted() {},
    methods: {
      async initialDataLoad() {
        const people = await this.$store.dispatch('app/fetchPeople')
        this.$store.dispatch('app/setPeople', people)
        this.$nextTick(() => {
          this.lazyLoadInstance.update()
        })
      },
      initLazyLoad() {
        this.lazyLoadInstance = new LazyLoad({
          callback_error: (img) => {
            img.setAttribute('src', '')
          },
          unobserve_entered: true,
          unobserve_completed: true,
        })
        this.lazyLoadInstance.update()
      },
    },
    watch: {
      searchTerm() {
        this.$nextTick(() => {
          this.lazyLoadInstance.update()
        })
      },
    },
  }
</script>

<style lang="scss">
  .people-section {
    .people-section-content {
      display: grid;
      grid-gap: 2rem;

      @media screen and (max-width: 599px) {
        grid-template-columns: repeat(1, 1fr);
      }

      @media screen and (min-width: 600px) {
        grid-template-columns: repeat(3, 1fr);
      }

      @media screen and (min-width: 960px) {
        grid-template-columns: repeat(4, 1fr);
      }

      @media screen and (min-width: 1100px) {
        grid-template-columns: repeat(5, 1fr);
      }

      @media screen and (min-width: 1440px) {
        grid-template-columns: repeat(6, 1fr);
      }

      @media screen and (min-width: 1700px) {
        grid-template-columns: repeat(8, 1fr);
      }
    }

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
    }

    .people-section-search-input {
      width: 200px;
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

    .people-section-filters {
      display: flex;

      > div {
        margin-right: 1rem;
      }
    }
  }
</style>
