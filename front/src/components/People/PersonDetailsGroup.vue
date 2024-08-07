<template>
  <div class="panel people-section-details-group">
    <p class="panel-heading">
      {{ customGroupTitle ?? group.title }}
    </p>
    <div class="panel-block">
      <div class="content">
        <template v-for="field in group.custom_fields">
          <div class="people-section-details-data-item" v-if="(person[field.machine_name] && person[field.machine_name] != '' && person[field.machine_name] != [] && !field?.metadata?.isImportProfileImageLink) || (field.input_type === 'multi' && getMultiFieldValue(field, person).length > 0)">
            <div class="people-section-details-data-item-label" v-if="titleVisible(field)">{{ field.title }}</div>
            <div v-if="field.input_type === 'short_text' || field.input_type === 'long_text'">
              <div v-if="!field.metadata.isLink">{{ person[field.machine_name] }}</div>
              <div v-if="field.metadata.isLink"><a :href="person[field.machine_name]" target="_blank">{{ person[field.machine_name] }}</a></div>
            </div>
            <div v-if="field.input_type === 'tags'">
              <div class="tags are-medium">
                <div @click="activatePeopleFilter(field.machine_name, value)" class="tag is-clickable" v-for="value in person[field.machine_name]">{{ value }}</div>
              </div>
            </div>
            <div v-if="field.input_type === 'tags_range'">
              <ul>
                <li v-for="item in person[field.machine_name]">
                  {{ item.tags.join(', ') }}, {{ item.from }}-{{ item.to }}
                </li>
              </ul>
            </div>
            <div v-if="field.input_type === 'multi'">
              <ul>
                <li v-for="item in getMultiFieldValue(field, person)">
                  <span v-for="(itemPart, index) in item">{{ itemPart }}<span v-if="index != item.length - 1">, </span></span>
                </li>
              </ul>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
  import { some } from 'lodash'
  import { getMultiFieldValue } from '@/lib/fields/multi.js'

  export default {
    name: 'PersonDetailsGroup',
    data() {
      return {
        getMultiFieldValue,
      }
    },
    props: {
      group: {
        type: Object,
        required: true,
      },
      person: {
        type: Object,
        required: true,
      },
      customGroupTitle: {
        type: String,
        required: false,
      },
    },
    methods: {
      activatePeopleFilter(fieldMachineName, value) {
        const hasValue = some(this.$store.state.people.peopleActiveFilters[fieldMachineName], filterValue => filterValue === value)

        if (hasValue === false) {
          if (!this.$store.state.people.peopleActiveFilters[fieldMachineName]) {
            this.$store.state.people.peopleActiveFilters[fieldMachineName] = []
          }

          this.$store.state.people.peopleActiveFilters[fieldMachineName].push(value)
        }

        this.$store.dispatch('people/setPeopleMarkReload', true)
        this.$router.push({
          name: 'people.index',
        })
      },
      titleVisible(field) {
        if (field?.metadata && field.metadata.hideTitle) {
          return false
        }

        return true
      },
    },
  }
</script>

<style lang="scss">
  .people-section-details-group {
    .people-section-details-data-item {
      padding-bottom: 0.5rem;
      margin-bottom: 0.5rem;
      border-bottom: 1px solid #dbdbdb;
      overflow-wrap: anywhere;

      &:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
      }

      .people-section-details-data-item-label {
        font-weight: bold;
        margin-bottom: 0.2rem;
      }
    }
  }
</style>
