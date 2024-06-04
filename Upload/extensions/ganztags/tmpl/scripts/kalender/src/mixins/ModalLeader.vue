<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box">
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content si-form">

          <ul class="">
            <li>
              <label>Teammitglied:</label>
            </li>
            <li >
              <button v-bind:key="j" v-for="(item, j) in leaders" v-if="!used.includes(item.id)" class="si-btn margin-r-m" :class="{'si-btn-off' : item.days[day] != true, 'si-btn-green' : preselect == item.id  }" v-on:click="handlerLeader(item)">{{ item.user.name }}</button>
            </li>
          </ul>
        </div>

    </div>

  </div>
</template>

<script>


//import Item from '../components/Item.vue'


export default {

  components: {},
  data() {
    return {
      open: false,
      callback: false,
      preselect: false,
      day: false,
      form: {
        user: false
      },
      used: []
    };
  },
  props: {

    leaders: Array
  },
  created: function () {
    var that = this;
    EventBus.$on('modal-leader--open', data => {

      if (data.callback) {
        that.callback = data.callback;
      }
      if (data.day) {
        that.day = data.day.toLowerCase();
      }
      if (data.preselect) {
        that.preselect = data.preselect;
      }

      that.used = [];
      if (data.content && data.type == 'group') {
        data.content.forEach((o) => {
          if (o.type == 'day-group' && o.leader_id > 0) {
            that.used.push(o.leader_id);
          }
        });
      }

      that.open = true;
    });
    EventBus.$on('modal-leader--close', data => {
      that.open = false;
      that.form = {
        user: false
      };
    });
  },
  methods: {
    handlerClose: function () {
      EventBus.$emit('modal-leader--close');
    },

    handlerLeader: function (item) {
      this.form.leader = item;
      this.handlerSelect();
    },
    handlerSelect: function () {

      if (this.form.leader) {
        if (this.callback && typeof this.callback == 'function') {
          this.callback(this.form);
        }
      }


    }
  }


};
</script>

<style>

</style>