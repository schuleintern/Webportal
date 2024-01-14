<template>
  <div>
    <Error v-bind:error="error"></Error>
    <Spinner v-bind:loading="loading"></Spinner>

    <List v-if="tabOpen == 'list' && acl.read == 1" v-bind:items="items" v-bind:acl="acl" ></List>
    <Item v-if="tabOpen == 'item' && acl.read == 1" v-bind:item="item" v-bind:tabs="tabs" v-bind:content="content" v-bind:acl="acl"></Item>

    <Form v-if="tabOpen == 'form' && acl.write == 1" v-bind:item="item"></Form>


  </div>
</template>

<script>

const axios = require('axios').default;

import Error from './mixins/Error.vue'
import Spinner from './mixins/Spinner.vue'

//import ModalForm from './mixins/ModalForm.vue'
import Item from './components/Item.vue'

import Form from './components/Form.vue'
import List from './components/List.vue'


export default {
  components: {
    Error, Spinner, Form, List, Item
  },
  data() {
    return {
      apiURL: globals.apiURL,
      acl: globals.acl,

      loading: false,
      error: false,

      items: [],
      item: {
        members: [],
        owners: []
      },
      tabs: [],
      content: [],

      tabOpen: 'list'

    };
  },
  created: function () {

    this.loadLists();

    EventBus.$on('tab--open', data => {
      if (!data.tabOpen) {
        return false;
      }

      if (data.tabOpen == 'list') {
        this.item = {
          members: [],
          owners: []
        };
      }


      this.tabOpen = data.tabOpen;
    });


  },
  mounted() {

    var that = this;

    EventBus.$on('item--open', data => {

      if (data.item) {
        that.item = data.item;
        that.tabOpen = 'item';

        that.loadTabs(data.item);

      }
    });



    EventBus.$on('form--open', data => {


      //if (data.item) {
        //that.form = data.item;
        that.tabOpen = 'form';

        //that.loadTabs(data.item);

        //this.form = data.item;
        //EventBus.$emit('modal-form--open');
      //}

    });


    EventBus.$on('item--info', data => {

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);
      formData.append('info', data.item.info);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/setInfo', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {

          if (response.data.error) {
            that.error = ''+response.data.msg;
          } else {
            //data.item.favorite = response.data.favorite;
          }
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
    });

    EventBus.$on('item--favorite', data => {

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/setFavorite', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {

          if (response.data.error) {
            that.error = ''+response.data.msg;
          } else {
             data.item.favorite = response.data.favorite;
          }
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
    });



    EventBus.$on('tab--change', data => {

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);
      formData.append('list_id', data.item.list_id);
      formData.append('title', data.item.title);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/setTab', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.error) {
            that.error = ''+response.data.msg;
          } else {

          }
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


    });




    EventBus.$on('tab--content', data => {

      if (!data.item.id) {
        console.log('missing');
        return false;
      }
      if (!data.item.list_id) {
        console.log('missing');
        return false;
      }
      const formData = new FormData();
      formData.append('tab_id', data.item.id);
      formData.append('list_id', data.item.list_id);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/getContent', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.error) {
            that.error = ''+response.data.msg;
          } else {
            that.content = response.data;
          }
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


    });


    EventBus.$on('tab--add', data => {

      //console.log(data.item);

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('list_id', data.item.id);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/setTab', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.error == false) {

            that.loadTabs(data.item);

          } else {
            that.error = ''+response.data.msg;
          }
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


    });

    EventBus.$on('tab--delete', data => {

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/deleteTab', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.error) {
            that.error = ''+response.data.msg;
          } else {
            that.loadTabs({'id': data.item.list_id});
            that.content = [];
          }
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


    });




    EventBus.$on('content--submit', data => {

      //console.log(data);

      if (!data.member_id) {
        console.log('missing memberID');
        return false;
      }
      if (!data.tab_id) {
        console.log('missing tabID');
        return false;
      }
      if (!data.list_id) {
        console.log('missing listID');
        return false;
      }

      const formData = new FormData();
      formData.append('list_id', data.list_id);
      formData.append('item_id', data.item_id);
      formData.append('member_id', data.member_id);
      formData.append('tab_id', data.tab_id);
      if (data.toggle !== undefined) {
        formData.append('toggle', data.toggle);
      }
      if (data.info) {
        formData.append('info', data.info);
      }


      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/setContent', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.error == false) {

            EventBus.$emit('tab--content', {
              item: {
                id: data.tab_id,
                list_id: that.item.id
              }
            });

          } else {
            that.error = ''+response.data.msg;
          }
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



    });



    EventBus.$on('form--submit', data => {

      if (!data.item.title
          || !data.members || data.members.length < 1) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);
      formData.append('title', data.item.title);
      formData.append('members', data.members);
      formData.append('owners', data.owners);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/setList', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.insert) {
            that.loadLists();
            if (data.item.id) {
              EventBus.$emit('tab--open', {
                tabOpen: 'item'
              });
            } else {
              EventBus.$emit('tab--open', {
                tabOpen: 'list'
              });
            }
          } else {
            that.error = ''+response.data.msg;
          }
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



    });

    EventBus.$on('list--delete', data => {

      if (!data.item.id) {
        console.log('missing');
        return false;
      }

      const formData = new FormData();
      formData.append('id', data.item.id);

      this.loading = true;
      var that = this;
      axios.post( this.apiURL+'/deleteList', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(function(response){
        if ( response.data ) {
          if (response.data.error) {
            that.error = ''+response.data.msg;
          } else {
            that.loadLists();
            EventBus.$emit('tab--open', {
              tabOpen: 'list'
            });
          }
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


    });



  },
  methods: {


    loadTabs: function (item) {

      if (!item.id) {
        return false;
      }
      this.loading = true;
      var that = this;
      axios.get( this.apiURL+'/getTabs/'+item.id)
          .then(function(response){
            if ( response.data ) {
              if (!response.data.error) {

                that.tabs = response.data;

              } else {
                that.error = ''+response.data.msg;
              }
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

    loadLists: function () {
      this.loading = true;
      var that = this;
      axios.get( this.apiURL+'/getList')
      .then(function(response){
        if ( response.data ) {
          if (!response.data.error) {

            that.items = response.data;

          } else {
            that.error = ''+response.data.msg;
          }
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