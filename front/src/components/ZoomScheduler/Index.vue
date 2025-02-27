<template>
  <div class="zoom-scheduler">
    <div class="commuse-blocks">
      <h3 class="is-size-3 has-text-weight-bold mb-4">Zoom scheduler</h3>

      <SkeletonPatternLoader :loading="loading">
        <template v-slot:content>
          <form @submit.prevent="createMeeting">
            <div class="panel">
              <p class="panel-heading">
                Create new meeting
              </p>
              <div class="panel-block">
                <div class="field">
                  <label class="label" for="zoom-scheduler-host-title">Title</label>
                  <div class="control">
                    <input class="input" type="text" id="zoom-scheduler-host-title" v-model="meeting.title" required>
                  </div>
                </div>

                <div class="field">
                  <label class="label" for="zoom-scheduler-host-email">Host email address</label>
                  <div class="control">
                    <input class="input" type="email" id="zoom-scheduler-host-email" v-model="currentUserEmail" required>
                  </div>
                </div>

                <div class="field">
                  <label class="label" for="zoom-scheduler-date-start">Start time</label>
                  <div class="control">
                    <date-picker :input-attr="{ required: true, id: 'zoom-scheduler-date-start' }" v-model:value="meeting.dateStart" type="datetime" value-type="format" input-class="input" :clearable="false" :showSecond="false" :minuteOptions="minuteOptions"></date-picker>
                  </div>
                </div>

                <div class="field">
                  <label class="label" for="zoom-scheduler-date-end">End time</label>
                  <div class="control">
                    <date-picker :input-attr="{ required: true, id: 'zoom-scheduler-date-end' }" v-model:value="meeting.dateEnd" type="datetime" value-type="format" input-class="input" :clearable="false" :showSecond="false" :minuteOptions="minuteOptions"></date-picker>
                  </div>
                </div>

                <div class="field">
                  <label class="label" for="zoom-scheduler-date-timezone">Timezone</label>
                  <div class="control">
                    <div class="select">
                      <select id="zoom-scheduler-date-timezone" v-model="meeting.timezone" required>
                        <option v-for="timezone in timezones" :key="timezone" :value="timezone">{{ timezone }}</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="field is-grouped">
                  <div class="control">
                    <ActionButton buttonText="Create" :button="true" :working="savingMeeting"></ActionButton>
                  </div>
                </div>
              </div>
            </div>
          </form>

          <div class="panel mt-4">
            <p class="panel-heading">
              Existing meetings
            </p>
            <div class="panel-block">
              <cu-table :tableClasses="['zoom-scheduler-meetings-table']">
                <thead>
                  <tr class="no-select">
                    <th>Topic</th>
                    <th>Link</th>
                    <th data-sort-method="none" class="no-sort commuse-table-row-cell-narrow">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="meeting in $store.state.zoomScheduler.meetings" :key="meeting.id">
                    <td>{{ meeting.topic }}</td>
                    <td>
                      <copy-paster :text="meeting.join_url"></copy-paster>
                    </td>
                    <td>
                      <VDropdown>
                        <div>
                          <a class="button">
                            <Icon :src="dropdownIcon" />
                          </a>
                        </div>

                        <template #popper>
                          <a class="dropdown-item" @click.prevent="deleteMeetingConfirm(meeting)">
                            <Icon :src="minusIcon" />
                            Delete meeting
                          </a>
                        </template>
                      </VDropdown>
                    </td>
                  </tr>
                  <tr v-if="$store.state.zoomScheduler.meetings.length === 0">
                    <td colspan="2">No existing meetings found.</td>
                  </tr>
                </tbody>
              </cu-table>
            </div>
          </div>
        </template>

        <template v-slot:skeleton>
          <div class="ssc-card ssc-wrapper mb-4">
            <div class="mb-5 ssc-head-line"></div>
            <div class="mb ssc-line w-20 mb"></div>
            <div class="mb ssc-head-line w-40 mb"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="mb ssc-head-line"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="mb ssc-head-line"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="mb ssc-head-line"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="mb ssc-head-line"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="ssc-square"></div>
          </div>

          <div class="ssc-card ssc-wrapper mb-4" v-for="n in 2" :key="n">
            <div class="mb-5 ssc-head-line"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="mb ssc-head-line"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="mb ssc-head-line"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="mb ssc-head-line"></div>
            <div class="mb ssc-line w-20"></div>
            <div class="ssc-square"></div>
          </div>
        </template>
      </SkeletonPatternLoader>
    </div>
  </div>

  <Modal
    v-model="deleteMeetingModalStatus"
    title="Delete meeting"
    @confirm="deleteMeeting()"
    @cancel="deleteMeetingModalStatus = false"
    :working="defaultMeetingModalWorking"
  >
    Are you sure you delete the <span class="has-text-weight-bold">{{ deleteMeetingCurrent.topic }}</span> meeting?
  </Modal>
</template>

<script>
  import minusIcon from '@/assets/images/minus.svg'
  import saveIcon from '@/assets/images/save.svg'
  import dropdownIcon from '@/assets/images/dropdown.svg'

  import CustomField from '@/components/CustomFields/CustomField.vue'
  import ActionButton from '@/components/Shared/ActionButton.vue'
  import SkeletonPatternLoader from '@/components/Shared/SkeletonPatternLoader.vue'
  import CuTable from '@/components/Shared/Table.vue'
  import Modal from '@/components/Shared/Modal.vue'
  import Icon from '@/components/Shared/Icon.vue'
  import CopyPaster from '@/components/Shared/CopyPaster.vue'

  const defaultMeeting = {
    title: '',
    email: '',
    dateStart: '',
    dateEnd: '',
    timezone: 'America/New_York',
  }

  export default {
    name: 'ZoomScheduler',
    data() {
      return {
        apiUrl: import.meta.env.VITE_API_URL,
        saveIcon,
        loading: true,
        savingMeeting: false,
        meeting: JSON.parse(JSON.stringify(defaultMeeting)),
        minuteOptions: [0, 15, 30, 45],
        timezones: Intl.supportedValuesOf('timeZone'),

        deleteMeetingModalStatus: false,
        deleteMeetingCurrent: null,
        defaultMeetingModalWorking: false,

        minusIcon,
        dropdownIcon,
      }
    },
    components: {
      CustomField,
      ActionButton,
      SkeletonPatternLoader,
      CuTable,
      Modal,
      Icon,
      CopyPaster,
    },
    created() {
      this.initialDataLoad()
    },
    computed: {
      currentUserEmail: {
        get() {
          return this.meeting.email || this.$store.state.user?.currentUser?.email
        },
        set(value) {
          this.meeting.email = value
        },
      },
    },
    methods: {
      async initialDataLoad() {
        await this.loadListOfMeetings()
      },
      async loadListOfMeetings() {
        let response = await this.$store.dispatch('zoomScheduler/fetchMeetings')

        this.$store.dispatch('zoomScheduler/setMeetings', response.meetings)

        this.loading = false
      },
      async createMeeting() {
        this.savingMeeting = true
        this.meeting.email = this.currentUserEmail

        try {
          await this.$store.dispatch('zoomScheduler/createMeeting', this.meeting)
          await this.loadListOfMeetings()
          this.awn.success('Meeting created successfully.')
          this.meeting = JSON.parse(JSON.stringify(defaultMeeting))
        } catch (error) {
          this.awn.warning(error.messages.error)
        } finally {
          this.savingMeeting = false
        }
      },
      deleteMeetingConfirm(meeting) {
        this.deleteMeetingCurrent = meeting
        this.deleteMeetingModalStatus = true
      },
      async deleteMeeting() {
        this.defaultMeetingModalWorking = true

        try {
          await this.$store.dispatch('zoomScheduler/deleteMeeting', this.deleteMeetingCurrent.id)
          await this.loadListOfMeetings()
          this.awn.success('Meeting deleted successfully.')
          this.deleteMeetingModalStatus = false
          this.deleteMeetingCurrent = null
        } catch (error) {
          this.awn.warning(error.messages.error)
        } finally {
          this.defaultMeetingModalWorking = false
        }
      },
    },
  }
</script>

<style lang="scss">
  $cl: '.zoom-scheduler';

  #{$cl} {
    &-meetings-table {
      width: 100%;
    }
  }
</style>
