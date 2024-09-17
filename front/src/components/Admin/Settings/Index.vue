<template>
  <div class="system-settings-header is-flex is-align-items-center">
    <h3 class="is-size-3 has-text-weight-bold">System settings</h3>
    <ActionButton :button="true" :disabled="loading" class="ml-2" buttonText="Save" :icon="saveIcon" @click="saveSettings()"></ActionButton>
  </div>

  <div class="system-settings-section" v-if="!loading">
    <form class="form-commuse-blocks mb-4" @submit.prevent="changePassword">
      <div class="panel mb-4" v-for="(systemField, key) in $store.state.systemSettings.systemSettings">
        <p class="panel-heading">
          {{ camelCaseToTitleCase(key) }}
        </p>
        <div class="panel-block">
          <div class="field">
            <div class="control">
              <div class="control">
                <div v-if="systemField.type === 'string'">
                  <textarea class="textarea" type="text" v-model="systemField.value"></textarea>
                </div>
                <div v-if="systemField.type === 'long_text_rich'">
                  <ckeditor :editor="editor" v-model="systemField.value" :config="editorConfig"></ckeditor>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <div class="system-settings-section ssc form-commuse-blocks" v-if="loading">
    <div class="ssc-card ssc-wrapper mb-4" v-for="n in 10" :key="n">
      <div class="ssc-head-line mb-4"></div>
      <div class="ssc-square"></div>
    </div>
  </div>
</template>

<script>
  import { ClassicEditor, Bold, Essentials, Italic, Paragraph, Undo, Link } from 'ckeditor5'
  import StickyElement from 'vue-sticky-element'
  import ActionButton from '@/components/Shared/ActionButton.vue'
  import saveIcon from '@/assets/images/save.svg'

  export default {
    name: 'SystemSettings',
    data() {
      return {
        settings: [],
        editor: ClassicEditor,
        editorConfig: {
          plugins: [ Bold, Essentials, Italic, Paragraph, Undo, Link ],
          toolbar: [ 'undo', 'redo', '|', 'bold', 'italic', '|', 'link' ],
        },
        saveIcon,
        loading: true,
      }
    },
    created() {
      this.loadSettings()
    },
    components: {
      StickyElement,
      ActionButton,
    },
    methods: {
      async loadSettings() {
        let settings = await this.$store.dispatch('systemSettings/fetchSystemSettings')

        this.$store.dispatch('systemSettings/setSystemSettings', settings)

        this.loading = false
      },
      camelCaseToTitleCase(str) {
        let result = str.replace(/([A-Z])/g, ' $1').trim()

        return result.charAt(0).toUpperCase() + result.slice(1)
      },
      async saveSettings() {
        const response = await this.$store.dispatch('systemSettings/saveSystemSettings', this.$store.state.systemSettings.systemSettings)

        if (response.ok) {
          this.awn.success('System settings have been saved.')
        } else {
          this.awn.warning('Something went wrong, try again.')
        }
      },
    },
  }
</script>

<style lang="scss">
  .system-settings-section {
    position: relative;
    padding-top: 5rem;
    z-index: 1;
  }

  .system-settings-header {
    position: fixed;
    background-color: #ffffff;
    width: 100%;
    z-index: 2;
    margin-top: calc(-2rem + 8px);
    padding: 1rem 0;
    padding-left: 1rem;
    border-bottom: 2px solid var(--greyish-color);
  }
</style>
