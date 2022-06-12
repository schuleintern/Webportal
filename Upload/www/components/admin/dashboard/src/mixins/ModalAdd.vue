<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box">
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">


        <table class="si-table si-table-style-allLeft">
          <thead>
            <tr>
              <td >Erweiterung</td>
              <td>Name</td>
            </tr>
          </thead>
          <tbody>
            <tr v-bind:key="index" v-for="(item, index) in addWidgetList">
              <td >{{item.ext}}</td>
              <td>
                <h4>{{item.title}}</h4>
                <div class="si-btn-multiple">
                    <button v-if="item.access.admin == 1" class="si-btn si-btn-off si-btn-small " > Admin</button>
                    <button v-if="item.access.adminGroup == 1" class="si-btn si-btn-off si-btn-small " > Moduladmin</button>
                    <button v-if="item.access.teacher == 1" class="si-btn si-btn-off si-btn-small " > Lehrer</button>
                  <button v-if="item.access.other == 1" class="si-btn si-btn-off si-btn-small " > Mitarbeiter</button>
                    <button v-if="item.access.pupil == 1" class="si-btn si-btn-off si-btn-small " > Schüler</button>
                    <button v-if="item.access.parents == 1" class="si-btn si-btn-off si-btn-small " > Eltern</button>

                    <span v-if="item.access.admin == 0 && item.access.adminGroup == 0 && item.access.teacher == 0 && item.access.pupil == 0 && item.access.parents == 0 && item.access.other == 0" >
                      <button v-if="item.status == 1" class="si-btn si-btn-red si-btn-small " > Unsichtbar !</button>
                    </span>
                  </div>
              </td>
              <td>
                <button v-if="item.active != 1" class="si-btn" v-on:click="handlerAdd(item)"><i class="fa fa-plus"></i> Hinzufügen</button>
                <button v-if="item.active == 1" class="si-btn si-btn-off" ><i class="fa fa-check"></i> Aktiviert</button>
              </td>
            </tr>
          </tbody>
        </table>

      </div>
    </div>

  </div>
</template>

<script>


export default {

  components: {},
  data() {
    return {
      open: false
    };
  },
  props: {
    data: Object | Boolean,

    addWidgetList: Array
  },
  created: function () {

  },
  mounted() {
    // access our input using template refs, then focus
  },
  watch: {
    data: function (newVal, oldVal) {
      if (newVal == false) {
        this.open = false;
      } else {
        this.open = true;
      }
    }
  },
  methods: {
    handlerClose: function () {
      //this.open = false;
      //this.data = false;
      EventBus.$emit('modalAdd--close');
    },

    handlerAdd: function (item) {

      if (item) {
        EventBus.$emit('modalAdd--add', {
          item: item
        });
      }
    }

  }


};
</script>

<style>

</style>