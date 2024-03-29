<template>
  <div class="admin-data-editor">
    <h3 class="is-size-3 has-text-weight-bold mb-4">Data Editor</h3>

    <div class="admin-data-editor-search mb-4">
      <input
        type="text"
        v-model="$store.state.app.dataEditorSearchQuery"
        placeholder="Search data to edit"
        class="input"
        @keyup="reloadView()"
      >
      <span><img :src="searchIcon"></span>
    </div>

    <form class="form">
      <admin-table :tableClasses="['admin-data-editor-table']">
        <thead>
          <tr class="no-select">
            <th data-sort-default aria-sort="descending">Value</th>
            <th data-sort-default aria-sort="descending">Value JSON</th>
            <th>Model ID</th>
            <th>Model</th>
            <th data-sort-method="none" class="no-sort">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="fieldsDataItem in fieldsData" :key="fieldsDataItem.id" class="no-break">
            <td class="admin-data-editor-table-value">{{ fieldsDataItem.value }}</td>
            <td class="admin-data-editor-table-value">{{ fieldsDataItem.value_json }}</td>
            <td class="admin-data-editor-table-model-id">{{ fieldsDataItem.model_id }}</td>
            <td class="admin-data-editor-table-model-id">{{ fieldsDataItem.model_name }}</td>
            <td class="admin-table-actions admin-data-editor-table-model-actions">
              <a title="Edit custom field" @click.prevent="openEditModal(fieldsDataItem)">
                <Icon :src="editIcon" />
              </a>
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

  export default {
    name: 'AdminDataEditor',
    components: {
      Icon,
      AdminTable,
      Booler,
      VueMultiselect,
      Modal,
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
    },
    methods: {
      async loadData() {
        this.mitt.emit('spinnerStart')
        const searchTermEntering = this.$store.state.app.dataEditorSearchQuery

        let fieldsData = null
        try {
          fieldsData = await this.$store.dispatch('app/fetchDataEditorData')
        } catch (error) {
          this.mitt.emit('spinnerStop')
          return
        }

        if (this.$store.state.app.dataEditorSearchQuery !== searchTermEntering) {
          this.mitt.emit('spinnerStop')
          return
        }

        this.fieldsData = fieldsData

        this.mitt.emit('spinnerStop')
      },
      reloadView() {
        if (this.$store.state.app.dataEditorSearchQuery.length > 1) {
          this.loadData()
        } else {
          this.fieldsData = []
        }
      },
      abortFetchDataEditorRequest() {
        if (this.$store.state.app.dataEditorFetchController) {
          this.$store.state.app.dataEditorFetchController.abort()
        }
      },
      openEditModal(dataItem) {
        this.fieldDataFormModalCurrent = dataItem
        this.fieldDataFormModalVisible = true
      },
      async submitEditDataForm() {
        this.mitt.emit('spinnerStart')

        const response = await this.$store.dispatch('app/saveDataEditorItem', this.fieldDataFormModalCurrent)

        this.mitt.emit('spinnerStop')

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
      '$store.state.app.dataEditorSearchQuery': function() {
        this.abortFetchDataEditorRequest()
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
