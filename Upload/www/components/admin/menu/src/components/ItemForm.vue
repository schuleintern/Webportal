<template>
  <div class="">

    <div class="flex-row">
      <div class="flex-1">
        <button class="si-btn si-btn-light" v-on:click="handlerBack"><i class="fa fa-angle-left"></i> Zurück</button>
      </div>
      <div v-show="item.id" class="flex flex-end">
        <button v-show="deleteItem == false" v-on:click="handlerDelete(item)" class="si-btn si-btn-light"><i class="far fa-trash-alt"></i> Löschen</button>
        <button v-show="deleteItem" v-on:click="handlerDeleteSure(item)" class="si-btn si-btn-red"><i class="far fa-trash-alt"></i>Endgültig Löschen!</button>
      </div>
    </div>

    <div class="si-form">
      <ul class="">
        <li class="">
          <label class="">Titel</label>
          <input type="text" v-model="item.title" class="width-40vw" />
        </li>
        <li class="">
          <label class="">Gruppe</label>
          <input type="text" readonly class="select readonly width-20vw" :value="item.parent_title" v-on:click="handlerParentOpen" />
        </li>
        <li class=""></li>
        <li v-show="parentOpen" class=" padding-t-m padding-b-m height_35 scrollable-y">
          <div  class="parent">
            <h4>Menu</h4>
            <div v-bind:key="b" v-for="(menu_item, b) in items" class="" :value="item.id">
              <div class="margin-b-s">
                <button class="si-btn si-btn-border"
                        :class="{'si-btn-active': menu_item.id == item.parent_id }"
                        v-on:click="handlerParentSelect(menu_item)"><i :class="menu_item.icon"></i> {{menu_item.title}}</button>
                <div v-if="menu_item.items.length >= 1" class=" flex-row">
                  <div v-bind:key="i" v-for="(child, i) in menu_item.items" :value="child.id"  class="margin-b-s padding-l-l margin-t-s">
                    <button class="si-btn si-btn-border"
                            :class="{'si-btn-active': child.id == item.parent_id }"
                            v-on:click="handlerParentSelect(child)" ><i :class="child.icon"></i> {{child.title}}</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </li>
        <li class="">
          <label class="">Seite</label>
          <label v-if="item.params" class="small">Params: {{item.params}}</label>
          <input type="text" :value="item.page" readonly class="select readonly width-20vw" v-on:click="handlerPagesOpen"/>
        </li>
        <li class=""></li>
        <li v-show="pagesOpen" class=" padding-t-m padding-b-m  height_35 scrollable-y">
            <div v-bind:key="c" v-for="(sub, c) in pages" class="">
              <h4>{{sub.name}}</h4>
              <div class="flex-row">
                <span v-if="sub.submenu" v-bind:key="d" v-for="(page, d) in sub.submenu" class="margin-b-s" >
                  <button
                      v-if="page.menu != false"
                      class="si-btn si-btn-border margin-r-m"
                      :class="{'si-btn-light': page.admin == true, 'si-btn-active': page.url.page == item.page && JSON.stringify(page.url.params) == item.params}"
                      v-on:click="handlerPagesSelect(page)"><i :class="page.icon"></i>{{page.title}}</button>
                </span>
              </div>
            </div>
        </li>
        <span v-if="item.options">
          <li class=""  v-bind:key="a" v-for="(item, a) in item.options">
            <label class=""><b>Option:</b> {{item.label}}</label>
            <span v-if="item.type == 'text'">
              <input type="text" v-model="item.value" class="width-40vw" >
            </span>
            <span v-if="item.type == 'number'">
              <input type="text" v-model="item.value" class="width-40vw" >
            </span>
          </li>
          </span>
        <li class="">
          <label class="">Icon</label>
          <input type="text" v-model="item.icon" class="width-20vw" />
        </li>
        <li class="" v-if="item.access">
          <label class="">Sichtbarkeit</label>

          <div  class="blockInline margin-l-l">

            <button class="si-btn si-btn-toggle-off margin-r-s" :class="{'si-btn-toggle-on': item.access.admin == 1}" v-on:click="handlerToggleActive('admin')">
              <i v-if="item.access.admin == 1" class="fas fa-toggle-on"></i>
              <i v-if="item.access.admin == 0" class="fas fa-toggle-off"></i> Admin</button>
            <button class="si-btn si-btn-toggle-off margin-r-s" :class="{'si-btn-toggle-on': item.access.adminGroup == 1}" v-on:click="handlerToggleActive('adminGroup')">
              <i v-if="item.access.adminGroup == 1" class="fas fa-toggle-on"></i>
              <i v-if="item.access.adminGroup == 0" class="fas fa-toggle-off"></i> Moduladmin</button>
            <br>
            <button class="si-btn si-btn-toggle-off margin-r-s" :class="{'si-btn-toggle-on': item.access.teacher == 1}" v-on:click="handlerToggleActive('teacher')">
              <i v-if="item.access.teacher == 1" class="fas fa-toggle-on"></i>
              <i v-if="item.access.teacher == 0" class="fas fa-toggle-off"></i> Lehrer</button>
            <button class="si-btn si-btn-toggle-off margin-r-s" :class="{'si-btn-toggle-on': item.access.pupil == 1}" v-on:click="handlerToggleActive('pupil')">
              <i v-if="item.access.pupil == 1" class="fas fa-toggle-on"></i>
              <i v-if="item.access.pupil == 0" class="fas fa-toggle-off"></i> Schüler</button>
            <button class="si-btn si-btn-toggle-off margin-r-s" :class="{'si-btn-toggle-on': item.access.parents == 1}" v-on:click="handlerToggleActive('parents')">
              <i v-if="item.access.parents == 1" class="fas fa-toggle-on"></i>
              <i v-if="item.access.parents == 0" class="fas fa-toggle-off"></i> Eltern</button>
            <button class="si-btn si-btn-toggle-off margin-r-s" :class="{'si-btn-toggle-on': item.access.other == 1}" v-on:click="handlerToggleActive('other')">
              <i v-if="item.access.other == 1" class="fas fa-toggle-on"></i>
              <i v-if="item.access.other == 0" class="fas fa-toggle-off"></i> Sonstige</button>

          </div>

        </li>


        <li>
          <label>Seite im neuen Fenster öffnen</label>
          <div class="blockInline margin-l-l">
            <button v-if="item.target == true" class="si-btn si-btn-toggle-on" v-on:click="handlerToggle"><i
                class="fa fas fa-toggle-on"></i> Ja</button>
            <button v-else class="si-btn si-btn-toggle-off" v-on:click="handlerToggle"><i
                class="fa fas fa-toggle-off"></i> Nein</button>
          </div>
        </li>
        <li>
          <br>
          <button class="si-btn" v-on:click="handlerSubmit"><i class="fas fa-mouse-pointer"></i> Speichern</button>
        </li>
      </ul>
    </div>


  </div>
</template>

<script>



export default {
  components: {

  },
  props: {
    item: Object,
    pages: Array,
    items: Array
  },
  data() {
    return {
      deleteItem: false,
      pagesOpen: false,
      parentOpen: false
    };
  },
  created: function () {
  },
  methods: {

    handlerToggle: function () {

      if (!this.item.target || this.item.target == 0  || this.item.target == '0') {
        this.item.target = 1;
      } else {
        this.item.target = 0;
      }

      return false;
    },
    handlerToggleActive: function (val) {
      if (this.item.access[val] == 1) {
        this.item.access[val] = 0;
      } else {
        this.item.access[val] = 1;
      }
    },
    handlerParentOpen: function () {
      if( this.parentOpen ) {
        this.parentOpen = false;
      } else {
        this.parentOpen = true;
      }
    },
    handlerParentSelect: function (item) {

      if (item.id && item.title) {
        this.item.parent_id = item.id;
        this.item.parent_title = item.title;
      }
      this.parentOpen = false;

    },
    handlerPagesSelect: function (page) {
      if (!page.title || !page.url) {
        return false
      }
      this.item.page = page.url.page;
      this.item.params = JSON.stringify(page.url.params);
      this.item.options = page.url.options;
      this.pagesOpen = false;
    },
    handlerPagesOpen: function () {
      if( this.pagesOpen ) {
        this.pagesOpen = false;
      } else {
        this.pagesOpen = true;
      }
    },
    handlerSubmit: function () {

      if (!this.item.title) {
        return false;
      }
      this.deleteItem = false;
      EventBus.$emit('item-form--submit', {
        item: this.item
      });


    },
    handlerBack: function () {
      this.deleteItem = false;
      this.pagesOpen = false;
      EventBus.$emit('show--set', {
        'show': 'items'
      });
    },
    handlerDelete: function (item) {
      if (!item.id) {
        return false;
      }
      this.deleteItem = item;
      this.pagesOpen = false;

    },
    handlerDeleteSure: function () {
      if (!this.item.id) {
        return false;
      }
      this.deleteItem = false;
      this.pagesOpen = false;
      EventBus.$emit('item-form--delete', {
        item: this.item
      });
    }

  }

};
</script>

<style>
</style>