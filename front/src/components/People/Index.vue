<template>
  <div class="people-section">
    <h3 class="is-size-3 has-text-weight-bold mb-4">People</h3>

    <div class="people-section-filters mb-4">
      <div>
        <input
          type="text"
          v-model="searchTerm"
          placeholder="Filter by text"
          class="input mb-4"
        >
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
  import Person from '@/components/People/Person.vue'
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
        interests: [],
        allInterests: [],
      }
    },
    computed: {
      filteredPeople() {
        const searchTerm = this.searchTerm.toLowerCase()

        return this.$store.state.app.people.filter((person) => {
          const searchText = `${person.first_name} ${person.last_name} ${person.bio}`.toLowerCase()

          const hasSearchTerm = searchText.includes(searchTerm)

          return hasSearchTerm
        })
      },
    },
    created() {
      const that = this

      this.mitt.emit('spinnerStart')
      this.mitt.on('setInterestActive', (interest) => that.setInterestActive(interest))
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
        this.mitt.emit('spinnerStop')
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
      setInterestActive(interest) {
        if (!this.interests.includes(interest)) {
          this.interests.push(interest)

          document.querySelector('body').scrollIntoView()
        }
      },
    },
    watch: {
      searchTerm() {
        this.$nextTick(() => {
          this.lazyLoadInstance.update()
        })
      },
      interests() {
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

      input::placeholder {
        color: rgba(54, 54, 54, 0.3);
      }
    }
  }
</style>
