<template>
  <div class="width-55vw">

    <div class="flex-row">
      <div class="flex-1">
        <button class="si-btn si-btn-light" v-on:click="handlerBack"><i class="fa fa-angle-left"></i> Zurück</button>
      </div>
    </div>




    <div class="si-form">
      <ul>
        <li>
          <label>Name</label>
          <input type="text" :value="item.title" class="readonly" readonly>
        </li>
        <li>
          <label>UniqID</label>
          <input type="text" :value="item.uniqid" readonly>
        </li>
        <li>
          <label>Position</label>
          <input type="text" :value="item.position" readonly>
        </li>
        <li class="line-oddEven padding-t-m padding-b-m">
          <label class="width-12rem padding-l-l">Sichtbarkeit</label>

          <div v-if="item.access" class="blockInline">

            <button class="si-btn si-btn-toggle-off margin-r-s" :class="{'si-btn-toggle-on': item.access.admin == 1}" v-on:click="handlerToggleAccess('admin')">
              <i v-if="item.access.admin == 1" class="fas fa-toggle-on"></i>
              <i v-if="item.access.admin == 0" class="fas fa-toggle-off"></i> Admin</button>
            <button class="si-btn si-btn-toggle-off margin-r-s" :class="{'si-btn-toggle-on': item.access.adminGroup == 1}" v-on:click="handlerToggleAccess('adminGroup')">
              <i v-if="item.access.adminGroup == 1" class="fas fa-toggle-on"></i>
              <i v-if="item.access.adminGroup == 0" class="fas fa-toggle-off"></i> Moduladmin</button>
            <br>
            <button class="si-btn si-btn-toggle-off margin-r-s" :class="{'si-btn-toggle-on': item.access.teacher == 1}" v-on:click="handlerToggleAccess('teacher')">
              <i v-if="item.access.teacher == 1" class="fas fa-toggle-on"></i>
              <i v-if="item.access.teacher == 0" class="fas fa-toggle-off"></i> Lehrer</button>
            <button class="si-btn si-btn-toggle-off margin-r-s" :class="{'si-btn-toggle-on': item.access.pupil == 1}" v-on:click="handlerToggleAccess('pupil')">
              <i v-if="item.access.pupil == 1" class="fas fa-toggle-on"></i>
              <i v-if="item.access.pupil == 0" class="fas fa-toggle-off"></i> Schüler</button>
            <button class="si-btn si-btn-toggle-off margin-r-s" :class="{'si-btn-toggle-on': item.access.parents == 1}" v-on:click="handlerToggleAccess('parents')">
              <i v-if="item.access.parents == 1" class="fas fa-toggle-on"></i>
              <i v-if="item.access.parents == 0" class="fas fa-toggle-off"></i> Eltern</button>
            <button class="si-btn si-btn-toggle-off margin-r-s" :class="{'si-btn-toggle-on': item.access.other == 1}" v-on:click="handlerToggleAccess('other')">
              <i v-if="item.access.other == 1" class="fas fa-toggle-on"></i>
              <i v-if="item.access.other == 0" class="fas fa-toggle-off"></i> Sonstige</button>

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
    item: Array
  },
  data() {
    return {
    };
  },
  created: function () {
  },
  methods: {

    handlerToggleAccess: function (val) {
      if (this.item.access[val] == 1) {
        this.item.access[val] = 0;
      } else {
        this.item.access[val] = 1;
      }
    },
    handlerSubmit: function () {

      if (!this.item.uniqid) {
        return false;
      }
      EventBus.$emit('item-form--submit', {
        item: this.item
      });


    },
    handlerBack: function () {
      EventBus.$emit('show--list', {});
    }

  }

};
</script>

<style>
</style>