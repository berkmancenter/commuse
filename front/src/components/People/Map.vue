<template>
  <div class="people-map" ref="map">
  </div>
</template>

<script>
  import 'leaflet/dist/leaflet.css'
  import 'leaflet/dist/leaflet'
  import { waitUntil } from '@/lib/wait_until'

  export default {
    name: 'Map',
    data() {
      return {
        apiUrl: import.meta.env.VITE_API_URL,
      }
    },
    mounted() {
      this.initMap()
    },
    methods: {
      async initMap() {
        if (this.$store.state.app.people.length === 0) {
          this.mitt.emit('spinnerStart')

          let people = null
          try {
            people = await this.$store.dispatch('app/fetchPeople')
          } catch (error) {
            this.mitt.emit('spinnerStop')
            return
          }

          this.$store.dispatch('app/setPeople', people)

          this.mitt.emit('spinnerStop')
        }

        const map = L.map(this.$refs.map, {
          center: [51.505, -0.09],
          zoom: 3,
        })

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
        }).addTo(map)

        this.$store.state.app.people.forEach(person => {
          if (person['current_location_lat']) {
            const marker = L.marker([person['current_location_lat'], person['current_location_lon']]).addTo(map)

            marker.bindPopup(`${person['first_name']} ${person['last_name']}<br><br><a href="${this.apiUrl}/person/${person['id']}" target="_blank">Click to see person profile</a>`)
          }
        });
      },
    },
  }
</script>

<style lang="scss">
  .people-map {
    width: 100%;
    height: calc(100vh - 8rem);
    max-width: 2200px;
  }
</style>
