<template>
  <div class="form-style-2">

    <div class="flex-row">
      <div class="flex-1">
        <button class="btn btn-grey-line" v-on:click="handlerBack"> Zurück</button>
      </div>
      <div v-show="item.id" class="flex flex-end">
        <button v-show="deleteItem == false" v-on:click="handlerDelete(item)" class="btn btn-grey-line"><i class="far fa-trash-alt"></i> Löschen</button>
        <button v-show="deleteItem" v-on:click="handlerDeleteSure(item)" class="btn btn-red"><i class="far fa-trash-alt"></i>Löschen!</button>
      </div>
    </div>

    <br><br>
    <ul class="noListStyle">
      <li class="line-oddEven padding-t-m padding-b-m">
        <label class="width-7rem padding-l-m">Gruppe</label>
        {{item.parent_title}}
      </li>
      <li class="line-oddEven padding-t-m padding-b-m">
        <label class="width-7rem padding-l-m">Seite</label>
        <input type="text" :value="item.page" readonly v-on:click="handlerPagesOpen" class="readonly"/>
        <input type="text" v-model="item.params" placeholder="Params" readonly class="readonly" />
      </li>
      <li v-show="pagesOpen">
        <div>
          <h3>Seiten:</h3>
          <div v-bind:key="index" v-for="(item, index) in pages" class="">
            <h4>{{item.name}}</h4>
            <div class="flex-row">
              <span v-if="item.submenu" v-bind:key="index" v-for="(page, i) in item.submenu" class="margin-b-s" >
                <button v-if="page.menu != false" class="btn btn-grau margin-r-m" :class="{'btn-grey-line': page.admin == true}" v-on:click="handlerPagesSelect(page)"><i :class="page.icon"></i>{{page.title}}</button>
              </span>
            </div>
          </div>
        </div>

      </li>
      <hr>
      <li class="line-oddEven padding-t-m padding-b-m">
        <label class="width-7rem padding-l-m">Title</label>
        <input type="text" v-model="item.title" />
      </li>
      <li class="line-oddEven padding-t-m padding-b-m">
        <label class="width-7rem padding-l-m">Icon</label>
        <input type="text" v-model="item.icon" />
      </li>
      <li>
        <br>
        <button class="btn btn-blau" v-on:click="handlerSubmit">Speichern</button>
      </li>
    </ul>


  </div>
</template>

<script>



export default {
  components: {

  },
  props: {
    item: Array,
    pages: Array
  },
  data() {
    return {
      deleteItem: false,
      pagesOpen: false
    };
  },
  created: function () {
  },
  methods: {

    handlerPagesSelect: function (page) {
      if (!page.title || !page.url) {
        return false
      }
      this.item.page = page.url.page;
      this.item.params = JSON.stringify(page.url.params);
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