<template>
  <div class="news-section">
    <h3 class="is-size-3 has-text-weight-bold mb-4">News & events</h3>

    <div class="card" v-for="news in newsItems" :key="news.id">
      <div class="card-content">
        <div class="media">
          <div class="media-left">
            <figure class="image">
              <img class="lazy" :data-src="news.image_url">
            </figure>
          </div>
          <div class="media-content">
            <p class="title is-4">
              <div v-if="!news.remote_url">{{ news.title }}</div>
              <div v-if="news.remote_url">
                <a :href="news.remote_url" target="_blank">{{ news.title }}</a>
              </div>
            </p>
            <p>{{ news.short_description }}</p>
          </div>
        </div>
      </div>
    </div>

    <vue-awesome-paginate
      :total-items="paginateTotalItems"
      :items-per-page="20"
      :max-pages-shown="5"
      :hidePrevNextWhenEnds="true"
      v-model="page"
      @click="paginateChangePage"
    />
  </div>
</template>

<script>
  import LazyLoad from 'vanilla-lazyload'

  export default {
    name: 'HomeIndex',
    data() {
      return {
        lazyLoadInstance: null,
        paginateTotalItems: 0,
        newsItems: [],
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
      this.loadNews()
      this.initLazyLoad()
    },
    methods: {
      async loadNews() {
        this.mitt.emit('spinnerStart')

        const response = await this.$store.dispatch('app/fetchNews', {
          paginateCurrentPage: this.page,
        })

        this.newsItems = response.items
        this.paginateTotalItems = response.metadata.total

        this.$nextTick(() => {
          this.lazyLoadInstance.update()
        })

        window.scrollTo({ top: 0 })

        this.mitt.emit('spinnerStop')
      },
      initLazyLoad() {
        this.lazyLoadInstance = new LazyLoad({
          callback_error: (img) => {
            img.setAttribute('src', '')
          },
          unobserve_entered: true,
          unobserve_completed: true,
        })
        this.lazyLoadInstance.update()
      },
      paginateChangePage(page) {
        this.$router.push({ name: 'home.index', query: { ...this.$route.query, page: page }})
      },
    },
    watch: {
      page () {
        this.loadNews()
      },
    },
  }
</script>

<style lang="scss">
  .news-section {
    .card {
      .media {
        margin-bottom: 0;

        .media-left {
          max-width: 20%;
        }

        img {
          max-width: 250px;
        }
      }

      margin-bottom: 2rem;
    }

    @media (max-width: 768px) {
      .card {
        .media {
          flex-direction: column;

          .media-left {
            max-width: 100%;
            width: 100%;
            margin-bottom: 1rem;
          }

          .title {
            margin-bottom: 0.5rem;
          }
        }
      }
    }
  }
</style>
