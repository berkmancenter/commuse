<template>
  <div class="buzz-section commuse-blocks">
    <h3 class="is-size-3 has-text-weight-bold mb-4">
      <Icon :src="buzzIcon"></Icon>
      Buzz
    </h3>

    <div class="buzz-section-editor">
      <div class="panel mb-4">
        <div class="panel-block">
          <ckeditor :editor="editor" v-model="$store.state.buzz.itemContent" :config="editorConfig"></ckeditor>

          <div class="buzz-section-editor-buttons mt-2">
            <ActionButton class="mt-2 mr-2" buttonText="Post" @click="postMessage()" :icon="sendIcon"></ActionButton>
            <ActionButton class="mt-2" buttonText="Clear" @click="clearEditor()" :icon="clearPostIcon"></ActionButton>
          </div>
        </div>
      </div>
    </div>

    <SkeletonPatternLoader :loading="loading">
      <template v-slot:content>
        <div class="buzz-section-item box" v-for="item in $store.state.buzz.items">
          <article class="media">
            <div class="media-left buzz-section-item-media">
              <figure class="image is-64x64">
                <router-link :to="'/people/' + item.user_id">
                  <img :data-src="`${apiUrl}/api/files/get/${item.image_url}`" class="lazy" />
                </router-link>
              </figure>
            </div>
            <div class="media-content">
              <div class="content">
                <div class="buzz-section-item-meta mb-2">
                  <router-link class="buzz-section-item-meta-name" :to="'/people/' + item.user_id">
                    <strong class="mr-2">{{ item.name }}</strong>
                  </router-link>
                  <small>{{ formatTime(item.created_at) }}</small>
                  <div class="buzz-section-item-meta-tags mt-2 mb-4" v-if="item.tags.length > 0">
                    <span class="tag mr-1" v-for="tag in item.tags">{{ tag }}</span>
                  </div>
                </div>

                <div class="buzz-section-item-content" v-html="item.content"></div>
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
</template>

<script>
  import sendIcon from '@/assets/images/send.svg'
  import clearPostIcon from '@/assets/images/clear_post.svg'
  import buzzIcon from '@/assets/images/buzz.svg'

  import LazyLoad from 'vanilla-lazyload'
  import SkeletonPatternLoader from '@/components/Shared/SkeletonPatternLoader.vue'
  import { ClassicEditor, Bold, Essentials, Italic, Paragraph, Undo, Link } from 'ckeditor5'
  import ActionButton from '@/components/Shared/ActionButton.vue'
  import Icon from '@/components/Shared/Icon.vue'
  import profileFallbackImage from '@/assets/images/profile_fallback.png'
  import moment from 'moment'

  export default {
    name: 'BuzzIndex',
    components: {
      SkeletonPatternLoader,
      ActionButton,
      Icon,
    },
    data() {
      return {
        sendIcon,
        clearPostIcon,
        buzzIcon,

        apiUrl: import.meta.env.VITE_API_URL,
        lazyLoadInstance: null,
        relativeTimeUpdater: null,
        listUpdater: null,
        profileFallbackImage,
        paginateTotalItems: 0,
        buzzItems: [],
        loading: true,
        editor: ClassicEditor,
        editorConfig: {
          plugins: [ Bold, Essentials, Italic, Paragraph, Undo, Link ],
          toolbar: [ 'undo', 'redo', '|', 'bold', 'italic', '|', 'link' ],
        },
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
      async reloadBuzz(showLoading = false) {
        if (showLoading) {
          this.loading = true
        }

        const response = await this.$store.dispatch('buzz/fetchItems')

        this.$store.commit('buzz/setItems', response)

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
        const content = this.$store.state.buzz.itemContent

        if (!content.trim()) {
          this.awn.warning('Message cannot be empty.')

          return
        }

        const response = await this.$store.dispatch('buzz/postMessage', {
          content,
        })

        this.clearEditor()
        this.reloadBuzz()

        this.$nextTick(() => {
          this.lazyLoadInstance.update()
        })
      },
      formatTime(timestamp) {
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
    }

    .panel-block {
      padding-top: 1rem;
    }

    &-item {
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
    }
  }
</style>
