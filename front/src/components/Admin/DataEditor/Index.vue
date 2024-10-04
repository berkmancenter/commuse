<template>
  <div class="admin-data-editor">
    <h3 class="is-size-3 has-text-weight-bold mb-4">Data editor</h3>

    <div class="mb-2">
      <SearchInput v-model="$store.state.dataEditor.dataEditorSearchQuery" />
    </div>

    <form class="form">
      <admin-table :tableClasses="['admin-data-editor-table']">
        <thead>
          <tr class="no-select">
            <th>Value</th>
            <th>Value JSON</th>
            <th>Field name</th>
            <th data-sort-default aria-sort="descending">Model ID</th>
            <th>Model</th>
            <th data-sort-method="none" class="no-sort">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="fieldsDataItem in fieldsData" :key="fieldsDataItem.id" class="no-break">
            <td class="admin-data-editor-table-value">{{ fieldsDataItem.value }}</td>
            <td class="admin-data-editor-table-value">{{ fieldsDataItem.value_json }}</td>
            <td>{{ fieldsDataItem.field_title }}</td>
            <td class="admin-data-editor-table-model-id">{{ fieldsDataItem.model_id }}</td>
            <td>{{ fieldsDataItem.model_name }}</td>
            <td>
              <div class="admin-table-actions">
                <a title="Edit custom field" @click.prevent="openEditModal(fieldsDataItem)">
                  <Icon :src="editIcon" />
                </a>
              </div>
            </td>
          </tr>
          <tr v-if="fieldsData.length === 0">
            <td colspan="4">Search to see data to edit.</td>
          </tr>
        </tbody>
      </admin-table>
    </form>
  </div>

  <Modal
    v-model="fieldDataFormModalVisible"
    title="Edit data"
    :focusOnConfirm="false"
    confirmButtonTitle="Save"
    @confirm="submitEditDataForm()"
    @cancel="fieldDataFormModalVisible = false"
  >
    <form class="form admin-data-editor-form">
      <div class="control mb-4">
        <label class="label">Value</label>
        <textarea class="textarea" v-model="fieldDataFormModalCurrent.value"></textarea>
      </div>

      <div class="control">
        <label class="label">Value JSON</label>
        <textarea class="textarea" v-model="fieldDataFormModalCurrent.value_json"></textarea>
      </div>
    </form>
  </Modal>
</template>

<script>
  import Icon from '@/components/Shared/Icon.vue'
  import Booler from '@/components/Shared/Booler.vue'
  import editIcon from '@/assets/images/edit.svg'
  import searchIcon from '@/assets/images/search.svg'
  import AdminTable from '@/components/Admin/AdminTable.vue'
  import VueMultiselect from 'vue-multiselect'
  import Modal from '@/components/Shared/Modal.vue'
  import SearchInput from '@/components/Shared/SearchInput.vue'

  export default {
    name: 'AdminDataEditor',
    components: {
      Icon,
      AdminTable,
      Booler,
      VueMultiselect,
      Modal,
      SearchInput,
    },
    data() {
      return {
        searchIcon,
        editIcon,
        fieldsData: [],
        fieldDataFormModalVisible: false,
        fieldDataFormModalCurrent: null,
      }
    },
    created() {
      this.reloadView()
    },
    methods: {
      async loadData() {
        const searchTermEntering = this.$store.state.dataEditor.dataEditorSearchQuery

        let fieldsData = null
        try {
          fieldsData = await this.$store.dispatch('dataEditor/fetchDataEditorData')
        } catch (error) {
          return
        }

        if (this.$store.state.dataEditor.dataEditorSearchQuery !== searchTermEntering) {
          return
        }

        this.fieldsData = fieldsData
      },
      reloadView() {
        if (this.$store.state.dataEditor.dataEditorSearchQuery.length > 1) {
          this.loadData()
        } else {
          this.fieldsData = []
        }
      },
      abortFetchDataEditorRequest() {
        if (this.$store.state.dataEditor.dataEditorFetchController) {
          this.$store.state.dataEditor.dataEditorFetchController.abort()
        }
      },
      openEditModal(dataItem) {
        this.fieldDataFormModalCurrent = dataItem
        this.fieldDataFormModalVisible = true
      },
      async submitEditDataForm() {
        const response = await this.$store.dispatch('dataEditor/saveDataEditorItem', this.fieldDataFormModalCurrent)

        this.reloadView()

        if (response.ok) {
          this.awn.success('Data item has been saved.')
        } else {
          this.awn.warning('Something went wrong, try again.')
        }

        this.fieldDataFormModalVisible = false
        this.fieldDataFormModalCurrent = null
      },
    },
    watch: {
      '$store.state.dataEditor.dataEditorSearchQuery': function() {
        this.abortFetchDataEditorRequest()
        this.reloadView()
      },
    },
  }
</script>

<style lang="scss">
  .admin-data-editor {
    .admin-data-editor-search {
      position: relative;
      max-width: 300px;

      span {
        display: block;
        width: 1.5rem;
        height: 1.5rem;
        position: absolute;
        top: 0;
        right: 0.5rem;
        height: 100%;
        display: flex;
        pointer-events: none;
      }
    }

    .admin-data-editor-table-value {
      max-width: 400px;
      white-space: normal;
    }

    .admin-data-editor-table-id,
    .admin-data-editor-table-actions {
      max-width: 100px;
    }
  }
</style>
