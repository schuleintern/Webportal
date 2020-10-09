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
            <label>Title</label>
            <input type="text" v-model="form.title" />
          </li>
          <li>
            <label>Preis Normal</label>
            <input type="text" v-model="form.preis_default" />
          </li>
          <li>
            <label>Preis Schüler</label>
            <input type="text" v-model="form.preis_schueler" />
          </li>
          <li>
            <label>Vegetarisch</label>
            <input type="checkbox" v-model="form.vegetarisch" true-value="1" false-value="0" />
          </li>
          <li>
            <label>Vegan</label>
            <input type="checkbox" v-model="form.vegan" true-value="1" false-value="0" />
          </li>
          <li>
            <label>Laktosefrei</label>
            <input type="checkbox" v-model="form.laktosefrei" true-value="1" false-value="0" />
          </li>
          <li>
            <label>Glutenfrei</label>
            <input type="checkbox" v-model="form.glutenfrei" true-value="1" false-value="0" />
          </li>
          <li>
            <label>Bio</label>
            <input type="checkbox" v-model="form.bio" true-value="1" false-value="0" />
          </li>
          <li>
            <label>Regional</label>
            <input type="checkbox" v-model="form.regional" true-value="1" false-value="0" />
          </li>
          <li>
            <label>Beschreibung</label>
            <textarea v-model="descClean"></textarea>
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
    dates: Array
  },
  data(){
    return {
      modalActive: false,

      error: false,

      form: {
        date: false,
        preis_schueler: '',
        preis_default: '',
        title: '',
        desc: '',
        vegetarisch: false,
        vegan: false,
        laktosefrei: false,
        glutenfrei: false,
        bio: false,
        regional: false
      }

    }
  },
  created: function () {

    var that = this;

    EventBus.$on('form--open', data => {
      if (data.item.date) {
        that.form = data.item;
        that.modalActive = true;
      }
    });

    EventBus.$on('form--close', data => {
      that.handlerCloseModal();
    });

  },
  computed: {
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
  },
  methods: {

    handlerCloseModal: function () {
      this.modalActive = false;
    },

    submitForm: function () {
      var that = this;
      this.form.date = this.$date(this.form.date).format('YYYY-MM-DD')
      EventBus.$emit('form--submit', {
        form: that.form
      });
    }

  }
}
</script>
