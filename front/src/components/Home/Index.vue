<template>
  <div class="news-section">
    <h3 class="is-size-3 has-text-weight-bold mb-4">News & Events</h3>

    <div class="card" v-for="(news) in $store.state.app.news">
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

        <div class="content">

        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import LazyLoad from 'vanilla-lazyload'

  export default {
    name: 'HomeIndex',
    data() {
      return {
        lazyLoadInstance: null,
      }
    },
    computed: {},
    created() {
      this.mitt.emit('spinnerStart')
      this.initialDataLoad()
      this.initLazyLoad()
    },
    mounted() {},
    methods: {
      async initialDataLoad() {
        const news = await this.$store.dispatch('app/fetchNews')
        this.$store.dispatch('app/setNews', news)
        this.$nextTick(() => {
          this.lazyLoadInstance.update()
        })
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
