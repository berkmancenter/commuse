<template>
  <div class="admin-custom-fields">
    <h3 class="is-size-3 has-text-weight-bold mb-4">Custom Fields</h3>

    <form class="form">
      <admin-table :tableClasses="['admin-custom-fields-table']">
        <thead>
          <tr class="no-select">
            <th data-sort-method="none" class="no-sort">
              <input type="checkbox" ref="toggleAllCheckbox" @click="toggleAll()">
            </th>
            <th data-sort-default aria-sort="descending">Title</th>
            <th>Model</th>
            <th>Type</th>
            <th>Created</th>
            <th data-sort-method="none" class="no-sort">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="customField in customFields" :key="customField.id" class="no-break">
            <td class="admin-table-selector">
              <input type="checkbox" v-model="customField.selected">
            </td>
            <td>{{ customField.title }}</td>
            <td>{{ customField.model_name }}</td>
            <td>{{ customField.input_type }}</td>
            <td>{{ formattedTimestamp(customField.created_at) }}</td>
            <td class="admin-table-actions">
              <a title="Edit custom field" @click.prevent="openEditFieldModal(customField)">
                <Icon :src="editIcon" />
              </a>
            </td>
          </tr>
          <tr v-if="customFields.length === 0">
            <td colspan="4">No custom fields found.</td>
          </tr>
        </tbody>
      </admin-table>
    </form>

    <div ref="saveCustomFieldTemplate" class="is-hidden">
      <div class="content" onsubmit="return false">
        <div class="is-size-5 mb-4">Update custom field</div>

        <form class="form admin-custom-fields-form">
          <div class="field">
            <label class="label">Title</label>
            <div class="control">
              <div class="control">
                <input class="input" type="text" name="admin-custom-fields-form-title">
              </div>
            </div>
          </div>

          <div class="admin-custom-fields-form-tags-fields admin-custom-fields-form-specific-fields">
            <hr>

            <div class="field">
              <label class="label">Allow to set multiple values</label>
              <div class="control">
                <div class="control">
                  <input class="checkbox" type="checkbox" name="admin-custom-fields-form-allow-multiple-values">
                </div>
              </div>
            </div>

            <hr>

            <div class="field">
              <label class="label">Allow add new values by users</label>
              <div class="control">
                <div class="control">
                  <input class="checkbox" type="checkbox" name="admin-custom-fields-form-allow-add-new-values">
                </div>
              </div>
            </div>

            <hr>

            <div class="field">
              <label class="label">Allow user values to be suggested to other users</label>
              <div class="control">
                <div class="control">
                  <input class="checkbox" type="checkbox" name="admin-custom-fields-form-share-users-values">
                </div>
              </div>
            </div>
          </div>

          <div class="admin-custom-fields-form-tags-fields admin-custom-fields-form-tags_range-fields admin-custom-fields-form-specific-fields">
            <hr>

            <div class="field">
              <label class="label">List of possible values</label>
              <p class="is-size-6">If "Allow user values to be suggested to other users" is selected these values will be added additionally. One value per line.</p>
              <div class="control">
                <div class="control">
                  <textarea class="textarea" name="admin-custom-fields-form-possible-values"></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="admin-custom-fields-form-tags_range-fields admin-custom-fields-form-specific-fields">
            <hr>

            <div class="field">
              <label class="label">Tag name</label>
              <div class="control">
                <div class="control">
                  <input class="input" type="text" name="admin-custom-fields-form-tag-name">
                </div>
              </div>
            </div>
          </div>

          <div class="admin-custom-fields-form-short_text-fields admin-custom-fields-form-specific-fields">
            <hr>

            <div class="field">
              <label class="label">Is link</label>
              <div class="control">
                <div class="control">
                  <input class="checkbox" type="checkbox" name="admin-custom-fields-form-is-link">
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
  import Icon from '@/components/Shared/Icon.vue'
  import Booler from '@/components/Shared/Booler.vue'
  import minusIcon from '@/assets/images/minus.svg'
  import clipboardIcon from '@/assets/images/clipboard.svg'
  import addIcon from '@/assets/images/add.svg'
  import editIcon from '@/assets/images/edit.svg'
  import Swal from 'sweetalert2'
  import AdminTable from '@/components/Admin/AdminTable.vue'
  import { formattedTimestamp } from '@/lib/time_stuff'
  import ActionButton from '@/components/Shared/ActionButton.vue'
  import VueMultiselect from 'vue-multiselect'

  export default {
    name: 'AdminCustomFields',
    components: {
      Icon,
      AdminTable,
      Booler,
      ActionButton,
      VueMultiselect,
    },
    data() {
      return {
        minusIcon,
        addIcon,
        clipboardIcon,
        editIcon,
        customFields: [],
        apiUrl: import.meta.env.VITE_API_URL,
        formattedTimestamp: formattedTimestamp,
        editFormSelector: '.swal2-html-container .admin-custom-fields-form',
      }
    },
    created() {
      this.initialDataLoad()
    },
    methods: {
      initialDataLoad() {
        this.loadCustomFields()
      },
      async loadCustomFields() {
        this.mitt.emit('spinnerStart')

        const customFields = await this.$store.dispatch('app/fetchCustomFields')

        this.customFields = customFields

        this.mitt.emit('spinnerStop')
      },
      toggleAll() {
        const newStatus = this.$refs.toggleAllCheckbox.checked

        this.customFields
          .map((customField) => {
            customField.selected = newStatus

            return customField
          })
      },
      openEditFieldModal(customField) {
        this.currentFormCustomField = customField

        Swal.fire({
          icon: null,
          showCancelButton: true,
          confirmButtonText: 'Save',
          confirmButtonColor: this.colors.main,
          html: this.$refs.saveCustomFieldTemplate.innerHTML,
          didOpen: () => {
            this.setupEditFieldFormAfterOpen(customField)
          },
        }).then(async (result) => {
          if (result.isConfirmed) {
            this.submitEditFieldForm()
          }
        })
      },
      setupEditFieldFormAfterOpen(customField) {
        const formElement = document.querySelector(this.editFormSelector)
        const customFieldSpecificProperties = formElement.querySelectorAll(`.admin-custom-fields-form-${customField.input_type}-fields`)

        if (customFieldSpecificProperties) {
          [...customFieldSpecificProperties].map((elem) => {
            elem.style.display = 'block'
          })
        }

        const updateFormField = (name, property, isCheckbox = true) => {
          const field = formElement.querySelector(`[name=${name}]`)
          if (field) {
            field[isCheckbox ? 'checked' : 'value'] = property
          }
        }

        updateFormField('admin-custom-fields-form-title', customField.title, false)
        updateFormField('admin-custom-fields-form-is-link', customField.metadata?.isLink)
        updateFormField('admin-custom-fields-form-allow-multiple-values', customField.metadata?.allowMultiple)
        updateFormField('admin-custom-fields-form-allow-add-new-values', customField.metadata?.allowNewValues)
        updateFormField('admin-custom-fields-form-share-users-values', customField.metadata?.shareUserValues)
        updateFormField('admin-custom-fields-form-possible-values', customField.metadata?.possibleValues?.join('\n') || '', false)
        updateFormField('admin-custom-fields-form-tag-name', customField.metadata?.tagName || '', false)
      },
      async submitEditFieldForm() {
        this.mitt.emit('spinnerStart')

        const formElement = document.querySelector(this.editFormSelector)
        
        const getFormFieldValue = (name, isCheckbox = true) => {
          const field = formElement.querySelector(`[name=${name}]`)
          return isCheckbox ? field.checked : field.value
        }

        const response = await this.$store.dispatch('app/saveCustomField', {
          id: this.currentFormCustomField.id,
          title: getFormFieldValue('admin-custom-fields-form-title', false),
          metadata: {
            isLink: getFormFieldValue('admin-custom-fields-form-is-link'),
            allowMultiple: getFormFieldValue('admin-custom-fields-form-allow-multiple-values'),
            allowNewValues: getFormFieldValue('admin-custom-fields-form-allow-add-new-values'),
            shareUserValues: getFormFieldValue('admin-custom-fields-form-share-users-values'),
            possibleValues: getFormFieldValue('admin-custom-fields-form-possible-values', false),
            tagName: getFormFieldValue('admin-custom-fields-form-tag-name', false),
          },
        })

        if (response.ok) {
          this.awn.success('Custom field has been updated.')
          this.loadCustomFields()
        } else {
          this.awn.warning('Something went wrong, try again.')
        }

        this.mitt.emit('spinnerStop')
      }
    },
  }
</script>

<style lang="scss">
  .admin-custom-fields-form {
    input[type=checkbox] {
      transform: scale(2);
      cursor: pointer;
      margin-left: 1rem;
      margin-bottom: 1rem
    }
  }

  .admin-custom-fields-form-specific-fields {
    display: none;
  }
</style>
