<template>
  <div class="field">
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
          :multiple="true"
          :taggable="true"
          :options="[]"
          tag-placeholder="Add"
          placeholder="Search or add new"
          @tag="addTag"
        >
        </VueMultiselect>
      </div>
    </div>

    <div v-if="type == 'tags_range'">
      <div class="box" v-for="(item, index) in $store.state.app.userProfile[machineName]">
        <span title="Remove item" @click="removeTagRangeItem(index)"><Icon :src="minusIcon" /></span>

        <div>
          <label class="label">{{ metadata.tag_title }}</label>
          <div class="control">
            <div class="control">
              <VueMultiselect
                v-model="item.tags"
                :multiple="true"
                :options="metadata.possible_values"
                placeholder="Select"
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
                :options="rangeYearsOptions"
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
                :options="rangeYearsOptions"
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
    created() {
      
    },
    computed: {
      rangeYearsOptions() {
        return ['Now'].concat(Array.from(Array(new Date().getFullYear() - 1995 + 1), (_, index) => new Date().getFullYear() - index))
      },
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
        this.$store.dispatch('app/addTag', {
          key: this.machineName,
          newOption: newOption,
        })
      },
    },
  }
</script>
