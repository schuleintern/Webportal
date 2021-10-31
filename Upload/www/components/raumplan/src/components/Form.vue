<template>

  <div class="form-modal " v-on:click.self="handlerCloseModal" v-show="modalActive" >
      <div class="form form-style-2 form-modal-content">
        
        <div class="form-modal-close"v-on:click="handlerCloseModal"><i class="fa fa-times"></i></div>
        
        <div v-show="error" class="form-modal-error">
          <b>Folgende Fehler sind aufgetreten:</b>
          <ul>
            <li>{{ error }}</li>
          </ul>
        </div>
        
        <br />

        <ul class="noListStyle">
          <li>
            <label>Tag</label>
            <strong>{{ $date(form.date).format("DD.MM.YYYY") }}</strong>
          </li>
          <li>
            <label>Stunde</label>
            {{form.stunde}}
          </li>
          <li>
            <label>Raum</label>
            {{form.room}}
          </li>
          <li>
            <label>Klasse</label>
            <input type="text" v-model="form.klasse" />
          </li>
          <li>
            <label>Lehrer</label>
            <input type="text" v-model="form.lehrer" />
          </li>
          <li>
            <label>Fach</label>
            <input type="text" v-model="form.fach" />
          </li>

        </ul>
        <br/>
        <button @click="submitForm" class="btn width-100p"><i class="fa fa-save"></i> Speichern</button>
      </div>
    </div>

</template>


<script>

export default {
  name: 'Form',
  props: {
    dates: Array,
    room: String
  },
  data(){
    return {
      modalActive: false,

      error: false,

      form: {
        date: false,
        stunde: '',
        room: '',

        klasse: '',
        lehrer: '',
        fach: ''

      }

    }
  },
  created: function () {



    var that = this;

    EventBus.$on('form--open', data => {
      if (data.item.date && data.item.stunde) {
        that.form = data.item;
        this.form.room = this.room;
        that.modalActive = true;
      }
    });

    EventBus.$on('form--close', data => {
      that.handlerCloseModal();
    });

  },
  /*computed: {
    descClean: {
      get() {
        if (this.form.desc) {
          return this.form.desc.replaceAll('<br />','');
        }
        return '';
      },
      set (desc) {
        this.form.desc = desc;
        return desc
      }
    }
  },*/
  methods: {

    handlerCloseModal: function () {
      this.modalActive = false;
    },

    submitForm: function () {
      var that = this;
      this.form.date = this.$date(this.form.date).format('YYYY-MM-DD')
      this.form.room = this.room;
      EventBus.$emit('form--submit', {
        form: that.form
      });
    }

  }
}
</script>
