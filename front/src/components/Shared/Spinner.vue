<template>
  <Transition name="commuse-spinner-fade">
    <div class="commuse-spinner" v-if="running">
      <div class="commuse-spinner-inner"></div>
    </div>
  </Transition>
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

      this.mitt.on('spinnerStopForce', () => {
        that.stopsToStop = 0
        that.stop()
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
  @keyframes spinner-slide {
    0% {
    transform:  translateX(0) scaleX(0);
    }
    40% {
      transform:  translateX(0) scaleX(0.4);
    }
    100% {
      transform:  translateX(100%) scaleX(0.5);
    }
  }

  .commuse-spinner-fade-enter-active,
  .commuse-spinner-fade-leave-active {
    transition: opacity 0.5s ease;
  }

  .commuse-spinner-fade-enter-from,
  .commuse-spinner-fade-leave-to {
    opacity: 0;
  }

  .commuse-spinner {
    position: fixed;
    height: 5px;
    background-color: var(--main-color);
    top: 4.5rem;
    left: 0;
    right: 0;
    width: 100%;
    z-index: 777;

    .commuse-spinner-inner {
      z-index: 99999;
      position: relative;
      width: 100%;
      height: 100%;
      background-color: var(--secondary-color);
      animation: spinner-slide 2s infinite linear;
      transform-origin: 0% 50%;
    }
  }
</style>
