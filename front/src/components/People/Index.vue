<template>
  <div class="people-section">
    <h3 class="is-size-3 has-text-weight-bold mb-4">People</h3>

    <div class="people-section-filters mb-4">
      <div class="people-section-filters-search">
        <input
          type="text"
          v-model="$store.state.app.peopleSearchTerm"
          placeholder="Search"
          class="input"
          @keyup="reloadView()"
        >
        <span><img :src="searchIcon"></span>
      </div>

      <div class="mt-4 mb-2">
        <ActionButton buttonText="Filters" :icon="filterIcon" @click="openFiltersModal()"></ActionButton>
        <ActionButton class="ml-2" buttonText="Clear all filters" :icon="clearFiltersIcon" @click="$store.state.app.peopleActiveFilters = {}"></ActionButton>
      </div>

      <div v-if="anyActiveFilters">
        <div class="people-section-active-filters-header">Active filters</div>

        <div class="people-section-active-filters mt-2 ml-4">
          <template v-for="(activeFilterValues, fieldMachineName) in $store.state.app.peopleActiveFilters">
            <div class="people-section-active-filter" v-if="activeFilterValues.length > 0">
              <div class="people-section-active-filter-title mt-1">
                <span>></span>
                {{ fieldTitle(fieldMachineName) }}
              </div>
              <div class="people-section-active-filter-values">
                <div class="people-section-active-filter-value" v-for="(activeFilterValue) in activeFilterValues">
                  <span>{{ activeFilterValue }}</span>
                  <img :src="closeIcon" @click="removeFilter(fieldMachineName, activeFilterValue)">
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <hr>

    <div class="people-section-actions">
      <ActionButton buttonText="Export" :icon="exportIcon" @click="openExportPeopleModal()"></ActionButton>
    </div>

    <hr>

    <div class="mt-2 people-section-sort-count mb-4">
      <div class="mt-2 people-section-counted-users">{{ countedUsers }}</div>

      <VueMultiselect
        v-model="sortingActive"
        :multiple="false"
        label="label"
        track-by="key"
        :options="sortingOptions"
        deselectLabel=""
        selectLabel=""
        selectedLabel=""
        :preselectFirst="true"
        :allowEmpty="false"
        :searchable="false"
      >
        <template v-slot:singleLabel="{ option }">
          <span class="people-section-sort-option-container">
            <span class="people-section-sort-option-label">{{ option.label }}</span>
            <span class="people-section-sort-option-arrow"><img :src="option.arrow"></span>
          </span>
        </template>
        <template v-slot:option="{ option }">
          <span class="people-section-sort-option-container">
            <span class="people-section-sort-option-label">{{ option.label }}</span>
            <span class="people-section-sort-option-arrow"><img :src="option.arrow"></span>
          </span>
        </template>
      </VueMultiselect>
    </div>

    <div class="is-clearfix"></div>

    <div class="content people-section-content">
      <Person
        v-for="(person, index) in sortedPeople"
        :key="person.id"
        :person="person"
        :ref="'personRef_' + index"
      ></Person>
    </div>
  </div>

  <Modal
    v-model="filtersModalStatus"
    title="Filter people"
    :focusOnConfirm="false"
    :centerVertically="false"
    :showConfirmButton="false"
    cancelButtonTitle="Close"
    @cancel="filtersModalStatus = false"
  >
    <template v-for="filter in $store.state.app.peopleFilters">
      <div class="mb-2">{{ filter.field_title }}</div>

      <VueMultiselect
        class="mb-2"
        v-model="$store.state.app.peopleActiveFilters[filter.field_machine_name]"
        :multiple="true"
        :options="filter.values"
      >
      </VueMultiselect>
    </template>
  </Modal>

  <Modal
    v-model="exportModalStatus"
    title="Export people"
    :focusOnConfirm="false"
    :showConfirmButton="false"
    cancelButtonTitle="Close"
    @cancel="exportModalStatus = false"
  >
    Export people as:

    <div class="mt-2">
      <button class="button" @click="exportToCsv()">CSV (firstname, lastname, email)</button>
      <button class="button mt-2" @click="exportToPlain()">Plain text list of emails</button>
    </div>
  </Modal>

  <Modal
    v-model="exportPlainModalStatus"
    title="Exported people"
    :focusOnConfirm="false"
    :showConfirmButton="false"
    cancelButtonTitle="Close"
    @cancel="exportPlainModalStatus = false"
  >
    <ActionButton class="mb-4" buttonText="Copy to clipboard" :icon="filterIcon" @click="exportToPlainCopyToClipboard()"></ActionButton>

    <textarea class="textarea" v-model="exportPlainList"></textarea>
  </Modal>
</template>

<script>
  import LazyLoad from 'vanilla-lazyload'
  import Person from '@/components/People/Person.vue'
  import profileFallbackImage from '@/assets/images/profile_fallback.png'
  import VueMultiselect from 'vue-multiselect'
  import searchIcon from '@/assets/images/search.svg'
  import filterIcon from '@/assets/images/filter.svg'
  import closeIcon from '@/assets/images/close.svg'
  import arrowUpIcon from '@/assets/images/arrow_up.svg'
  import arrowDownIcon from '@/assets/images/arrow_down.svg'
  import clearFiltersIcon from '@/assets/images/filter_remove.svg'
  import exportIcon from '@/assets/images/export.svg'
  import ActionButton from '@/components/Shared/ActionButton.vue'
  import Modal from '@/components/Shared/Modal.vue'
  import { some, orderBy } from 'lodash'

  const apiUrl = import.meta.env.VITE_API_URL

  export default {
    name: 'PeopleIndex',
    components: {
      Person,
      VueMultiselect,
      ActionButton,
      Modal,
    },
    data() {
      return {
        lazyLoadInstance: null,
        profileFallbackImage: profileFallbackImage,
        searchIcon,
        filterIcon,
        closeIcon,
        clearFiltersIcon,
        exportIcon,
        filtersModalStatus: false,
        sortingOptions: [
          {
            label: 'First name',
            arrow: arrowUpIcon,
            key: 'firstname_asc',
            field: 'first_name',
            direction: 'asc',
          },
          {
            label: 'First name',
            arrow: arrowDownIcon,
            key: 'firstname_desc',
            field: 'first_name',
            direction: 'desc',
          },
          {
            label: 'Last name',
            arrow: arrowUpIcon,
            key: 'lastname_asc',
            field: 'last_name',
            direction: 'asc',
          },
          {
            label: 'Last name',
            arrow: arrowDownIcon,
            key: 'lastname_desc',
            field: 'last_name',
            direction: 'desc',
          },
        ],
        sortingActive: '',
        exportModalStatus: false,
        exportPlainModalStatus: false,
        exportPlainList: '',
      }
    },
    created() {
      this.loadPeople()
      this.loadFilters()
      this.initLazyLoad()
    },
    computed: {
      countedUsers() {
        const count = this.$store.state.app.people.length

        if (count === 1) {
          return `Found 1 user`
        } else {
          return `Found ${count} users`
        }
      },
      anyActiveFilters() {
        return some(this.$store.state.app.peopleActiveFilters, filter => filter.length > 0)
      },
      sortedPeople() {
        const sortingField = this.sortingActive.field

        if (!sortingField) {
          return []
        }

        return orderBy(this.$store.state.app.people, [person => person[sortingField].toLowerCase()], [this.sortingActive.direction])
      },
    },
    methods: {
      async loadPeople() {
        this.mitt.emit('spinnerStart')
        const searchTermEntering = this.$store.state.app.peopleSearchTerm

        let people = null
        try {
          people = await this.$store.dispatch('app/fetchPeople')
        } catch (error) {
          this.mitt.emit('spinnerStop')
          return
        }

        if (this.$store.state.app.peopleSearchTerm !== searchTermEntering) {
          this.mitt.emit('spinnerStop')
          return
        }

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
      reloadView() {
        this.loadPeople()
      },
      openFiltersModal() {
        this.filtersModalStatus = true
      },
      async loadFilters() {
        this.mitt.emit('spinnerStart')

        this.$store.state.app.peopleFilters = await this.$store.dispatch('app/fetchPeopleFilters')

        this.mitt.emit('spinnerStop')
      },
      applyFilters() {
        this.reloadView()
        this.filtersModalStatus = false
      },
      fieldTitle(fieldMachineName) {
        return this.$store.state.app.peopleFilters.filter(filter => filter.field_machine_name === fieldMachineName)[0].field_title
      },
      abortFetchPeopleRequest() {
        if (this.$store.state.app.peopleFetchController) {
          this.$store.state.app.peopleFetchController.abort()
        }
      },
      removeFilter(fieldMachineName, activeFilterValue) {
        this.$store.state.app.peopleActiveFilters[fieldMachineName] = this.$store.state.app.peopleActiveFilters[fieldMachineName].filter(filterValue => filterValue != activeFilterValue)
      },
      exportToCsv() {
        const peopleIdsToExport = this.$store.state.app.people.map((person) => person.id).join(',')
        this.exportModalStatus = false
        window.location.href = `${apiUrl}/api/people/export?format=csv&ids=${peopleIdsToExport}`
      },
      exportToPlain() {
        this.exportModalStatus = false
        this.exportPlainModalStatus = true
        this.exportPlainList = this.$store.state.app.people
          .filter((person) => person.email)
          .map((person) => person.email).join('\n')
      },
      exportToPlainCopyToClipboard() {
        window.navigator.clipboard.writeText(this.exportPlainList)
        this.awn.success('The list of emails has been copied to the clipboard.')
      },
      openExportPeopleModal() {
        if (this.$store.state.app.people.length === 0) {
          this.awn.warning('The list of people is empty, nothing to export.')
          return
        }

        this.exportModalStatus = true
      },
    },
    watch: {
      '$store.state.app.peopleSearchTerm': function() {
        this.abortFetchPeopleRequest()
        this.$nextTick(() => {
          this.lazyLoadInstance.update()
        })
      },
      '$store.state.app.peopleActiveFilters': {
        handler: function() {
          this.abortFetchPeopleRequest()
          this.$nextTick(() => {
            this.reloadView()
            this.lazyLoadInstance.update()
          })
        },
        deep: true,
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
      > div {
        margin-right: 1rem;
      }

      input {
        border-bottom: 2px solid var(--main-color);
        border-radius: 0;
      }

      input::placeholder {
        color: rgba(54, 54, 54, 0.8);
      }

      .people-section-filters-search {
        position: relative;
        max-width: 300px;

        span {
          display: block;
          width: 1.5rem;
          height: 1.5rem;
          position: absolute;
          top: 0;
          right: 0.5rem;
          height: 100%;
          display: flex;
          pointer-events: none;
        }
      }
    }

    .people-section-active-filters-header,
    .people-section-counted-users {
      border-bottom: 2px solid var(--main-color);
      display: inline-block;
    }

    .people-section-active-filters {
      .people-section-active-filter {
        .people-section-active-filter-values {
          display: flex;

          .people-section-active-filter-value {
            background-color: var(--secondary-color);
            padding: 0.2rem 0.6rem;
            border-radius: 1rem;
            color: #ffffff;
            margin-right: 1rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;

            img {
              width: 1.5rem;
              height: 1.5rem;
              display: block;
              margin-left: 0.5rem;
              cursor: pointer;
            }
          }
        }

        .people-section-active-filter-title {
          display: flex;
          align-items: center;

          span {
            color: var(--main-color);
            margin-right: 0.5rem;
          }
        }
      }
    }

    .people-section-sort-count {
      display: flex;

      > div {
        max-width: 200px;
      }

      .multiselect {
        margin-left: auto;
        cursor: pointer;
      }

      .multiselect__select {
        display: none;
      }

      .multiselect__tags {
        padding-right: 8px;
      }

      .people-section-sort-option-container {
        display: flex;
        align-items: center;

        img {
          height: 1.5rem;
          width: 1.5rem;
        }

        .people-section-sort-option-arrow {
          margin-left: auto;
        }
      }
    }
  }
</style>
