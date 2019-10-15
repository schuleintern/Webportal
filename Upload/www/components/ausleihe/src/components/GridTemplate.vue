<template>
  <div class="gridtemplate">

{{sortKey}}
<hr>
{{sortOrders}}

  <table>
    <thead>
      <tr>
        <th v-bind:key="index" v-for="(item, index) in columns"
        @click="sortBy(item)"
        :class="{ active: sortKey == item }">
        {{ item | capitalize }}
        <span class="arrow" :class="sortOrders[item] > 0 ? 'asc' : 'dsc'">
        </span>
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-bind:key="index" v-for="(entry, index) in  filteredlist">
        <td v-bind:key="index" v-for="(item, index) in columns">
          {{entry[item]}}
        </td>
      </tr>
    </tbody>
  </table>

  </div>
</template>

<script>

export default {
  name: 'GridTemplate',
  template: '#grid-template',
  props: {
    list: Array,
    columns: Array,
    filterKey: String
  },
  data: function () {
    var sortOrders = {}
    this.columns.forEach(function (key) {
      sortOrders[key] = 1
    })
    return {
      sortKey: '',
      sortOrders: sortOrders
    }
    
  },
  computed: {
    filteredlist: function () {
      var sortKey = this.sortKey
      var filterKey = this.filterKey && this.filterKey.toLowerCase()
      var order = this.sortOrders[sortKey] || 1
      var list = this.list
      if (filterKey) {
        list = list.filter(function (row) {
          return Object.keys(row).some(function (key) {
            return String(row[key]).toLowerCase().indexOf(filterKey) > -1
          })
        })
      }
      console.log('order',order);
      if (sortKey) {
        list = list.slice().sort(function (a, b) {
          a = a[sortKey]
          b = b[sortKey]
          return (a === b ? 0 : a > b ? 1 : -1) * order
        })
      }
      return list
    }
  },
  filters: {
    capitalize: function (str) {
      return str.charAt(0).toUpperCase() + str.slice(1)
    }
  },
  methods: {
    sortBy: function (key) {

      if (key == 'timeFormat') {
        console.log(111);
        key = 'messageTime';
      }

      this.sortKey = key

      if (!(key in this.sortOrders)) {
        // not set.
        console.log('dddd');
        this.sortOrders[key] = 1;
      } else {
        
        this.sortOrders[key] = this.sortOrders[key] * -1
        console.log('aaaa',this.sortOrders[key]);
      }


      
    }
  }
}


</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

table {
  border: 2px solid #42b983;
  border-radius: 3px;
  background-color: #fff;
}

th {
  background-color: #42b983;
  color: rgba(255,255,255,0.66);
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

td {
  background-color: #f9f9f9;
}

th, td {
  min-width: 120px;
  padding: 10px 20px;
}

th.active {
  color: #fff;
}

th.active .arrow {
  opacity: 1;
}

.arrow {
  display: inline-block;
  vertical-align: middle;
  width: 0;
  height: 0;
  margin-left: 5px;
  opacity: 0.66;
}

.arrow.asc {
  border-left: 4px solid transparent;
  border-right: 4px solid transparent;
  border-bottom: 4px solid #fff;
}

.arrow.dsc {
  border-left: 4px solid transparent;
  border-right: 4px solid transparent;
  border-top: 4px solid #fff;
}

</style>
