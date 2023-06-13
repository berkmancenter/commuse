<template>
  <div class="people-section">
    <h3 class="is-size-3">People</h3>

    <input
      type="text"
      v-model="searchTerm"
      placeholder="Search"
      class="input mb-4 people-section-search-input"
    >

    <div class="content people-section-content">
        <div class="people-section-person"
            v-for="(person, index) in filteredPeople"
            :key="person.id"
            :ref="'personRef_' + index">
          <div class="is-size-4 mb-2">
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

          <div class="is-size-6 mt-2">
            {{ person.bio }}
          </div>
        </div>
    </div>
  </div>
</template>

<script>
  import LazyLoad from 'vanilla-lazyload'

  export default {
    name: 'PeopleIndex',
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
          const searchText = `${person.first_name} ${person.last_name} ${person.bio}`.toLowerCase();

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

    .person-section-social {
      img {
        display: block;
        width: 2rem;
        height: 2rem;
      }
    }

    .people-section-search-input {
      width: 200px;
    }
  }
</style>
