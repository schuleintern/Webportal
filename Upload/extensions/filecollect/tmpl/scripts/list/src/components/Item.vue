<template>

  <div class="" v-if="acl.read == 1">

    <div class="flex-row">
      <div class="flex-1 flex-row ">
        <div class="flex-1">
          <a href="#list" class="si-btn si-btn-light" v-on:click="handlerBack"><i class="fa fa-angle-left"></i> Zurück</a>
          <button class="si-btn margin-l-m" v-on:click="handlerSubmit"><i class="fa fa-save"></i> Speichern</button>
        </div>
        <div class="flex-1 text-right">
          <a  class="si-btn margin-r-m si-btn-green" :href="'index.php?page=ext_filecollect&view=list&task=download&coid='+item.id"
             target="_blank"><i class="fa fa-download"></i> Download </a>
          <a :href="'#'+item.id" class="si-btn si-btn-light si-btn-icon" v-on:click="handlerOpenForm(item)"><i
              class="fa fa-pen"></i> Bearbeiten</a>
        </div>

      </div>
    </div>

    <h2 class="padding-b-l">{{ item.title }}</h2>

    <div class="flex-row padding-b-l">
      <div class="flex-2 padding-r-l">
        <div v-if="item.endDate">
          <label class="margin-r-m">Bis:</label>
          <span>{{ item.endDate }}</span>
        </div>

        <div v-if="item.info" class="si-hinweis margin-r-l">{{ item.info }}</div>
      </div>

      <div v-if="item.members.length > 0" class="flex-1 padding-l-m">
        <div><h5><i class="fa fa-share-alt"></i> Vorausgewählte Benutzer*innen ({{item.members.length}}):</h5>
          <div class=" height-20rem scrollable-y">

              <div v-bind:key="index" v-for="(item, index) in  item.members" class="margin-r-s margin-b-s">
                <User v-bind:data="item"></User>
              </div>

          </div>
        </div>
      </div>
    </div>


    <Folders v-bind:acl="acl" v-bind:item="item"></Folders>


  </div>

</template>


<script>

import User from "../mixins/User.vue";

import Folders from "./Folders.vue";

export default {
  components: {
    User, Folders
  },
  name: 'Item',
  props: {
    item: Object,
    acl: Object
  },
  data() {
    return {}
  },
  computed: {},
  watch: {},
  created: function () {




  },
  mounted() {

  },
  methods: {

    handlerSubmit: function () {

      this.$bus.$emit('folder--submit');

    },
    handlerBack: function () {
      this.$bus.$emit('page--open', {
        back: true
      });
    },
    handlerOpenForm: function (item) {
      this.$bus.$emit('page--open', {
        page: 'form',
        item: item
      });
    },

  }
}
</script>
