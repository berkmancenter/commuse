<template>
  <div class="commuse-spinner" v-if="running">
    <img :src="loaderGif">
  </div>
</template>

<script>
  import loaderGif from '@/assets/images/loader.gif'

  export default {
    name: 'Spinner',
    data () {
      return {
        running: false,
        loaderGif: loaderGif,
        stopsToStop: 0,
      }
    },
    created() {
      const that = this

      this.mitt.on('spinnerStart', (stopsToStop = 1) => {
        that.stopsToStop = that.stopsToStop + parseInt(stopsToStop)
        that.start()
      })

      this.mitt.on('spinnerStop', () => {
        that.stopsToStop = that.stopsToStop - 1

        if (that.stopsToStop === 0) {
          that.stop()
        }
      })
    },
    methods: {
      start() {
        this.running = true
      },
      stop() {
        this.running = false
      },
    },
  }
</script>

<style lang="scss">
  .commuse-spinner {
    width: 2rem;
    height: 2rem;
    position: fixed;
    bottom: 2rem;
    left: 2rem;
  }
</style>
