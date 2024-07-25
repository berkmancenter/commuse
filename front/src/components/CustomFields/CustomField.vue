<template>
  <div class="field custom-field" ref="fieldContainer">
    <label class="label" v-if="titleVisible()">{{ label }}</label>
    <div class="user-profile-subtitle"
         v-if="description"
         v-html="description"
    ></div>

    <div class="control" v-if="type == 'short_text'">
      <div class="control">
        <input type="text" class="input" :value="value" @input="updateSimpleValue">
      </div>
    </div>

    <div class="control" v-if="type == 'long_text'">
      <div class="control">
        <textarea class="textarea" :value="value" @input="updateSimpleValue"></textarea>
      </div>
    </div>

    <div class="control" v-if="type == 'tags'">
      <div class="control">
        <VueMultiselect
          :model-value="value"
          @update:modelValue="updateMultiValue"
          :multiple="metadata.allowMultiple ?? false"
          :taggable="metadata.allowNewValues ?? false"
          :options="metadata.possibleValues ?? []"
          tag-placeholder="Add"
          :placeholder="metadata.allowNewValues ? 'Search or add new' : 'Search'"
          @tag="addTag"
        >
        </VueMultiselect>
      </div>
    </div>

    <div v-if="type == 'tags_range'">
      <div class="box" v-for="(item, index) in storeObject[machineName]">
        <span title="Remove item" @click="removeTagRangeItem(index)"><Icon :src="minusIcon" /></span>

        <div>
          <label class="label">{{ metadata.tagName ?? 'Item name' }}</label>
          <div class="control">
            <div class="control">
              <VueMultiselect
                v-model="item.tags"
                :multiple="true"
                :taggable="metadata.allowNewValues ?? false"
                :options="metadata.possibleValues ?? []"
                @tag="addTagRange"
                :placeholder="metadata.allowNewValues ? 'Select or add new' : 'Select'"
              >
              </VueMultiselect>
            </div>
          </div>

          <label class="label">From</label>
          <div class="control">
            <div class="control">
              <VueMultiselect
                v-model="item.from"
                :multiple="false"
                :options="rangeYearsOptionsFrom()"
                placeholder="Select"
              >
              </VueMultiselect>
            </div>
          </div>

          <label class="label">To</label>
          <div class="control">
            <div class="control">
              <VueMultiselect
                v-model="item.to"
                :multiple="false"
                :options="rangeYearsOptionsTo(item)"
                placeholder="Select"
              >
              </VueMultiselect>
            </div>
          </div>
        </div>
      </div>

      <span title="Add more" @click="addEmptyTagRangeItem"><Icon :src="addIcon" /></span>
    </div>

    <div v-if="type == 'multi'">
      <div class="box" v-for="(item, index) in storeObject[machineName]">
        <span title="Remove item" @click="removeMultiItem(index)"><Icon :src="minusIcon" /></span>

        <div>
          <ProfileField
            :label="childField.title"
            :type="childField.input_type"
            :machine-name="childField.machine_name"
            :metadata="childField.metadata"
            :field-data="childField"
            v-bind:value="item[childField.machine_name]"
            v-on:update:value="item[childField.machine_name] = $event"
            v-for="childField in fieldData.child_fields"
          ></ProfileField>
        </div>
      </div>

      <span title="Add more" @click="addEmptyMultiItem"><Icon :src="addIcon" /></span>
    </div>
  </div>
</template>

<script>
  import VueMultiselect from 'vue-multiselect'
  import Icon from '@/components/Shared/Icon.vue'
  import addIcon from '@/assets/images/add.svg'
  import minusIcon from '@/assets/images/minus_light.svg'

  export default {
    name: 'ProfileField',
    props: {
      label: String,
      description: String,
      type: String,
      machineName: String,
      metadata: Object,
      value: null,
      fieldData: Object,
      storeObject: Object,
      groupDescription: String,
    },
    data() {
      return {
        apiUrl: import.meta.env.VITE_API_URL,
        addIcon,
        minusIcon,
      }
    },
    components: {
      VueMultiselect,
      Icon,
    },
    methods: {
      updateSimpleValue(ev) {
        this.$emit('update:value', ev.target.value)
      },
      updateMultiValue(value) {
        this.$emit('update:value', value)
      },
      removeMultiValue(option) {
        let currentValue = this.value
        this.$emit('update:value', currentValue.filter(item => item !== option))
      },
      addEmptyTagRangeItem() {
        this.$store.dispatch('app/addEmptyTagRangeItem', this.machineName)
      },
      addEmptyMultiItem() {
        this.$store.dispatch('app/addEmptyMultiItem', this.machineName)
      },
      removeTagRangeItem(index) {
        this.$store.dispatch('app/removeTagRangeItem', {
          index: index,
          machineName: this.machineName,
        })
      },
      removeMultiItem(index) {
        this.$store.dispatch('app/removeMultiItem', {
          index: index,
          machineName: this.machineName,
        })
      },
      addTag (newOption) {
        this.metadata.possibleValues.push(newOption)

        this.$store.dispatch('app/addTag', {
          key: this.machineName,
          newOption: newOption,
        })
      },
      addTagRange (newOption) {
        this.metadata.possibleValues.push(newOption)

        this.$store.dispatch('app/addTagRange', {
          key: this.machineName,
          newOption: newOption,
        })
      },
      rangeYearsOptionsFrom() {
        return Array.from(Array(new Date().getFullYear() - 1995 + 1), (_, index) => new Date().getFullYear() - index)
      },
      rangeYearsOptionsTo(item) {
        let toOptions = [];

        if (this.metadata.disableRangeToNowValues && item.tags.some(item => this.metadata.disableRangeToNowValues.includes(item))) {
          if (item.to === 'Now') {
            item.to = null
          }

          toOptions = [(new Date().getFullYear() + 1)].concat(this.rangeYearsOptionsFrom())
        } else {
          toOptions = ['Now', (new Date().getFullYear() + 1)].concat(this.rangeYearsOptionsFrom())
        }

        return toOptions
      },
      validate() {
        if (this.type === 'tags_range') {
          let errorMessages = []
          let valid = true

          this.storeObject[this.machineName]?.forEach((fieldItem) => {
            if (!fieldItem.to) {
              valid = false
              errorMessages.push(`<span class="has-text-weight-bold">To</span> value in the <span class="has-text-weight-bold">${this.label}</span> field must be set.`)
            }

            if (!fieldItem.from) {
              valid = false
              errorMessages.push(`<span class="has-text-weight-bold">From</span> value in the <span class="has-text-weight-bold">${this.label}</span> field must be set.`)
            }

            if (!fieldItem?.tags?.length) {
              valid = false
              errorMessages.push(`<span class="has-text-weight-bold">${this.fieldData?.metadata?.tagName}</span> value in the <span class="has-text-weight-bold">${this.label}</span> field must be set.`)
            }
          })

          // Remove duplicates
          errorMessages = [...new Set(errorMessages)]

          if (valid === false) {
            return {
              status: false,
              message: errorMessages.join('<br>'),
            }
          }
        }

        return {
          status: true,
          message: '',
        }
      },
      convertRemToPixels(rem) {
        return rem * parseFloat(getComputedStyle(document.documentElement).fontSize)
      },
      titleVisible() {
        if (this?.metadata && this.metadata.hideTitle) {
          return false
        }

        return true
      },
    },
  }
</script>
