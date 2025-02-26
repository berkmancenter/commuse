<template>
  <div class="commuse-table-wrapper">
    <table class="table table-hovered commuse-table" :class="tableClasses" :ref="tableRefName">
      <slot></slot>
    </table>
  </div>
</template>

<script>
  import 'tablesort/tablesort.css'
  import Tablesort from 'tablesort'

  export default {
    name: 'CuTable',
    props: {
      tableRefName: {
        type: String,
        required: false,
        default: 'Table',
      },
      tableClasses: {
        type: Array,
        required: false,
        default: [],
      },
    },
    mounted() {
      this.initTableSorting()
    },
    methods: {
      initTableSorting() {
        new Tablesort(this.$refs[this.tableRefName], {
          descending: true,
        })
      },
    },
  }
</script>

<style lang="scss">
  .commuse-table-wrapper {
    overflow-x: auto;
  }

  .commuse-table {

    td {
      word-break: break-word;
      vertical-align: middle;
    }

    input[type=checkbox] {
      transform: scale(2);
      cursor: pointer;
    }

    &.table-hovered {
      tbody {
        tr:hover {
          background-color: #e4e4e4;

          td {
            background-color: #e4e4e4;
          }
        }
      }
    }

    &-row-cell-narrow {
      max-width: 120px;
      white-space: normal;
      word-break: normal !important;
    }
  }

  .commuse-table-actions {
    border-left: 1px solid #dbdbdb !important;
    padding-left: 1rem;
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;

    > * {
      width: 3rem;

      img {
        width: 100%;
      }
    }
  }

  .commuse-table-selector {
    width: 6rem;
  }
</style>
