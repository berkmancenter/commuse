<template>
  <div class="people-map" ref="map">
  </div>
</template>

<script>
  import 'leaflet/dist/leaflet.css'
  import 'leaflet/dist/leaflet'
  import 'leaflet.markercluster/dist/MarkerCluster.css'
  import 'leaflet.markercluster/dist/MarkerCluster.Default.css'
  import 'leaflet.markercluster/dist/leaflet.markercluster.js'

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
        if (this.$store.state.people.people.length === 0) {
          this.mitt.emit('spinnerStart')

          let people = null
          try {
            people = await this.$store.dispatch('people/fetchPeople')
          } catch (error) {
            this.mitt.emit('spinnerStop')
            return
          }

          this.$store.dispatch('people/setPeople', people)

          this.mitt.emit('spinnerStop')
        }

        const map = L.map(this.$refs.map, {
          center: [51.505, -0.09],
          zoom: 3,
        })

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          noWrap: true,
        }).addTo(map)

        const clusters = {}
        this.$store.state.people.people.forEach(person => {
          if (person['current_location_lat']) {
            const clusterKey = person['current_location_lat'].replace(/\D/g,'') + '_' + person['current_location_lon'].replace(/\D/g,'')

            clusters[clusterKey] = clusters[clusterKey] || []

            clusters[clusterKey].push(
              {
                lat: person['current_location_lat'],
                lon: person['current_location_lon'],
                first_name: person['first_name'],
                last_name: person['last_name'],
                city: person['current_city'],
                id: person['id'],
              }
            )
          }
        })

        for (const [latlon, cluster] of Object.entries(clusters)) {
          const markersCluster = L.markerClusterGroup()

          cluster.forEach(markerData => {
            const marker = L.marker([markerData['lat'], markerData['lon']])
            marker.bindPopup(`${markerData['first_name']} ${markerData['last_name']}<br>${markerData['city']}<br><br><a href="${this.apiUrl}/people/${markerData['id']}" target="_blank">Click to see person profile</a>`)
            markersCluster.addLayer(marker)
          })

          map.addLayer(markersCluster)
        }
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
