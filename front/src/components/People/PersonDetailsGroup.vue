<template>
  <div class="panel people-section-details-group" v-if="hasFields(group)">
    <p class="panel-heading">
      {{ customGroupTitle ?? group.title }}
    </p>
    <div class="panel-block">
      <div class="content">
        <template v-for="field in group.custom_fields">
          <div class="people-section-details-data-item" v-if="person[field.machine_name] && person[field.machine_name] != '' && person[field.machine_name] != [] && !field?.metadata?.isImportProfileImageLink">
            <div class="people-section-details-data-item-label">{{ field.title }}</div>
            <div v-if="!Array.isArray(person[field.machine_name])">
              <div v-if="!field.metadata.isLink">{{ person[field.machine_name] }}</div>
              <div v-if="field.metadata.isLink"><a :href="person[field.machine_name]" target="_blank">{{ person[field.machine_name] }}</a></div>
            </div>
            <div v-if="Array.isArray(person[field.machine_name])">
              <div class="tags are-medium">
                <div class="tag" v-for="value in person[field.machine_name]">{{ value }}</div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
  import { flatten, compact } from 'lodash'

  export default {
    name: 'PersonDetailsGroup',
    data() {
      return {}
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
      hasFields(group) {
        return group.custom_fields.some(field => {
          if (field?.metadata?.isImportProfileImageLink) {
            return false
          }

          const fieldValue = compact(flatten([this.person[field.machine_name]]))

          return fieldValue.length > 0
        })
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
