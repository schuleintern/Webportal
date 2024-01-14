<template>


  <div class="width-30vw item">

    <User v-if="data.user" v-bind:data="data.user" ></User>

    <div class="si-form">
      <ul>
        <li>
          <label>Einheiten</label>
          <input type="text" v-model="data.einheiten" readonly />
        </li>
        <li v-bind:key="index" v-for="(item, index) in dates" >
          <h4>{{index+1}}. Sitzung</h4>
          <div class="date">
            <label>Datum</label>
            <input v-model="item.date" placeholder="00.00.0000" />
          </div>
          <div>
            <label>Dauer</label>
            <input v-model="item.duration" placeholder="60"/>
          </div>
        </li>
        <li>
          <label>Zusätzliche Informationen</label>
          <textarea v-model="data.info"></textarea>
        </li>
        <li>
          <button class="si-btn" v-on:click="handlerSubmit"><i class="fas fa-plus-circle"></i> Slot bestätigen & Schließen</button>
        </li>
      </ul>
    </div>
  </div>


</template>

<script>

import User from '../mixins/User.vue'
export default {
  components: {
    User
  },
  data() {
    return {
      dates: []
    };
  },
  props: {
      data: Object
  },
  created: function () {

    this.data.einheiten = parseInt(this.data.einheiten);

    for(let i = 0; i < this.data.einheiten; i++) {
      this.dates[i] = {
        date: '',
        duration: ''
      };
    }
  },
  methods: {
    handlerSubmit: function () {

      var go = true;
      this.dates.forEach((o) => {
        if (o.date == '') {
          go = false;
        }
      })

      if (go == false) {
        const elm = this.$el.querySelectorAll('.date');
        elm.forEach(function (o) {
          o.classList.add('required');
        })
        return false;
      }

      this.data.dates = JSON.stringify(this.dates);

      console.log(this.data.dates);

      this.$emit('formsubmit', 'someValue')

      EventBus.$emit('form-submit', {
        data: this.data,
      });

    }
  }

};
</script>

<style>
.item {
  display: flex;
  flex-direction: column;
}
.item .si-user {
  align-self: flex-end;
}
</style>