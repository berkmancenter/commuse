<template>
  <div>
    <table class="table">
      <tr><th colspan="2">New/changed</th></tr>
      <tr v-for="(changeNew, key) in auditDataItem.changes.new" :key="key">
        <td class="no-break">{{ key }}</td>
        <td>{{ changeNew }}</td>
      </tr>
    </table>

    <table class="table">
      <tr><th colspan="2">Old/removed</th></tr>
      <tr v-for="(changeOld, key) in auditDataItem.changes.old" :key="key">
        <td class="no-break">{{ key }}</td>
        <td>{{ changeOld }}</td>
      </tr>
    </table>
  </div>
</template>

<script>
export default {
  name: 'ChangesList',
  props: {
    auditDataItem: {
      type: Object,
      required: true
    }
  }
}
</script>

<style></style>
