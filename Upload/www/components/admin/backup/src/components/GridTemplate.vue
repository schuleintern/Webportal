<template>
  <div class="gridtemplate">
    <table class="noselect">
      <thead>
        <tr>
          <td><span @click="selectAllToggle()">Select all</span></td>
          <td v-bind:key="index" v-for="(item, index) in columns"
            :class="{ active: sortKey == item }">
            <span v-if="columsHeader[index]" @click="sortBy(item)">{{columsHeader[index]}}</span>
            <i class="fa "
              :class="{
                'fa-sort-down': sortOrders[item] == 1 ,
                'fa-sort-up': sortOrders[item] == -1
              }"></i>
          </td>
        </tr>
      </thead>
      <tbody>
        <tr v-bind:key="index" v-for="(entry, index) in  filteredlist" >
          
          <td><input type="checkbox" :checked="entry.selected" @click="clickHandler(entry, {shiftKey: true})"/></td>
          <td v-bind:key="index" v-for="(item, index) in columns" >

            <i v-if="item == 'isRead' && entry[item] == 1" class=""></i>
            <i v-else-if="item == 'isRead' && entry[item] == 0" class="fa fa-envelope-o"></i>
            <i v-else-if="item == 'priority' && entry[item] == 'NORMAL'" class=""></i>
            <i v-else-if="item == 'priority' && entry[item] == 'HIGH'" class="fa fa-arrow-up text-red"></i>
            <i v-else-if="item == 'priority' && entry[item] == 'LOW'" class="fa fa-arrow-down text-green"></i>
            <i v-else-if="item == 'hasAttachment' && entry[item] " class="fa fa-file-o"></i>
            <i v-else-if="item == 'hasAttachment' && entry[item] == '' " class=""></i>
            <span v-else
              @click="clickHandler(entry, $event)" >
              {{entry[item]}}
            </span>

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
    columsHeader: Array,
    filterKey: String
  },
  data: function () {
    //var sortOrders = {}
    // this.columns.forEach(function (key) {
    //   sortOrders[key] = 0
    // })
    return {
      sortKey: '',
      sortOrders: {},

      clickHandlerList: [],
      clickHandlerNode: false
    }
    
  },
  computed: {
    filteredlist: function () {

      var filterKey = this.filterKey && this.filterKey.toLowerCase()
      var order = parseInt(this.sortOrders[this.sortKey]) || 1
      var list = this.list
      if (filterKey) {
        list = list.filter(function (row) {
          return Object.keys(row).some(function (key) {
            return String(row[key]).toLowerCase().indexOf(filterKey) > -1
          })
        })
      }
      // console.log('this.sortOrders');
      //console.log('sortKey',this.sortKey);
       //console.log('order',order);
      //  console.log(list);

      var that = this;
      
      if (list) {

        list = list.slice().sort(function (a, b) {
          if (that.sortKey == 'timeFormat') {
            a = a['messageTime']
            b = b['messageTime']
          } else {
            a = a[that.sortKey]
            b = b[that.sortKey]
          }
          return (a === b ? 0 : a > b ? 1 : -1) * order
        })

        for (var i = 0; i < list.length; i++) {
          for (var j = 0; j < this.clickHandlerList.length; j++) {
            if (list[i].id == this.clickHandlerList[j].id) {
              list.selected = true;
            }
          } 
        }

      }
      
      
      return list;
    }
  },
  filters: {
    capitalize: function (str) {
      if (str) {
        return str.charAt(0).toUpperCase() + str.slice(1)
      }
      return '';
      
    }
  },
  methods: {

    selectAllToggle: function () {

      console.log('toggle', this.clickHandlerList.length);

      if (this.clickHandlerList.length > 1) {
        this.clickHandlerList = [];
        for (var i = 0; i < this.filteredlist.length; i++) {
          this.filteredlist[i].selected = false;
        }

      } else {
        // for (var i = 0; i < this.filteredlist.length; i++) {
        //   this.clickHandlerList.push(this.filteredlist[i]);
        //   this.filteredlist[i].selected = true;
        // }
        this.clickHandlerList = this.filteredlist;
      }
      

    },

    clickHandler: function(item, $event) {


      if (!item) {
        return false;
      }

      if ( $event.shiftKey ) {

        var found = false;
        for (var i = 0; i < this.clickHandlerList.length; i++) {
          if (this.clickHandlerList[i].id == item.id) {

            this.clickHandlerList[i].selected = false;

            var index = this.clickHandlerList.indexOf( this.clickHandlerList[i] );
            this.clickHandlerList.splice(index, 1);
            found = true;
          }
        }
        if (found == false) {

          item.selected = true;
          this.clickHandlerList.push(item);
        }

        EventBus.$emit('message--close', {})

      } else {

        if (this.clickHandlerList) {
          for (var i = 0; i < this.clickHandlerList.length; i++) {
            this.clickHandlerList[i].selected = false;
          }
        }  
        item.selected = true;

        this.clickHandlerList = [ item ];

        EventBus.$emit('message--open', {
          message: this.clickHandlerList[0],
        })

      }


      EventBus.$emit('message--list', {
        list: this.clickHandlerList
      })
      
      

      

    },
    sortBy: function (key) {

      if (!key) {
        return false;
      }
      // if (key == 'timeFormat') {
      //   key = 'messageTime';
      // }

      this.sortKey = key
      var order = parseInt(this.sortOrders[this.sortKey]) || 1;

      // console.log( 'value:', this.sortOrders[this.sortKey ] );

      if ( this.sortKey in this.sortOrders) {
        
        // console.log('is set.....');
        //this.sortOrders[this.sortKey] = order * -1;

        this.$set(this.sortOrders, this.sortKey, order * -1 )
        //this.sortOrders['timeFormat'] = this.sortOrders['timeFormat']   * -1;
      } else {
        
        // not set.
        //this.sortOrders[this.sortKey ] = 1;
        this.sortOrders = {};
        this.$set(this.sortOrders, this.sortKey, 1 )
        // console.log('not set....');
      }

       //console.log('# ',this.sortOrders[this.sortKey]);
      // console.log('sortKey',this.sortKey);
       //console.log('order',order);

      
    }
  }
}


</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
/* 
table {
  border: 2px solid #42b983;
  border-radius: 3px;
  background-color: #fff;
  margin-bottom: 3rem;
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
} */

</style>
