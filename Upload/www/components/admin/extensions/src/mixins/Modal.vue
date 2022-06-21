<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box" >
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">

        <h4 class="text-green"><i class="fas fa-check-circle"></i> Erweiterung wurde erfolgreich installiert!</h4>

        <p class="padding-t-m padding-b-m width-30vw">Um die Erweiterung nutzen zu können, müssen sie diese noch aktivieren und den Menüpunkt hinzufügen.</p>

        <table class="si-table si-table-style-allLeft">
          <tbody>
            <tr>
              <td><label class="padding-l-m">Aktivieren</label></td>
              <td>
                <button
                    v-if="active == 1"
                    v-on:click="handlerToggle(data, $event)"
                    class="si-btn si-btn-toggle-on"><i class="fas fa-toggle-on"></i> An</button>
                <button
                    v-else
                    v-on:click="handlerToggle(data, $event)"
                    class="si-btn si-btn-toggle-off"><i class="fas fa-toggle-off"></i> Aus</button>
              </td>
            </tr>
            <tr>
              <td><label class="padding-l-m">Navigation</label></td>
              <td>
                <button class="si-btn si-btn-green" v-if="showAddMenue" v-on:click="handlerAddMenue(data)"><i class="fa fa-plus"></i> Menüpunkt Hinzufügen</button>
                <a class="si-btn" href="index.php?page=administrationmodule&module=AdminMenu"><i class="fa fa-bars"></i> Zur Navigation</a>
              </td>
            </tr>
          </tbody>
        </table>

      </div>
    </div>



  </div>
</template>

<script>



// import Item from '../components/Item.vue'

export default {

  components: {

  },
  data() {
    return {
      open: false,
      showAddMenue: true,
      active: false
    };
  },
  props: {
      data: Object
  },
  created: function () {

  },
  mounted() {
    // access our input using template refs, then focus
  },
  watch: {
    data: function(newVal, oldVal) {
      if (newVal == false) {
        this.open = false;
      } else {
        this.open = true;
        this.active = newVal.active;
      }
    }
  },
  methods: {
    handlerClose: function () {
      this.data = false;
      this.$emit('close');
    },
    /*
    toggleActive: function (active) {
      this.data.active = active;
      this.data.version = false;
    },
    */
    handlerToggle: function () {

      var that = this;

      EventBus.$emit('handlerToggleActive', {
        item: this.data,
        callback: function (active) {
          that.active = active;
        }
      });

    },
    handlerAddMenue: function () {
      EventBus.$emit('handlerAddMenue', {
        item: this.data
      });
      this.showAddMenue = false;
    }

  }


};
</script>

<style>

</style>