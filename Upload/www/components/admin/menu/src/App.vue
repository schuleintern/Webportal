<template>
  <div class="component-menu">

    <Error v-bind:error="error"></Error>

    <div v-if="loading == true" class="overlay">
      <i class="fa fas fa-sync-alt fa-spin"></i>
    </div>

    <Menu v-show="show == 'menu'" v-bind:list="list"></Menu>

    <ItemsCats v-show="show == 'items'" v-bind:items="items"></ItemsCats>

    <ItemForm v-show="show == 'form'" v-bind:item="itemOpen" v-bind:items="items" v-bind:pages="pages"></ItemForm>

  </div>
</template>

<script>
import Error from './mixins/Error.vue';

import Menu from './components/Menu.vue';
import ItemsCats from './components/ItemsCats.vue';
import ItemForm from './components/ItemForm.vue';

const axios = require('axios').default;

export default {
  components: {
    Error,
    Menu,
    ItemsCats,
    ItemForm
  },
  data() {
    return {
      selfURL: globals.selfURL,
      error: false,
      loading: false,

      show: '',
      itemOpen: false,

      pages: globals.pages,
      list: false,
      items: false

    };
  },
  created: function () {
    this.loadExtensions();

    EventBus.$on('show--set', data => {
      if (!data.show) {
        return false;
      }
      this.show = data.show;
    });

    EventBus.$on('menu--open', data => {
      if (!data.item.id) {
        return false;
      }
      this.loadItems(data.item);
    });

    EventBus.$on('item-form--open', data => {

      if (data.item && data.item.id) {
        this.itemOpen = data.item;
      } else {
        this.itemOpen = {};
      }
      if (data.parent) {
        this.itemOpen.parent_id = data.parent.id;
        this.itemOpen.parent_title = data.parent.title;
      }
      this.show = 'form';

    });

    EventBus.$on('item-form--sort', data => {
      if (!data.items) {
        return false;
      }

      this.loading = true;
      var that = this;
      var formData = new FormData();
      formData.append("items", JSON.stringify(data.items) );
      axios.post(this.selfURL+'&task=item-sort', formData)
          .then(function (response) {
            //console.log(response);
            if ( response.data ) {
              if (response.data.error != true) {
              } else {
                that.error = response.data.msg;
              }
            } else {
              that.error = 'Fehler beim Laden. 01';
            }
          })
          .catch(function (error) {
            that.error = 'Fehler beim Laden. 02';
          }).finally(function () {
        // always executed
        that.loading = false;
      });
    });


    EventBus.$on('item-form--submit', data => {
      if (!data.item.title) {
        return false;
      }
      this.loading = true;
      var that = this;
      var formData = new FormData();
      formData.append("id", data.item.id || false );
      formData.append("title", data.item.title || '' );
      formData.append("icon", data.item.icon || '' );
      formData.append("params", data.item.params || '' );
      formData.append("pageurl", data.item.page || '' );
      formData.append("parent_id", data.item.parent_id || 0 );
      formData.append("access", JSON.stringify(data.item.access) || '' );
      axios.post(this.selfURL+'&task=item-submit&id='+data.item.id, formData)
      .then(function (response) {
        //console.log(response);
        if ( response.data ) {
          if (response.data.error != true) {
            that.loadItems(that.openMenu);
            that.show = 'items';
          } else {
            that.error = response.data.msg;
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      })
      .catch(function (error) {
        that.error = 'Fehler beim Laden. 02';
      }).finally(function () {
        // always executed
        that.loading = false;
      });
    });

    EventBus.$on('item-form--delete', data => {
      if (!data.item.id) {
        return false;
      }
      this.loading = true;
      var that = this;
      var formData = new FormData();
      formData.append("id", data.item.id || false );
      axios.post(this.selfURL+'&task=item-delete&id='+data.item.id, formData)
          .then(function (response) {
            //console.log(response);
            if ( response.data ) {
              that.loadItems(that.openMenu);
            } else {
              that.error = 'Fehler beim Laden. 01';
            }
          })
          .catch(function (error) {
            that.error = 'Fehler beim Laden. 02';
          }).finally(function () {
        // always executed
        that.loading = false;
      });
    });



    EventBus.$on('item-form--active', data => {
      if (!data.item.id) {
        return false;
      }
      this.loading = true;
      var that = this;
      var formData = new FormData();
      formData.append("id", data.item.id || false );
      formData.append("active", data.item.active );
      axios.post(this.selfURL+'&task=item-active&id='+data.item.id, formData)
          .then(function (response) {
            if ( response.data ) {
              data.item.active = response.data.active;
            } else {
              that.error = 'Fehler beim Laden. 01';
            }
          })
          .catch(function (error) {
            that.error = 'Fehler beim Laden. 02';
          }).finally(function () {
            // always executed
            that.loading = false;
          });
    });

  },
  methods: {


    loadItems: function (item) {

      if (!item.alias) {
        return false;
      }
      this.loading = true;
      var that = this;
      axios.get( this.selfURL+'&task=api-items&id='+item.alias)
          .then(function(response){

            if ( response.data ) {
              that.items = response.data;
              that.show = 'items';
              that.openMenu = item;
            } else {
              that.error = 'Fehler beim Laden. 01';
            }

          })
          .catch(function(){
            that.error = 'Fehler beim Laden. 02';
          })
          .finally(function () {
            // always executed
            that.loading = false;
          });

    },


    loadExtensions: function () {

      this.loading = true;

      var that = this;
      axios.get( this.selfURL+'&task=api-all')
      .then(function(response){
        
        if ( response.data ) {
          that.list = response.data;
          that.show = 'menu';
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
        
      })
      .catch(function(){
        that.error = 'Fehler beim Laden. 02';
      })
      .finally(function () {
        // always executed
        that.loading = false;
      }); 

    }
  }

};
</script>

<style>

</style>