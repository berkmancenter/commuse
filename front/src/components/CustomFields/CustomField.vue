<template>
  <div class="field custom-field" ref="fieldContainer">
    <label class="label" v-if="titleVisible()">{{ label }}</label>
    <div class="user-profile-subtitle"
         v-if="description"
         v-html="description"
    ></div>

    <div class="control" v-if="type == 'short_text'">
      <div class="control">
        <input type="text" class="input" :value="value" @input="updateSimpleValue" :disabled="disabled()">
      </div>
    </div>

    <div class="control" v-if="type == 'long_text'">
      <div class="control">
        <textarea class="textarea" :value="value" @input="updateSimpleValue" :disabled="disabled()"></textarea>
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
          :disabled="disabled()"
        >
        </VueMultiselect>
      </div>
    </div>

    <div v-if="type == 'tags_range'">
      <div class="box" v-for="(item, index) in formatTagsRangeValue(storeObject[machineName])">
        <span title="Remove item" @click="removeTagRangeItem(index)" v-if="!disabled() && !forceOneItem"><Icon :src="minusIcon" /></span>

        <div>
          <label class="label" :for="`${machineName}-tags-${index}`">{{ metadata.tagName ?? 'Item name' }}</label>
          <div class="control mb-2">
            <div class="control">
              <VueMultiselect
                v-model="item.tags"
                :multiple="true"
                :taggable="metadata.allowNewValues ?? false"
                :options="metadata.possibleValues ?? []"
                @tag="addTagRange"
                :placeholder="metadata.allowNewValues ? 'Select or add new' : 'Select'"
                :disabled="disabled()"
                :id="`${machineName}-tags-${index}`"
                :ref="`${machineName}-tags-${index}`"
                @update:modelValue="updateTagsRangeTags(item, index)"
              >
              </VueMultiselect>
            </div>
          </div>

          <label class="label" :for="`${machineName}-from-${index}`">From</label>
          <div class="control">
            <div class="control mb-2">
              <date-picker
                v-model:value="item.from"
                format="MMMM D, Y"
                type="date"
                value-type="format"
                input-class="input"
                :clearable="false"
                :disabled="disabled()"
                :ref="`${machineName}-from-${index}`"
                :input-attr="{ id: `${machineName}-from-${index}` }"
              ></date-picker>
            </div>
          </div>

          <label class="label" :for="`${machineName}-to-${index}`">To</label>
          <div class="control">
            <div class="control">
              <date-picker
                v-model:value="item.to"
                format="MMMM D, Y"
                type="date"
                value-type="format"
                input-class="input"
                :clearable="false"
                :disabled="disabled()"
                :ref="`${machineName}-to-${index}`"
                :input-attr="{ id: `${machineName}-to-${index}` }"
              ></date-picker>
            </div>
          </div>

          <div class="mt-2" v-if="metadata.autoExtend && this.$store.state.user.currentUser.admin">
            <label class="label" :for="`${machineName}-auto-extend-${index}`">Auto-extend</label>
            <div class="control ml-2">
              <input
                type="checkbox"
                :id="`${machineName}-auto-extend-${index}`"
                v-model="item.autoExtend"
                :ref="`${machineName}-auto-extend-${index}`"
              >
            </div>
          </div>
        </div>
      </div>

      <span 
        title="Add more"
        @click="addEmptyTagRangeItem"
        v-if="!disabled() && ((!storeObject[machineName]) || (storeObject[machineName].length === 0) || (storeObject[machineName].length > 0 && metadata.multipleItems))">
        <Icon :src="addIcon" />
      </span>
    </div>

    <div v-if="type == 'multi'">
      <div class="box" v-for="(item, index) in storeObject[machineName]">
        <span title="Remove item" @click="removeMultiItem(index)" v-if="!disabled() && !forceOneItem"><Icon :src="minusIcon" /></span>

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

      <span title="Add more" @click="addEmptyMultiItem" v-if="!disabled()"><Icon :src="addIcon" /></span>
    </div>
  </div>
</template>

<script>
  import VueMultiselect from 'vue-multiselect'
  import Icon from '@/components/Shared/Icon.vue'
  import addIcon from '@/assets/images/add.svg'
  import minusIcon from '@/assets/images/minus_light.svg'
  import { calendarDateFormat } from '@/lib/time_stuff.js'

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
      hideTitle: Boolean,
      autoPopulateFirstItem: Boolean,
      forceOneItem: Boolean,
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
    mounted() {
      this.checkIfAutoPopulateFirstItem()
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
        this.$store.dispatch('user/addEmptyTagRangeItem', {
          store: this.storeObject,
          key: this.machineName,
        })
      },
      addEmptyMultiItem() {
        this.$store.dispatch('user/addEmptyMultiItem', {
          store: this.storeObject,
          key: this.machineName,
        })
      },
      removeTagRangeItem(index) {
        this.$store.dispatch('user/removeTagRangeItem', {
          index: index,
          machineName: this.machineName,
        })
      },
      removeMultiItem(index) {
        this.$store.dispatch('user/removeMultiItem', {
          index: index,
          machineName: this.machineName,
        })
      },
      addTag (newOption) {
        this.metadata.possibleValues.push(newOption)

        this.$store.dispatch('user/addTag', {
          key: this.machineName,
          newOption: newOption,
        })
      },
      addTagRange (newOption) {
        this.metadata.possibleValues.push(newOption)

        this.$store.dispatch('user/addTagRange', {
          key: this.machineName,
          newOption: newOption,
        })
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
        if ((this?.metadata && this.metadata.hideTitle) || this.hideTitle) {
          return false
        }

        return true
      },
      disabled() {
        return this?.metadata && this?.metadata.editableOnlyByAdmins && !this.$store.state.user.currentUser.admin
      },
      checkIfAutoPopulateFirstItem() {
        if (this.autoPopulateFirstItem) {
          if (!this.storeObject[this.machineName]) {
            this.storeObject[this.machineName] = []
          }

          if (this.type == 'tags_range') {
            this.addEmptyTagRangeItem()
          } else if (this.type == 'multi') {
            this.addEmptyMultiItem()
          }
        }
      },
      formatTagsRangeValue(value) {
        if (!value) {
          return []
        }

        return value.map((item) => {
          if (item.to) {
            item.to = calendarDateFormat(item.to)
          }

          if (item.from) {
            item.from = calendarDateFormat(item.from)
          }

          return item
        })
      },
      updateTagsRangeTags(item, index) {
        if (this.metadata.autoExtend && this.metadata.autoExtendValuesAutoSelect && this.metadata.autoExtendValuesAutoSelect instanceof Array) {
          let autoExtend = this.metadata.autoExtendValuesAutoSelect.some((value) => item.tags.includes(value))

          if (autoExtend) {
            item.autoExtend = true
          }
        }
      },
    },
  }
</script>

<style lang="scss">
  .custom-field {
    .label {
      display: inline-flex;
    }
  }
</style>
