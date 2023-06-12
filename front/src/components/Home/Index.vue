<template>
  <div class="news-section">
    <h3 class="is-size-3">News & Events</h3>

    <div class="card" v-for="(news) in $store.state.app.news">
      <div class="card-content">
        <div class="media">
          <div class="media-left">
            <figure class="image">
              <img :src="news.image_url">
            </figure>
          </div>
          <div class="media-content">
            <p class="title is-4">{{ news.title }}</p>
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
  export default {
    name: 'HomeIndex',
    data() {
      return {}
    },
    computed: {},
    created() {
      this.initialDataLoad()
    },
    mounted() {},
    methods: {
      async initialDataLoad() {
        const news = await this.$store.dispatch('app/fetchNews')
        this.$store.dispatch('app/setNews', news)
      }
    },
  }
</script>

<style lang="scss">
  .news-section {
    .card {
      .media {
        margin-bottom: 0;

        .media-left {
          max-width: 30%;
        }
      }

      margin-bottom: 2rem;
    }

    @media (max-width: 575px) {
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
