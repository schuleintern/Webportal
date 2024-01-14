<template>

  <div class="">


    {{ item }}

    <div class="flex-row">
      <div class="flex-1">
        <a href="#list" class="si-btn si-btn-light" v-on:click="handlerBack"><i class="fa fa-angle-left"></i> Zurück</a>
      </div>

    </div>

    <h2 class="padding-b-l">{{ item.vorname }} {{ item.nachname }}</h2>

    <User v-if="item" v-bind:data="item"></User>

    <div class="si-form">
      <ul>
        <li>
          <label>Montag</label>

          <div>
            <button v-if="item.toggle == 1" class="si-btn si-btn-toggle-on" v-on:click="handlerToggleMember(item)">
              <i class="fa fas fa-toggle-on"></i> An</button>
            <button v-else class="si-btn si-btn-toggle-off" v-on:click="handlerToggleMember(item)">
              <i class="fa fas fa-toggle-off"></i> Aus</button>

            <select>
              <option>Gruppe 1</option>
            </select>

            <input type="text" placeholder="Info" class="">
          </div>

        </li>
        <li>
          <label>Dienstag</label>

          <div>
            <button v-if="item.toggle == 1" class="si-btn si-btn-toggle-on" v-on:click="handlerToggleMember(item)">
              <i class="fa fas fa-toggle-on"></i> An</button>
            <button v-else class="si-btn si-btn-toggle-off" v-on:click="handlerToggleMember(item)">
              <i class="fa fas fa-toggle-off"></i> Aus</button>

            <select>
              <option>Gruppe 1</option>
            </select>

            <input type="text" placeholder="Info" class="">
          </div>

        </li>
      </ul>
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
    console.log(this.tabs);
    console.log(this.tabActive);

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
