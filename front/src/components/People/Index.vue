<template>
  <div class="people-section">
    <h3 class="is-size-3 has-text-weight-bold mb-4">People</h3>

    <div class="people-section-filters mb-4">
      <SearchInput v-model="$store.state.people.peopleSearchTerm" />

      <div class="mt-4 mb-2">
        <ActionButton :button="true" :disabled="loading" buttonText="Filters" :icon="filterIcon" @click="openFiltersModal()"></ActionButton>
        <ActionButton :button="true" :disabled="loading" class="ml-2" buttonText="Clear all" :icon="clearFiltersIcon" @click="clearAllFilters()"></ActionButton>
      </div>

      <div v-if="anyActiveFilters">
        <div class="people-section-active-filters-header">Active filters</div>

        <div class="people-section-active-filters mt-2 ml-4">
          <template v-for="(activeFilterValues, fieldMachineName) in $store.state.people.peopleActiveFilters">
            <div class="people-section-active-filter" v-if="isNotEmpty(activeFilterValues)">
              <div class="people-section-active-filter-title mt-1">
                <span>></span>
                {{ fieldTitle(fieldMachineName) }}
              </div>
              <div class="people-section-active-filter-values">
                <div class="people-section-active-filter-value" v-if="Array.isArray(activeFilterValues)" v-for="(activeFilterValue) in activeFilterValues">
                  <span>{{ activeFilterValue }}</span>
                  <img :src="closeIcon" @click="removeFilter(fieldMachineName, activeFilterValue)">
                </div>
                <div class="people-section-active-filter-value" v-if="activeFilterValues.tags" v-for="(tag) in activeFilterValues.tags">
                  <span>{{ tag }}</span>
                  <img :src="closeIcon" @click="removeFilterTag(fieldMachineName, tag)">
                </div>
                <template v-if="activeFilterValues.from || activeFilterValues.to">
                  <div class="people-section-active-filter-value">
                    <span>{{ calendarDateFormat(activeFilterValues.from) }} - {{ calendarDateFormat(activeFilterValues.to) }}</span>
                    <img :src="closeIcon" @click="removeDateRange(fieldMachineName)">
                  </div>
                </template>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <hr>

    <div class="people-section-actions">
      <ActionButton :button="true" :disabled="loading" buttonText="Export" :icon="exportIcon" @click="openExportPeopleModal()"></ActionButton>
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

    <SkeletonPatternLoader :loading="loading">
      <template v-slot:content>
        <div class="content people-section-content">
          <Person
            v-for="(person, index) in sortedPeople"
            :key="person.id"
            :person="person"
            :ref="'personRef_' + index"
          ></Person>
        </div>
      </template>

      <template v-slot:skeleton>
        <div class="content people-section-content">
          <div class="ssc-wrapper mb-4 is-flex is-flex-direction-column is-align-items-center" v-for="n in 30" :key="n">
            <div class="ssc-head-line w-80 mb-4"></div>
            <div class="ssc-head-line w-80 mb-4"></div>
            <div class="ssc-square"></div>
          </div>
        </div>
      </template>
    </SkeletonPatternLoader>
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
    <template v-for="filter in $store.state.people.peopleFilters">
      <div v-if="filter.field_input_type !== 'tags_range'">
        <label class="label">{{ filter.field_title }}</label>

        <VueMultiselect
          class="mb-2"
          v-model="$store.state.people.peopleActiveFilters[filter.field_machine_name]"
          :multiple="true"
          :options="filter.values"
        >
        </VueMultiselect>
      </div>

      <div v-if="filter.field_input_type === 'tags_range' && isFilterInitialized(filter.field_machine_name)">
        <label class="label">{{ filter.field_title }}</label>

        <VueMultiselect
          class="mb-2"
          v-model="$store.state.people.peopleActiveFilters[filter.field_machine_name]['tags']"
          :multiple="true"
          :options="filter.values"
        >
        </VueMultiselect>

        <div class="people-section-filters-filter-time-range mb-2">
          <date-picker
            v-model:value="$store.state.people.peopleActiveFilters[filter.field_machine_name]['from']"
            placeholder="From"
            format="MMMM D, Y"
            type="date"
            value-type="format"
            input-class="input"
            :append-to-body="false"
          ></date-picker>

          <date-picker
            v-model:value="$store.state.people.peopleActiveFilters[filter.field_machine_name]['to']"
            placeholder="To"
            format="MMMM D, Y"
            type="date"
            value-type="format"
            input-class="input"
            :append-to-body="false"
          ></date-picker>
        </div>
      </div>
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

    <div class="mt-2 is-flex is-flex-direction-column">
      <button class="button" @click="exportToCsv()">CSV (firstname, lastname, email)</button>
      <button class="button mt-2" v-if="this.$store.state.user.currentUser.admin" @click="exportAllDataToCsv()">CSV (full data set)</button>
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
  import filterIcon from '@/assets/images/filter.svg'
  import closeIcon from '@/assets/images/close.svg'
  import arrowUpIcon from '@/assets/images/arrow_up.svg'
  import arrowDownIcon from '@/assets/images/arrow_down.svg'
  import clearFiltersIcon from '@/assets/images/filter_remove.svg'
  import exportIcon from '@/assets/images/export.svg'
  import ActionButton from '@/components/Shared/ActionButton.vue'
  import Modal from '@/components/Shared/Modal.vue'
  import { some, orderBy } from 'lodash'
  import SkeletonPatternLoader from '@/components/Shared/SkeletonPatternLoader.vue'
  import SearchInput from '@/components/Shared/SearchInput.vue'
  import { calendarDateFormat } from '@/lib/time_stuff'

  const apiUrl = import.meta.env.VITE_API_URL

  export default {
    name: 'PeopleIndex',
    components: {
      Person,
      VueMultiselect,
      ActionButton,
      Modal,
      SkeletonPatternLoader,
      SearchInput,
    },
    data() {
      return {
        lazyLoadInstance: null,
        profileFallbackImage: profileFallbackImage,
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
        loading: false,
        peopleActiveFiltersWatcherActive: true,
        calendarDateFormat,
      }
    },
    async created() {
      if (this.$store.state.people.people.length === 0 || this.$store.state.people.peopleMarkReload) {
        this.loading = true
        this.loadFilters()
        await this.loadPeople()
        this.$store.dispatch('people/setPeopleMarkReload', false)
        this.loading = false
      }

      this.initImgLazyLoad()
    },
    unmounted() {
      this.lazyLoadInstance.destroy()
      this.lazyLoadInstance = null
    },
    computed: {
      countedUsers() {
        const count = this.$store.state.people.people.length

        if (count === 1) {
          return `Found 1 user`
        } else {
          return `Found ${count} users`
        }
      },
      anyActiveFilters() {
        return some(
          this.$store.state.people.peopleActiveFilters,
          filter =>
            filter.length > 0 ||
            (filter.tags && filter.tags.length > 0) ||
            filter.from ||
            filter.to
        )
      },
      sortedPeople() {
        const sortingField = this.sortingActive.field

        if (!sortingField) {
          return []
        }

        return orderBy(this.$store.state.people.people, [person => {
          if (person[sortingField]) {
            return person[sortingField].toLowerCase()
          } else {
            if (sortingField === 'first_name') {
              if (person.middle_name) {
                return person.middle_name.toLowerCase()
              }

              if (person.last_name) {
                return person.last_name.toLowerCase()
              }
            }

            if (sortingField === 'last_name') {
              if (person.first_name) {
                return person.first_name.toLowerCase()
              }

              if (person.middle_name) {
                return person.middle_name.toLowerCase()
              }
            }
          }

          return ''
        }], [this.sortingActive.direction])
      },
    },
    methods: {
      async loadPeople() {
        let people = null
        people = await this.$store.dispatch('people/fetchPeople')

        this.$store.dispatch('people/setPeople', people)
      },
      initImgLazyLoad() {
        this.lazyLoadInstance = new LazyLoad({
          callback_error: (img) => {
            img.setAttribute('src', this.profileFallbackImage)
          },
          unobserve_entered: true,
          unobserve_completed: true,
        })
        this.$nextTick(() => {
          if (this.lazyLoadInstance) {
            this.lazyLoadInstance.update()
          }
        })
      },
      async reloadView() {
        this.loading = true
        await this.loadPeople()
        this.loading = false

        this.$nextTick(() => {
          if (this.lazyLoadInstance) {
            this.lazyLoadInstance.update()
          }
        })
      },
      openFiltersModal() {
        this.filtersModalStatus = true
      },
      async loadFilters() {
        this.$store.state.people.peopleFilters = await this.$store.dispatch('people/fetchPeopleFilters')
      },
      applyFilters() {
        this.reloadView()
        this.filtersModalStatus = false
      },
      fieldTitle(fieldMachineName) {
        return this.$store.state.people.peopleFilters.filter(filter => filter.field_machine_name === fieldMachineName)[0].field_title
      },
      removeFilter(fieldMachineName, activeFilterValue) {
        this.$store.state.people.peopleActiveFilters[fieldMachineName] = this.$store.state.people.peopleActiveFilters[fieldMachineName].filter(filterValue => filterValue != activeFilterValue)
      },
      exportToCsv() {
        const peopleIdsToExport = this.$store.state.people.people.map((person) => person.id).join(',')
        this.exportModalStatus = false
        window.location.href = `${apiUrl}/api/people/export?format=csv&ids=${peopleIdsToExport}`
      },
      exportAllDataToCsv() {
        const peopleIdsToExport = this.$store.state.people.people.map((person) => person.id).join(',')
        this.exportModalStatus = false
        window.location.href = `${apiUrl}/api/people/exportAllData?format=csv&ids=${peopleIdsToExport}`
      },
      exportToPlain() {
        this.exportModalStatus = false
        this.exportPlainModalStatus = true
        this.exportPlainList = this.$store.state.people.people
          .filter((person) => person.email)
          .map((person) => person.email).join('\n')
      },
      exportToPlainCopyToClipboard() {
        window.navigator.clipboard.writeText(this.exportPlainList)
        this.awn.success('The list of emails has been copied to the clipboard.')
      },
      openExportPeopleModal() {
        if (this.$store.state.people.people.length === 0) {
          this.awn.warning('The list of people is empty, nothing to export.')
          return
        }

        this.exportModalStatus = true
      },
      clearAllFilters() {
        this.$store.dispatch('people/setPeopleActiveFilters', {})
        this.$store.dispatch('people/setPeopleSearchTerm', '')
      },
      isFilterInitialized(fieldMachineName) {
        this.peopleActiveFiltersWatcherActive = false

        if (!this.$store.state.people.peopleActiveFilters[fieldMachineName]) {
          this.$store.state.people.peopleActiveFilters[fieldMachineName] = {
            tags: [],
            from: '',
            to: '',
          }
        }

        this.$nextTick(() => {
          this.peopleActiveFiltersWatcherActive = true
        })

        return true
      },
      isNotEmpty(value) {
        if (Array.isArray(value)) {
          return value.length > 0
        } else if (typeof value === 'object') {
          return (value.tags && value.tags.length > 0) || value.from || value.to
        }

        return false
      },
      removeFilterTag(fieldMachineName, tag) {
        const tags = this.$store.state.people.peopleActiveFilters[fieldMachineName].tags
        this.$store.state.people.peopleActiveFilters[fieldMachineName].tags = tags.filter(t => t !== tag)
      },
      removeDateRange(fieldMachineName) {
        this.$store.state.people.peopleActiveFilters[fieldMachineName].from = ''
        this.$store.state.people.peopleActiveFilters[fieldMachineName].to = ''
      },
    },
    watch: {
      '$store.state.people.peopleSearchTerm': function() {
        this.reloadView()
        this.$nextTick(() => {
          this.lazyLoadInstance.update()
        })
      },
      '$store.state.people.peopleActiveFilters': {
        handler: function() {
          if (this.peopleActiveFiltersWatcherActive === false) {
            return
          }

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
        grid-template-columns: repeat(2, 1fr);
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

    .people-section-filters {
      > div {
        margin-right: 1rem;
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

  .people-section-filters-filter-time-range {
    display: flex;

    > div {
      width: 50%;
    }

    > div:first-child {
      margin-right: 1rem;
    }
  }
</style>
