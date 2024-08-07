<template>
  <div class="system-settings-header is-flex is-align-items-center">
    <h3 class="is-size-3 has-text-weight-bold">System settings</h3>
    <ActionButton class="ml-2" buttonText="Save" :icon="saveIcon" :button="true" @click="saveSettings()"></ActionButton>
  </div>

  <div class="system-settings-section">
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
        this.mitt.emit('spinnerStart')

        let settings = await this.$store.dispatch('systemSettings/fetchSystemSettings')

        this.mitt.emit('spinnerStop')

        this.$store.dispatch('systemSettings/setSystemSettings', settings)
      },
      camelCaseToTitleCase(str) {
        let result = str.replace(/([A-Z])/g, ' $1').trim()

        return result.charAt(0).toUpperCase() + result.slice(1)
      },
      async saveSettings() {
        this.mitt.emit('spinnerStart')

        const response = await this.$store.dispatch('systemSettings/saveSystemSettings', this.$store.state.systemSettings.systemSettings)

        this.mitt.emit('spinnerStop')

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
