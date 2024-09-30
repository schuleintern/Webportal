<template>
  <div class="">

    <ItemModal :info="info"></ItemModal>

    <button v-if="acl.write == 1" class="si-btn" @click="handlerOpen()"><i class="fa fa-plus"></i> Hinzufügen</button>
    <input type="search" class="si-input margin-l-m" v-model="searchString" placeholder="Suche..."/>

    <table class="si-table" v-if="sortList && sortList.length >= 1">
      <thead>
      <tr>
        <th v-on:click="handlerSort('user_id')" class="curser-sort">Benutzer*in</th>
        <td v-bind:key="i" v-for="(strike, i) in  count">{{i+1}}.</td>
        <td></td>
      </tr>
      </thead>
      <tbody>
      <tr v-bind:key="index" v-for="(item, index) in  sortList" class="">
        <td><span v-if="item.user && item.user.name">{{ item.user.name }}</span></td>
        <td v-bind:key="i" v-for="(strike, i) in  count">

          <div class="si-btn-multiple" v-if="showBtnSet(item.counts, i)" >
            <button v-if="item.counts[i] && item.counts[i].createDate" class="si-btn si-btn-icon si-btn-light" @click="handlerInfo(item, item.counts[i])"><i class="fas fa-info-circle"></i></button>
            <button v-else class="si-btn si-btn-icon si-btn-red" @click="handlerCreate(i, item)"><i class="fa fa-plus"></i></button>

            <button v-if="item.counts[i] && item.counts[i].doneDate" class="si-btn si-btn-icon si-btn-light" @click="handlerInfo(item, item.counts[i])"><i class="fa fa-check"></i></button>
            <button v-else class="si-btn si-btn-icon si-btn-green" :class="{'si-btn-off': !item.counts[i] || !item.counts[i].createDate  }" @click="handlerFinish(item.counts[i], item)"><i class="fa fa-check"></i></button>
          </div>
        </td>
        <td></td>
      </tr>
      </tbody>
    </table>
    <div v-else>
      <div class="padding-m"><i>- Noch keine Inhalte vorhanden -</i></div>
    </div>

  </div>
</template>

<script>

import ItemModal from './../mixins/ItemModal.vue'


export default {
  vname: 'ListComponent',
  components: {
    ItemModal
  },
  data() {
    return {

      error: false,
      loading: false,
      sort: {
        column: 'id',
        order: true
      },
      searchColumns: ['id', 'user_id', 'userName'],
      searchString: '',
      info: false

    };
  },
  props: {
    acl: Array,
    list: Array,
    count: Number
  },
  computed: {
    sortList: function () {
      if (this.list) {
        let data = this.list;
        if (data.length > 0) {


          // SUCHE
          if (this.searchString != '') {
            let split = this.searchString.toLowerCase().split(' ');
            var search_temp = [];
            var search_result = [];
            this.searchColumns.forEach(function (col) {
              search_temp = data.filter((item) => {
                return split.every(v => item[col].toLowerCase().includes(v));
              });
              if (search_temp.length > 0) {
                search_result = Object.assign(search_result, search_temp);
              }
            });
            data = search_result;
          }

          /*
          // USER nachladen
          if (data.length <= 0) {
            this.loadUsers(this.searchString);
          }
          */



          // SORTIERUNG
          if (this.sort.column) {
            if (this.sort.order) {
              return data.sort((a, b) => a[this.sort.column].localeCompare(b[this.sort.column]))
            } else {
              return data.sort((a, b) => b[this.sort.column].localeCompare(a[this.sort.column]))
            }
          }


          return data;
        }
      }
      return [];
    }

  },
  created: function () {


  },
  methods: {

    showBtnSet(count,i) {
      if (i-1 <= 0) {
        return true;
      }
      if (count[i-1]) {
        return true;
      }
      return false;
    },
    handlerInfo(item, info) {
      this.info = info;
      this.info.user = item.user;
      this.$bus.$emit('modal-info--open');

    },
    handlerCreate(i, item) {

      item.count = i+1;
      item.typ = 'create';
      this.$bus.$emit('page--open', {
        page: 'form',
        item: item
      });

    },
    handlerFinish(data, item) {

      if (data && data.id) {
        if (item && item.user) {
          data.user = item.user;
        }
        data.typ = 'finish';
        this.$bus.$emit('page--open', {
          page: 'form',
          item: data
        });
      }

    },

    handlerOpen(item) {

      this.$bus.$emit('page--open', {
        page: 'item',
        item: item
      });

    },
    handlerSort: function (column) {
      if (column) {
        this.sort.column = column;
        if (this.sort.order) {
          this.sort.order = false;
        } else {
          this.sort.order = true;
        }
      }
    },

  }


};
</script>

<style>

</style>