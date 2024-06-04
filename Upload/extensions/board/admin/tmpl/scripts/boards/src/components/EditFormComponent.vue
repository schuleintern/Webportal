<template>
  <div class="">

    <div class="flex-row flex-space-between">
      <div class="">
        <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück
        </button>
        <button class="si-btn" @click="handlerSubmit"><i class="fa fa-save"></i> Speichern</button>
      </div>
      <div>
        <button v-if="form.id" class="si-btn si-btn-red" v-on:click="handlerDelete"><i class="fas fa-trash"></i> Löschen
        </button>
      </div>
    </div>
    <div class="width-70vw">
      <div class="si-form flex-row">
        <div class="flex-5">
          <ul class="">
            <li>
              <label>Title</label>
              <input type="text" v-model="form.title">
            </li>
            <li>
              <label>Text</label>
              <textarea v-model="form.text"></textarea>
            </li>
            <li>
              <label>Bild</label>
              <img v-if="form.cover && form.coverURL" :src="form.coverURL" width="200" class="margin-b-m"/>
              <div class="">
                <FormUpload class="blockInline" @fileSuccess="handerUploadCover" :target="apiURL+'/uploadItem/cover/'+form.id"></FormUpload>
                <button v-if="form.cover" class="blockInline margin-l-m si-btn si-btn-icon si-btn-border" @click="handlerClearCover"><i
                    class="fa fa-trash"></i></button>
              </div>
            </li>
            <li>
              <label>PDF</label>
              <span v-if="form.pdf" class="text-grey padding-l-l margin-b-s">{{form.pdf}}</span>
              <div class="">
                <FormUpload class="blockInline" @fileSuccess="handerUploadPdf" :target="apiURL+'/uploadItem/pdf/'+form.id"></FormUpload>
                <button v-if="form.pdf" class="blockInline margin-l-m si-btn si-btn-icon si-btn-border" @click="handlerClearPdf"><i
                    class="fa fa-trash"></i></button>
              </div>

            </li>

          </ul>
        </div>
        <div class="flex-2">
          <ul class="">
            <li>
              <label>Status</label>
              <FormToggle :input="form.state" @change="handlerToggleState"></FormToggle>
            </li>
            <li>
              <label>Ablaufdatum</label>
              <VueDatePicker :format="format" model-type="yyyy-MM-dd" v-model="form.enddate"
                             :enableTimePicker="false" locale="de" cancel-text="Abbrechen"
                             select-text="Ok" :monthChangeOnScroll="false"></VueDatePicker>
            </li>

          </ul>
        </div>


      </div>
    </div>

  </div>

</template>

<script>

import FormToggle from '../mixins/FormToggle.vue'
import FormUpload from '../mixins/FormUpload.vue'

import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

export default {
  name: 'EditFormComponent',
  components: {
    FormUpload,
    FormToggle,
    VueDatePicker
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      form: {}

    };
  },
  setup() {
    const format = (val) => {
      return `${val.getDate()}.${val.getMonth() + 1}.${val.getFullYear()}`
    }


    return {
      format
    }
  },
  props: {
    acl: Array,
    board: Array,
    edit: Array
  },
  created: function () {
    this.form = this.edit;
  },
  methods: {
    handerUploadPdf(rootFile, response) {
      if (response.success) {
        this.form.pdf = response.path + response.filename;
      }
    },
    handlerClearPdf() {
      this.form.pdf = '';
    },
    handerUploadCover(rootFile, response) {
      if (response.success) {
        this.form.cover = response.path + response.filename;
      }
    },
    handlerClearCover() {
      this.form.cover = '';
    },
    handlerToggleState: function (data) {
      this.form.state = data.value;
      //console.log(val)
    },
    handlerSubmit: function () {
      this.form.board_id = this.board.id;
      this.$bus.$emit('editform--submit', this.form);
    },
    handlerBack: function () {
      this.$bus.$emit('page--open', {
        page: 'edit',
        item: this.board
      });
    },
    handlerDelete: function () {
      if (!this.form.id) {
        return false;
      }
      if (confirm('Wirklich löschen?')) {
        this.$bus.$emit('editform--delete', {
          item: this.form
        });
      }

    }


  }


};
</script>


<style>

.dp__theme_light {
  --dp-background-color: #ffffff;
  --dp-text-color: #212121;
  --dp-hover-color: #f3f3f3;
  --dp-hover-text-color: #212121;
  --dp-hover-icon-color: #959595;
  --dp-primary-color: #3c8dbc;
  --dp-primary-text-color: #f8f5f5;
  --dp-secondary-color: #c0c4cc;
  --dp-border-color: #ddd;
  --dp-menu-border-color: #ddd;
  --dp-border-color-hover: #aaaeb7;
  --dp-disabled-color: #f6f6f6;
  --dp-scroll-bar-background: #f3f3f3;
  --dp-scroll-bar-color: #959595;
  --dp-success-color: #018d4e;
  --dp-success-color-disabled: #a3d9b1;
  --dp-icon-color: #959595;
  --dp-danger-color: #dd4b39;
}

.dp__action.dp__cancel,
.dp__action.dp__select {
  box-shadow: rgba(0, 0, 0, 0.1) 0px 10px 15px -3px, rgba(0, 0, 0, 0.05) 0px 4px 6px -2px;
  border-radius: 3rem;

  display: inline-block;

  padding: 1rem 1.6rem;
  margin-bottom: 0.3rem;
  margin-top: 0.3rem;

  font-size: 11pt;
  font-weight: 300;
  line-height: 100%;
  letter-spacing: 0.75pt;
  text-align: center;


  vertical-align: middle;
  -ms-touch-action: manipulation;
  touch-action: manipulation;
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  background-image: none;

  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  background-color: #3c8dbc;
  border: 1px solid #3c8dbc;
  color: #fff;
}

.dp__action.dp__cancel {
  background-color: #b7c7ce;
  border-color: #b7c7ce;
  color: #fff;
  margin-right: 1rem;
}

.dp__action_row {
  flex-direction: column;
}

.dp__selection_preview,
.dp__action_buttons {
  flex: 1;
  width: 100% !important;
}

.dp__menu {
  font-size: inherit;
}

.dp__selection_preview {
  font-size: 1rem !important;
  padding-bottom: 0.3rem;
  display: flex;
  justify-content: space-around;
}

.dp__input_icons {
  width: 1.5rem;
  height: 1.5rem;
  margin-left: 1rem;
  margin-right: 0.3rem;
}

.dp__input {
  padding-left: 5rem !important;
}
</style>