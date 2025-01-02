<template>
  <div class="buzz-section commuse-blocks">
    <h3 class="is-size-3 has-text-weight-bold mb-4">
      <Icon :src="buzzIcon"></Icon>
      Buzz
    </h3>

    <div class="mb-4">
      <SearchInput v-model="searchTerm" />
    </div>

    <div class="buzz-section-editor" :class="{ 'buzz-section-editor-editing': editing }">
      <div class="panel mb-4">
        <div class="panel-block">
          <ckeditor :editor="editor" v-model="$store.state.buzz.editorItem.content" :config="editorConfig"></ckeditor>

          <div class="buzz-section-editor-buttons mt-2">
            <ActionButton class="mt-2 mr-2" buttonText="Post" @click="postMessage()" :icon="sendIcon"></ActionButton>
            <ActionButton class="mt-2" buttonText="Clear" @click="clearEditor()" :icon="clearPostIcon"></ActionButton>
          </div>
        </div>
      </div>
    </div>

    <SkeletonPatternLoader :loading="loading">
      <template v-slot:content>
        <div class="buzz-section-item box" v-for="item in $store.state.buzz.items" :key="item.id">
          <article class="media">
            <div class="media-left buzz-section-item-media">
              <figure class="image is-64x64">
                <router-link :to="'/people/' + item.person_id">
                  <img :data-src="`${apiUrl}/api/files/get/${item.image_url}`" class="lazy" />
                </router-link>
              </figure>
            </div>
            <div class="media-content">
              <div class="content">
                <div class="buzz-section-item-meta mb-2">
                  <router-link class="buzz-section-item-meta-name" :to="'/people/' + item.person_id">
                    <strong class="mr-2">{{ item.name }}</strong>
                  </router-link>
                  <small :title="localTime(item.created_at)">{{ agoTime(item.created_at) }}</small>
                  <div class="buzz-section-item-meta-tags mt-2 mb-4" v-if="item.tags.length > 0">
                    <span class="tag mr-1" v-for="tag in item.tags">{{ tag }}</span>
                  </div>
                </div>

                <div class="buzz-section-item-content" v-html="item.content"></div>

                <VDropdown class="buzz-section-item-dots" v-if="$store.state.user.currentUser.id === item.user_id">
                  <div>
                    <Icon :src="dotsIcon"></Icon>
                  </div>

                  <template #popper>
                    <a class="dropdown-item" @click="editItem(item)" v-close-popper>
                      Edit
                    </a>
                    <a class="dropdown-item" @click="openDeleteModal(item.id)" v-close-popper>
                      Delete
                    </a>
                  </template>
                </VDropdown>
              </div>
            </div>
          </article>
        </div>
      </template>

      <template v-slot:skeleton>
        <div class="w-100">
          <div class="ssc-wrapper mb-4" v-for="n in 10" :key="n">
            <div class="ssc-head-line"></div>
            <br>
            <div class="ssc-square"></div>
          </div>
        </div>
      </template>
    </SkeletonPatternLoader>
  </div>

  <Modal
    v-model="deleteMessageModalStatus"
    title="Delete message"
    @confirm="deleteMessage(id)"
    @cancel="deleteMessageModalStatus = false"
  >
    Are you sure you delete the message?
  </Modal>
</template>

<script>
  import sendIcon from '@/assets/images/send.svg'
  import clearPostIcon from '@/assets/images/clear_post.svg'
  import buzzIcon from '@/assets/images/buzz.svg'
  import dotsIcon from '@/assets/images/dots.svg'

  import LazyLoad from 'vanilla-lazyload'
  import SkeletonPatternLoader from '@/components/Shared/SkeletonPatternLoader.vue'
  import { ClassicEditor, Bold, Essentials, Italic, Paragraph, Undo, Link } from 'ckeditor5'
  import ActionButton from '@/components/Shared/ActionButton.vue'
  import Icon from '@/components/Shared/Icon.vue'
  import profileFallbackImage from '@/assets/images/profile_fallback.svg'
  import moment from 'moment'
  import Modal from '@/components/Shared/Modal.vue'
  import SearchInput from '@/components/Shared/SearchInput.vue'

  export default {
    name: 'BuzzIndex',
    components: {
      SkeletonPatternLoader,
      ActionButton,
      Icon,
      Modal,
      SearchInput,
    },
    data() {
      return {
        sendIcon,
        clearPostIcon,
        buzzIcon,
        dotsIcon,

        apiUrl: import.meta.env.VITE_API_URL,
        lazyLoadInstance: null,
        profileFallbackImage,
        relativeTimeUpdater: null,
        listUpdater: null,
        paginateTotalItems: 0,
        loading: true,
        editing: false,
        searchTerm: '',

        editor: ClassicEditor,
        editorConfig: {
          plugins: [ Bold, Essentials, Italic, Paragraph, Undo, Link ],
          toolbar: [ 'undo', 'redo', '|', 'bold', 'italic', '|', 'link' ],
        },

        deleteMessageModalStatus: false,
        deleteMessageModalCurrentId: null,
      }
    },
    computed: {
      page: {
        get () {
          return parseInt(this.$route.query.page) || 1
        },
        set () {}
      },
    },
    created() {
      this.reloadBuzz(true)
      this.initLazyLoad()
      this.startRelativeTimeUpdater()
      this.startListUpdater()
    },
    unmounted() {
      this.lazyLoadInstance.destroy()
      clearInterval(this.relativeTimeUpdater)
      clearInterval(this.listUpdater)
    },
    methods: {
      async reloadBuzz(replace = false) {
        const searchTermEntering = this.searchTerm

        if (replace) {
          this.loading = true
          this.$store.dispatch('buzz/clearItems')
        }

        const latestTimestamp = this.$store.state.buzz.items.length
                                ? this.$store.state.buzz.items[0].created_at
                                : null

        const response = await this.$store.dispatch('buzz/fetchItems', {
          since: latestTimestamp,
          query: this.searchTerm,
        })

        if (searchTermEntering !== this.searchTerm) {
          return
        }

        if (response.length > 0) {
          this.$store.commit('buzz/updateItems', response)
        }

        this.loading = false

        this.$nextTick(() => {
          this.lazyLoadInstance.update()
        })
      },
      initLazyLoad() {
        this.lazyLoadInstance = new LazyLoad({
          callback_error: (img) => {
            img.setAttribute('src', this.profileFallbackImage)
          },
          unobserve_entered: true,
          unobserve_completed: true,
        })
        this.lazyLoadInstance.update()
      },
      clearEditor() {
        this.$store.commit('buzz/clearCurrentItem')
      },
      async postMessage() {
        const content = this.$store.state.buzz.editorItem.content

        if (!content.trim()) {
          this.awn.warning('Message cannot be empty.')

          return
        }

        try {
          await this.$store.dispatch('buzz/postMessage', this.$store.state.buzz.editorItem)
        } catch (error) {
          this.awn.warning('Something went wrong, try again.')
          return
        }

        this.clearEditor()
        this.reloadBuzz()

        this.$nextTick(() => {
          this.lazyLoadInstance.update()
        })
      },
      localTime(timestamp) {
        return moment.utc(timestamp).local().format('YYYY-MM-DD HH:mm')
      },
      agoTime(timestamp) {
        return moment.utc(timestamp).fromNow()
      },
      startRelativeTimeUpdater() {
        this.relativeTimeUpdater = setInterval(() => {
          this.$forceUpdate()
        }, 60000)
      },
      startListUpdater() {
        this.listUpdater = setInterval(() => {
          this.reloadBuzz()
        }, 5000)
      },
      async editItem(item) {
        this.editing = false
        await this.$nextTick(() => {
          this.editing = true

          setTimeout(() => {
            this.editing = false
          }, 1500)
        })

        this.$store.commit('buzz/setCurrentItem', item)
        window.scrollTo(0, 0)
      },
      openDeleteModal(id) {
        this.deleteMessageModalCurrentId = id
        this.deleteMessageModalStatus = true
      },
      async deleteMessage() {
        try {
          await this.$store.dispatch('buzz/deleteMessage', this.deleteMessageModalCurrentId)
        } catch (error) {
          this.awn.warning('Something went wrong, try again.')
          return
        }

        this.$store.commit('buzz/removeItem', this.deleteMessageModalCurrentId)

        if (this.deleteMessageModalCurrentId === this.$store.state.buzz.editorItem.id) {
          this.clearEditor()
        }

        this.deleteMessageModalStatus = false
        this.deleteMessageModalCurrentId = null
      },
      abortFetchItemsRequest() {
        if (this.$store.state.buzz.fetchItemsController) {
          this.$store.state.buzz.fetchItemsController.abort()
        }
      },
    },
    watch: {
      'searchTerm': function() {
        this.abortFetchItemsRequest()
        this.reloadBuzz(true)
        this.$nextTick(() => {
          this.lazyLoadInstance.update()
        })
      },
    },
  }
</script>

<style lang="scss">
  .buzz-section {
    &-editor {
      .panel {
        width: 100%;
      }

      .ck-editor {
        width: 100%;

        .ck-content {
          p {
            margin: 0.5rem 0;
          }
        }
      }

      .ck-editor__editable {
        min-height: 100px;
      }

      &-editing .ck-editor {
        animation: pulse-border 1.5s infinite;
      }

      @keyframes pulse-border {
        0% {
          box-shadow: 0 0 0 0 var(--blue-stronger-color);
        }
        100% {
          box-shadow: 0 0 10px 10px transparent;
        }
      }
    }

    .panel-block {
      padding-top: 1rem;
    }

    &-item {
      position: relative;

      &-media {
        overflow: hidden;
        border-radius: 50%;

        &:hover {
          transform: scale(1.05);
        }
      }

      &-meta {
        &-name {
          color: #000;

          &:hover {
            text-decoration: underline;
          }
        }

        &-tags {
          .tag {
            background-color: var(--main-color-lighter);
          }
        }
      }

      &-dots {
        position: absolute;
        top: 0.8rem;
        right: 1rem;
        cursor: pointer;
        padding: 0.2rem;
        border-radius: 50%;

        &:hover {
          background-color: var(--main-color-lighter);
        }

        img {
          padding: 0;
          height: 2rem;
          width: 2rem;

          &:hover {
            background-color: unset;
          }
        }
      }
    }
  }
</style>
