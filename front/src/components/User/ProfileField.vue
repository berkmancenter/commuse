<template>
  <div class="field user-profile-field" ref="fieldContainer">
    <label class="label">{{ label }}</label>

    <div class="control" v-if="type == 'short_text'">
      <div class="control">
        <input type="text" class="input" :value="value" @input="updateValue">
      </div>
    </div>

    <div class="control" v-if="type == 'long_text'">
      <div class="control">
        <textarea class="textarea" :value="value" @input="updateValue"></textarea>
      </div>
    </div>

    <div class="control" v-if="type == 'tags'">
      <div class="control">
        <VueMultiselect
          v-model="$store.state.app.userProfile[machineName]"
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
      <div class="box" v-for="(item, index) in $store.state.app.userProfile[machineName]">
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
                :options="rangeYearsOptionsTo()"
                placeholder="Select"
              >
              </VueMultiselect>
            </div>
          </div>
        </div>
      </div>

      <span title="Add more" @click="addEmptyTagRangeItem"><Icon :src="addIcon" /></span>
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
      type: String,
      machineName: String,
      metadata: Object,
      value: null,
      fieldData: Object,
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
      updateValue(ev) {
        this.$emit('update:value', ev.target.value)
      },
      addEmptyTagRangeItem() {
        this.$store.dispatch('app/addEmptyTagRangeItem', this.machineName)
      },
      removeTagRangeItem(index) {
        this.$store.dispatch('app/removeTagRangeItem', {
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
      rangeYearsOptionsTo() {
        return ['Now', (new Date().getFullYear() + 1)].concat(this.rangeYearsOptionsFrom())
      },
      validate() {
        if (this.type === 'tags_range') {
          let errorMessages = []
          let valid = true

          this.$store.state.app.userProfile[this.machineName]?.forEach((fieldItem) => {
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
    },
  }
</script>
