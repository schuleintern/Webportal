<template>
  <div class="">

    <div class="flex-row">
      <button class="si-btn si-btn-light margin-r-m" @click="handlerBack()"><i class="fa fa fa-angle-left"></i> Zurück
      </button>
      <button v-if="showSubmit() == true" class="si-btn" @click="handlerSubmit"><i class="fa fa-envelope"></i> Senden
      </button>
    </div>


    <div class="si-form flex-row">
      <ul class="flex-5">

        <li>
          <label>Empfänger</label>
          <InboxSelect :recipients="recipients" :preselect="form.inbox" :cache="cache.inbox"
                       @submit="handlerInboxSubmit"></InboxSelect>
        </li>
        <li>
          <label>CC-Empfänger</label>
          <InboxSelect :recipients="recipients" :preselect="form.inbox_cc" :cache="cache.inbox_cc"
                       @submit="handlerInboxCCSubmit"></InboxSelect>
        </li>
        <li>
          <label>Betreff</label>
          <input v-model="form.subject" required>
        </li>
        <li class="height-50rem">
          <QuillEditor theme="snow" v-model:content="form.text" contentType="html" class=""/>
        </li>
        <li>
          <label>Dateianhänge</label>
          <FormUpload class="" @done="handerUpload" @error="handerUploadError" :target="'rest.php/inbox/uploadItem/'+form.id"></FormUpload>
        </li>

      </ul>

      <ul class="flex-2">
        <li>
          <label>Sender</label>
          <FormSelect :input="form.sender" :options="inboxs" @submit="triggerSender"></FormSelect>
        </li>
        <li>
          <label>Lesebestätigung</label>
          <FormToggle :input="form.confirm" @change="triggerConfirm"></FormToggle>
        </li>
        <li>
          <label>Priorität</label>
          <div class="si-btn-multiple">
            <button class="si-btn si-btn-small" :class="{'si-btn-active': form.priority == 1}"
                    @click="triggerPriority(1)"><i class="fa fa-arrow-down "></i> Niedrig
            </button>
            <button class="si-btn si-btn-small" :class="{'si-btn-active': form.priority == 0}"
                    @click="triggerPriority(0)"><i class="fa fa-arrow-right"></i> Normal
            </button>
            <button class="si-btn si-btn-small" :class="{'si-btn-active': form.priority == 2}"
                    @click="triggerPriority(2)"><i class="fa fa-arrow-up"></i> Hoch
            </button>
          </div>
        </li>
        <li>
          <label>Antworten nicht erlauben?</label>
          <FormToggle :input="form.noAnswer" @change="triggerNoAnswer"></FormToggle>
        </li>
        <!--
        <li>
          <label>Inhalt der Nachricht ist vertraulich?</label>
          <FormToggle :input="form.isPrivat" @change="triggerIsPrivat"></FormToggle>
        </li>-->




      </ul>


    </div>


  </div>

</template>

<script>

import InboxSelect from '../mixins/InboxSelect.vue'
import FormToggle from '../mixins/FormToggle.vue'
import FormSelect from '../mixins/FormSelect.vue'
import FormUpload from '../mixins/FormUpload.vue'


export default {
  name: 'FormComponent',
  components: {
    FormUpload,
    InboxSelect, FormToggle, FormSelect
  },
  data() {
    return {
      form: {},
      cache: {},

    };
  },
  props: {
    acl: Array,
    inbox: [],
    inboxs: Array,
    recipients: Array,
    answerToMsg: Object
  },
  created: function () {


    if (this.inbox && this.inbox.id) {
      this.form.sender = this.inbox.id;
      //this.triggerSender(this.inbox.id)
    }
    //console.log(this.answerToMsg);

    if (this.answerToMsg && this.answerToMsg.id) {
      this.form.answer_id = this.answerToMsg.id;

      this.form.text = '\n\n\n<b>Am ' + this.answerToMsg.date + ' schrieb ' + this.answerToMsg.from.title + ':</b>\n' + this.answerToMsg.text;

      // Answer to:
      if (this.answerToMsg.props && this.answerToMsg.props.answer) {
        if (this.answerToMsg.from && this.answerToMsg.from.str) {
          this.form.inbox = this.answerToMsg.from.str;
          this.cache.inbox = JSON.parse(this.answerToMsg.from.strLong);
        }
        this.form.subject = 'Re: ' + this.answerToMsg.subject;
      }

      // Answer to All:
      else if (this.answerToMsg.props && this.answerToMsg.props.answerAll) {

        if (this.answerToMsg.to) {
          let item = this.answerGetInbox(this.answerToMsg.to, this.answerToMsg, true);
          this.form.inbox = JSON.stringify(item[0]);
          this.cache.inbox = item[1];
        }

        if (this.answerToMsg.toCC) {
          let item = this.answerGetInbox(this.answerToMsg.toCC, this.answerToMsg);
          this.form.inbox_cc = JSON.stringify(item[0]);
          this.cache.inbox_cc = item[1];
        }
        this.form.subject = 'Re: ' + this.answerToMsg.subject;

      }

      if (this.answerToMsg.props && this.answerToMsg.props.forward) {
        this.form.subject = 'Fw: ' + this.answerToMsg.subject;
      }


      //console.log(this.answerToMsg)

    }


  },
  methods: {

    handerUploadError(error, response) {
      if (response.error && response.msg) {
        this.$bus.$emit('error', {
          msg: response.msg
        });
      }
    },
    handerUpload(rootFile, response) {
      if (!this.form.files) {
        this.form.files = [];
      }
      if (response.success) {
        this.form.files.push({
          "name": rootFile,
          "path": response.path + response.filename
        });
      }
    },
    answerGetInbox: function (recipient, msgObj, withFrom) {

      var str = [];
      var strLong = [];

      recipient.forEach((o) => {
        if (o.inbox && o.inbox.str && o.inbox.strLong) {
          let strObj = JSON.parse(o.inbox.str);
          if (strObj) {
            str = str.concat(strObj);
          }
          let strLongObj = JSON.parse(o.inbox.strLong);
          if (strLongObj) {
            strLong = strLong.concat(strLongObj);
          }
        }
      });

      if (withFrom == true) {
        // add from
        str = str.concat(JSON.parse(msgObj.from.str));
        strLong = strLong.concat(JSON.parse(msgObj.from.strLong));
      }

      // filter self inbox
      var fromInbox = [];
      var cacheInbox = [];

      str.forEach((s) => {
        if (parseInt(s.content) !== parseInt(msgObj.inbox_id)) {
          fromInbox.push(s);
        }
      });
      strLong.forEach((s) => {
        if (parseInt(s.content) !== parseInt(msgObj.inbox_id)) {
          cacheInbox.push(s);
        }
      });

      return [fromInbox, cacheInbox];
    },

    triggerPriority(val) {
      this.form.priority = val;
    },
    triggerSender(val) {
      this.form.sender = val.value;
    },
    triggerConfirm(val) {
      this.form.confirm = val.value;
    },
    triggerNoAnswer(val) {
      this.form.noAnswer = val.value;
    },
    triggerIsPrivat(val) {
      this.form.isPrivat = val.value;
    },
    handlerInboxSubmit(input, cached) {

      //console.log('----handlerInboxSubmit', input, cached);

      if (!input) {
        return false;
      }
      this.form.inbox = JSON.stringify(input);
      this.cache.inbox = cached;
    },
    handlerInboxCCSubmit(input, cached) {

      //console.log('----handlerInboxCCSubmit');

      if (!input) {
        return false;
      }
      this.form.inbox_cc = JSON.stringify(input);
      this.cache.inbox_cc = cached;
    },

    showSubmit() {

      //console.log(this.form);


      if (!this.form.inbox) {
        return false;
      }
      if (!this.form.sender) {
        return false;
      }

      return true;

    },
    handlerSubmit() {
      this.$bus.$emit('message--submit', {
        form: this.form
      });
    },
    handlerBack() {
      this.$bus.$emit('page--open', {
        page: 'list'
      });
    },


  }


};
</script>

<style>

.ql-container {
  font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif !important;
  font-size: 14px !important;
  font-weight: 300;
  line-height: 14pt;
  letter-spacing: 0.75pt;
  border-bottom-left-radius: 3rem;
  border-bottom-right-radius: 3rem;
  background-color: #fff;
}

.ql-toolbar {
  border-top-left-radius: 3rem;
  border-top-right-radius: 3rem;
}

.ql-editor li {
  display: block !important;
}

</style>