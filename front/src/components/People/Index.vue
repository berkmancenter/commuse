<template>
  <div class="people-section">
    <h3 class="is-size-2 mb-4">People</h3>

    <div class="people-section-filters mb-4">
      <div>
        <input
          type="text"
          v-model="searchTerm"
          placeholder="Filter by text"
          class="input mb-4"
        >
      </div>

      <div>
        <VueMultiselect
          v-model="topics"
          :multiple="true"
          :options="allTopics"
          placeholder="Filter by interests"
        >
        </VueMultiselect>
      </div>
    </div>

    <div class="content people-section-content">
      <Person
        v-for="(person, index) in filteredPeople"
        :key="person.id"
        :person="person"
        :ref="'personRef_' + index"
      ></Person>
    </div>
  </div>
</template>

<script>
  import LazyLoad from 'vanilla-lazyload'
  import Person from './Person.vue'
  import profileFallbackImage from '@/assets/images/profile_fallback.png'
  import VueMultiselect from 'vue-multiselect'

  export default {
    name: 'PeopleIndex',
    components: {
      Person,
      VueMultiselect,
    },
    data() {
      return {
        lazyLoadInstance: null,
        searchTerm: '',
        profileFallbackImage: profileFallbackImage,
        topics: [],
        allTopics: [],
      }
    },
    computed: {
      filteredPeople() {
        const searchTerm = this.searchTerm.toLowerCase()

        return this.$store.state.app.people.filter((person) => {
          const searchText = `${person.first_name} ${person.last_name} ${person.short_bio} ${person.city} ${person.country} ${person.continent} ${person.topics.join(' ')}`.toLowerCase()

          const hasSearchTerm = searchText.includes(searchTerm)
          const hasSelectedTopics = this.topics.length === 0 || this.topics.some(topic => person.topics.includes(topic))

          return hasSearchTerm && hasSelectedTopics
        })
      },
    },
    created() {
      this.initialDataLoad()
      this.initLazyLoad()
    },
    mounted() {},
    methods: {
      async initialDataLoad() {
        this.loadTopics();

        const people = await this.$store.dispatch('app/fetchPeople')

        this.$store.dispatch('app/setPeople', people)
        this.$nextTick(() => {
          this.lazyLoadInstance.update()
        })
      },
      initLazyLoad() {
        this.lazyLoadInstance = new LazyLoad({
          callback_error: (img) => {
            img.setAttribute('src', this.profileFallbackImage)
          },
          unobserve_entered: true,
          unobserve_completed: true,
        })
        this.lazyLoadInstance.update()
      },
      async loadTopics() {
        let topics = await this.$store.dispatch('app/fetchTopics')

        this.allTopics = topics
      },
    },
    watch: {
      searchTerm() {
        this.$nextTick(() => {
          this.lazyLoadInstance.update()
        })
      },
      topics() {
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

    .people-section-search-input {
      width: 200px;
    }

    .people-section-filters {
      display: flex;

      > div {
        margin-right: 1rem;
      }
    }
  }
</style>
