<template>

  <div class="">

    <div class="flex-row">
      <div class="flex-1">
        <a href="#list" class="si-btn si-btn-light" v-on:click="handlerBack"><i class="fa fa-angle-left"></i> Zurück</a>
      </div>
      <div v-show="item.id" class="flex-row flex-end flex-1">
        <button v-on:click="handlerEdit(item)" class="si-btn si-btn-light margin-r-m"><i
            class="fa fas fa-pencil-alt"></i> Bearbeiten
        </button>
        <span v-if="acl.delete == 1">
          <button v-show="deleteItem == false" v-on:click="handlerDelete(item)" class="si-btn si-btn-light"><i
              class="far fa-trash-alt"></i> Löschen
          </button>
          <button v-show="deleteItem" v-on:click="handlerDeleteSure(item)" class="si-btn si-btn-red"><i
              class="far fa-trash-alt"></i>Endgültig Löschen!
          </button>
        </span>
      </div>
    </div>

    <h2 class="padding-b-l">{{ item.title }}</h2>



    <div class="flex-row padding-b-l">
      <div class="flex-2">

        <button class="si-btn si-btn-off text-bold">{{ item.stats.count }} Benutzer</button>
        <span class="si-btn-multiple">
          <button class="si-btn si-btn-off">{{ item.stats.isPupil }} Schüler </button>
          <button class="si-btn si-btn-off">{{ item.stats.isEltern }} Eltern</button>
          <button class="si-btn si-btn-off">{{ item.stats.isTeacher }} Lehrer</button>
          <button class="si-btn si-btn-off">{{ item.stats.isNone }} Sonstige</button>
        </span>

        <br>

        <textarea class="si-textarea width-100p height-15rem margin-t-m" v-model="item.info"  v-on:change="handlerChangeInfo"></textarea>


      </div>
      <div class="flex-1 padding-l-m">
        <div v-if="item.owners" >
          <h5><i class="fa fa-share-alt"></i>  Teilen mit {{item.owners.length}} Benutzern:</h5>
          <div class=" height-20rem scrollable-y">
            <div v-bind:key="index" v-for="(item, index) in  item.owners" class="margin-b-s" >
              <User v-if="item" v-bind:data="item"></User>
            </div>
          </div>
        </div>
      </div>
    </div>


    <div class="si-btn-multiple margin-b-s">
      <button class="si-btn margin-r-s" :class="{'si-btn-active': tabActive == item}"
              v-bind:key="index" v-for="(item, index) in  tabs"
              v-on:click="handlerOpenTab(item)"
      >{{ item.title }}
      </button>
      <button class="si-btn si-btn-green" v-on:click="handlerAddTab"><i class="fa fa-plus"></i></button>
    </div>

    <div v-if="content && tabActive" class="si-box">

      <div class="flex-row">
        <div class="flex-1">
          <input class="si-input" placeholder="Titel ..." v-model="tabActive.title"
                 v-on:change="handlerChangeTabTitle">

          <a :href="'index.php?page=ext_userlist&view=default&task=print&id='+tabActive.id+'&list_id='+tabActive.list_id" target="_blank" class="si-btn si-btn-icon si-btn-light"><i
              class="fa fas fa-print"></i></a>
          <a :href="'index.php?page=ext_userlist&view=default&task=export&id='+tabActive.id+'&list_id='+tabActive.list_id" class="si-btn si-btn-icon si-btn-light"><i
              class="fa fas fa-download"></i></a>

        </div>
        <div class="flex-row flex-end flex-1">
           <button v-show="deleteTab == false" v-on:click="handlerDeleteTab(tabActive)"
                   class="si-btn si-btn-icon si-btn-light"><i class="far fa-trash-alt"></i></button>
          <button v-show="deleteTab" v-on:click="handlerDeleteTabSure(tabActive)" class="si-btn si-btn-red"><i
              class="far fa-trash-alt"></i> Endgültig Löschen!</button>
        </div>

      </div>

      <table class="si-table si-table-style-allLeft">
        <thead>
          <tr>
            <th width="20%" v-on:click="handlerSort('vorname')" class="curser">Vorname</th>
            <th width="20%" v-on:click="handlerSort('nachname')" class="curser">Nachname</th>
            <th width="10%"v-on:click="handlerSort('type')" class="curser">Typ</th>
            <th width="10%">{{ countContent.on }} An / {{ countContent.off }} Aus</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-bind:key="index" v-for="(item, index) in  sortedArray">
            <td class="">{{ item.vorname }}</td>
            <td class="">{{ item.nachname }}</td>
            <td class="text-grey">
              <span v-if="item.type == 'isPupil'"> Schüler</span>
              <span v-if="item.type == 'isEltern'"> Eltern</span>
              <span v-if="item.type == 'isTeacher'"> Lehrer</span>
              <span v-if="item.type == 'isNone'"> Sonstige</span>
            </td>
            <td class="">
              <button v-if="item.toggle == 1" class="si-btn si-btn-toggle-on" v-on:click="handlerToggleMember(item)"><i
                  class="fa fas fa-toggle-on"></i> An</button>
              <button v-else class="si-btn si-btn-toggle-off" v-on:click="handlerToggleMember(item)"><i
                  class="fa fas fa-toggle-off"></i> Aus</button>
            </td>
            <td class="">
              <input type="text" class="si-input width-100p" maxlength="255" v-model="item.info" v-on:change="handlerInfoMember(item)"/>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>

</template>


<script>

import User from './../mixins/User.vue'

export default {
  components: {
    User
  },
  name: 'List',
  props: {
    item: Object,
    tabs: Array,
    content: Array,
    acl: Object
  },
  data() {
    return {
      deleteItem: false,
      sort: {
        column: false,
        order: true
      },
      tabActive: false,
      deleteTab: false
    }
  },
  computed: {
    sortedArray: function () {

      if (this.sort.column) {
        if (this.sort.order) {
          return this.content.sort((a, b) => a[this.sort.column].localeCompare(b[this.sort.column]))
        } else {
          return this.content.sort((a, b) => b[this.sort.column].localeCompare(a[this.sort.column]))
        }
      }
      return this.content;
    },
    countContent: function () {

      var ret = {
        on: 0,
        off: 0
      };
      this.content.forEach((o) => {
        if (o.toggle == 1) {
          ret.on++;
        } else {
          ret.off++;
        }
      })
      return ret;

    }
  },
  watch: {
    tabs: function (newVal, oldVal) {
      this.handlerOpenTab(this.tabs[this.tabs.length - 1]);
    }
  },
  created: function () {
  },
  mounted() {
    //console.log(this.tabs);
    //console.log(this.tabActive);

  },
  methods: {
    handlerChangeInfo: function () {
      EventBus.$emit('item--info', {
        item: this.item
      });
    },
    handlerEdit: function (item) {
      EventBus.$emit('form--open', {
        item: item
      });
    },
    handlerDeleteTab: function (item) {
      if (!item.id) {
        return false;
      }
      this.deleteTab = item;
      //this.pagesOpen = false;

    },
    handlerDeleteTabSure: function () {
      if (!this.deleteTab.id) {
        return false;
      }

      EventBus.$emit('tab--delete', {
        item: this.deleteTab
      });
      this.tabActive = false;

    },
    handlerChangeTabTitle: function () {
      if (this.tabActive.title != '') {
        EventBus.$emit('tab--change', {
          item: this.tabActive
        });
      }
    },
    handlerOpenTab: function (item) {
      if (!item || !item.id) {
        return false;
      }
      EventBus.$emit('tab--content', {
        item: item
      });
      this.tabActive = item;
      this.deleteTab = false;
    },
    handlerAddTab: function () {
      EventBus.$emit('tab--add', {
        item: this.item
      });
    },
    handlerBack: function () {
      this.deleteItem = false;
      this.deleteTab = false;
      this.tabActive = false;
      //this.item = [];
      EventBus.$emit('tab--open', {
        tabOpen: 'list'
      });
    },
    handlerDelete: function (item) {
      if (!item.id) {
        return false;
      }
      this.deleteItem = item;
      //this.pagesOpen = false;

    },
    handlerDeleteSure: function () {
      if (!this.item.id) {
        return false;
      }
      this.deleteItem = false;
      //this.pagesOpen = false;
      EventBus.$emit('list--delete', {
        item: this.item
      });
    },


    handlerToggleMember: function (item) {


      if (item.member_id && this.tabActive.id && this.tabActive.list_id) {
        let toggle = false;
        if (item.toggle == false || item.toggle == 0 || !item.toggle) {
          toggle = true;
        }
        EventBus.$emit('content--submit', {
          item_id: item.id,
          list_id: this.tabActive.list_id,
          member_id: item.member_id,
          tab_id: this.tabActive.id,
          toggle: toggle
        });


      }
    },
    handlerInfoMember: function (item) {

      if (item.member_id && this.tabActive.id && this.tabActive.list_id) {
        EventBus.$emit('content--submit', {
          item_id: item.id,
          list_id: this.tabActive.list_id,
          member_id: item.member_id,
          tab_id: this.tabActive.id,
          info: item.info
        });
      }
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
    }
  }
}
</script>
