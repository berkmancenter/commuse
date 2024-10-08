<template>
  <div class="admin-custom-fields">
    <h3 class="is-size-3 has-text-weight-bold mb-4">Custom fields</h3>

    <form class="form">
      <admin-table :tableClasses="['admin-custom-fields-table']">
        <thead>
          <tr class="no-select">
            <th data-sort-default aria-sort="descending">Title</th>
            <th>Model</th>
            <th>Type</th>
            <th>Created</th>
            <th data-sort-method="none" class="no-sort">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="customField in customFields" :key="customField.id" class="no-break">
            <td>{{ customField.title }}</td>
            <td>{{ customField.model_name }}</td>
            <td>{{ customField.input_type }}</td>
            <td>{{ formattedTimestamp(customField.created_at) }}</td>
            <td>
              <div class="admin-table-actions">
                <a title="Edit custom field" @click.prevent="openEditFieldModal(customField)">
                  <Icon :src="editIcon" />
                </a>
              </div>
            </td>
          </tr>
          <tr v-if="customFields.length === 0">
            <td colspan="4">No custom fields found.</td>
          </tr>
        </tbody>
      </admin-table>
    </form>
  </div>

  <Modal
    v-model="fieldModalVisible"
    title="Field data"
    :focusOnConfirm="false"
    @confirm="submitEditFieldForm()"
    @cancel="fieldModalVisible = false"
  >
    <form class="form admin-custom-fields-form">
      <div class="field">
        <label class="label">Title</label>
        <div class="control">
          <input class="input" type="text" v-model="fieldModalCurrent.title">
        </div>
      </div>

      <hr>

      <div class="field">
        <label class="label">Hide title</label>
        <div class="control">
          <input class="checkbox" type="checkbox" v-model="fieldModalCurrent.metadata.hideTitle">
        </div>
      </div>

      <div v-if="['tags'].includes(fieldModalCurrent.input_type)">
        <hr>

        <div class="field">
          <label class="label">Allow to set multiple values</label>
          <div class="control">
            <input class="checkbox" type="checkbox" v-model="fieldModalCurrent.metadata.allowMultiple">
          </div>
        </div>

        <hr>

        <div class="field">
          <label class="label">Allow user values to be suggested to other users</label>
          <div class="control">
            <input class="checkbox" type="checkbox" v-model="fieldModalCurrent.metadata.shareUserValues">
          </div>
        </div>
      </div>

      <div v-if="['tags', 'tags_range'].includes(fieldModalCurrent.input_type)">
        <hr>

        <div class="field">
          <label class="label">List of possible values</label>
          <p class="is-size-6">If "Allow user values to be suggested to other users" is selected these values will be added additionally. One value per line.</p>
          <div class="control">
            <textarea class="textarea" v-model="fieldModalCurrent.metadata.possibleValues"></textarea>
          </div>
        </div>

        <hr>

        <div class="field">
          <label class="label">Allow add new values by users</label>
          <div class="control">
            <input class="checkbox" type="checkbox" v-model="fieldModalCurrent.metadata.allowNewValues">
          </div>
        </div>
      </div>

      <div v-if="['tags_range'].includes(fieldModalCurrent.input_type)">
        <hr>

        <div class="field">
          <label class="label">Tag name</label>
          <div class="control">
            <input class="input" type="text" v-model="fieldModalCurrent.metadata.tagName">
          </div>
        </div>

        <hr>

        <div class="field">
          <label class="label">Allow to create multiple items</label>
          <div class="control">
            <input class="checkbox" type="checkbox" v-model="fieldModalCurrent.metadata.multipleItems">
          </div>
        </div>

        <hr>

        <div class="field">
          <label class="label">Show "Auto-extend" checkbox</label>
          <p class="is-size-6">If selected, the field will show a checkbox called "Auto-extend" that can be used to auto-extend the field period.</p>
          <div class="control mt-2">
            <input class="checkbox" type="checkbox" v-model="fieldModalCurrent.metadata.autoExtend">
          </div>
        </div>

        <hr>

        <div class="field">
          <label class="label">Show "Auto-extend once" checkbox</label>
          <p class="is-size-6">If selected, the field will show a checkbox called "Auto-extend once" that can be used to auto-extend the field period only once.</p>
          <div class="control mt-2">
            <input class="checkbox" type="checkbox" v-model="fieldModalCurrent.metadata.autoExtendOnce">
          </div>
        </div>

        <hr>

        <div class="field">
          <label class="label">Values that, when selected, will auto-select "Auto-extend" field</label>
          <p class="is-size-6 mb-2">One value per line.</p>
          <div class="control">
            <textarea class="textarea" v-model="fieldModalCurrent.metadata.autoExtendValuesAutoSelect"></textarea>
          </div>
        </div>
      </div>

      <div v-if="['short_text'].includes(fieldModalCurrent.input_type)">
        <hr>

        <div class="field">
          <label class="label">Is link</label>
          <div class="control">
            <input class="checkbox" type="checkbox" v-model="fieldModalCurrent.metadata.isLink">
          </div>
        </div>

        <hr>

        <div class="field">
          <label class="label">Is import profile image link</label>
          <div class="control">
            <input class="checkbox" type="checkbox" v-model="fieldModalCurrent.metadata.isImportProfileImageLink">
          </div>
        </div>
      </div>

      <div v-if="['multi'].includes(fieldModalCurrent.input_type)">
        <hr>

        <div class="field">
          <label class="label">Child fields</label>
          <div class="control">
            <VueMultiselect
              v-model="fieldModalCurrent.metadata.childFields"
              :multiple="true"
              :taggable="false"
              :options="customFields ?? []"
              track-by="id"
              label="title"
            >
            </VueMultiselect>
          </div>
        </div>

        <hr>

        <div class="field">
          <label class="label">Is import profile image link</label>
          <div class="control">
            <input class="checkbox" type="checkbox" v-model="fieldModalCurrent.metadata.isImportProfileImageLink">
          </div>
        </div>
      </div>

      <hr>

      <div class="field">
        <label class="label">Is people filter</label>
        <div class="control">
          <input class="checkbox" type="checkbox" v-model="fieldModalCurrent.metadata.isPeopleFilter">
        </div>
      </div>

      <hr>

      <div class="field">
        <label class="label">Editable only by admins</label>
        <div class="control">
          <input class="checkbox" type="checkbox" v-model="fieldModalCurrent.metadata.editableOnlyByAdmins">
        </div>
      </div>
    </form>
  </Modal>
</template>

<script>
  import Icon from '@/components/Shared/Icon.vue'
  import Booler from '@/components/Shared/Booler.vue'
  import minusIcon from '@/assets/images/minus.svg'
  import clipboardIcon from '@/assets/images/clipboard.svg'
  import addIcon from '@/assets/images/add.svg'
  import editIcon from '@/assets/images/edit.svg'
  import AdminTable from '@/components/Admin/AdminTable.vue'
  import { formattedTimestamp } from '@/lib/time_stuff'
  import VueMultiselect from 'vue-multiselect'
  import Modal from '@/components/Shared/Modal.vue'

  export default {
    name: 'AdminCustomFields',
    components: {
      Icon,
      AdminTable,
      Booler,
      VueMultiselect,
      Modal,
    },
    data() {
      return {
        minusIcon,
        addIcon,
        clipboardIcon,
        editIcon,
        customFields: [],
        formattedTimestamp: formattedTimestamp,
        fieldModalVisible: false,
        fieldModalCurrent: null,
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
        const customFields = await this.$store.dispatch('admin/fetchCustomFields')

        this.customFields = customFields
      },
      toggleAll() {
        const newStatus = this.$refs.toggleAllCheckbox.checked

        this.customFields
          .map((customField) => {
            customField.selected = newStatus

            return customField
          })
      },
      async openEditFieldModal(customField) {
        await this.loadCustomFields()
        this.fieldModalCurrent = this.customFields.find((field) => field.id === customField.id)

        this.fieldModalVisible = true
      },
      async submitEditFieldForm() {
        const response = await this.$store.dispatch('admin/saveCustomField', this.fieldModalCurrent)

        if (response.ok) {
          this.awn.success('Custom field has been updated.')
          this.loadCustomFields()
        } else {
          this.awn.warning('Something went wrong, try again.')
        }

        this.fieldModalVisible = false
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
</style>
