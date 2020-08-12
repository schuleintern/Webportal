<template>
  <div class="form flex-9">
    
    
    <form ref="form" @submit="checkForm"
      action="index.php?page=MessageCompose&action=send"
      method="post" enctype="multipart/form-data" id="composeForm">

      <input type="hidden" name="recipients" v-model="messageRecipients">
      <input type="hidden" name="ccrecipients" value="">
      <input type="hidden" name="bccrecipients" value="">
      <input type="hidden" name="messageSubject" v-model="messageSubject">
      <input type="hidden" name="priority" v-model="priority" value="">
      <input type="hidden" name="attachments" v-model="messageAttachments">
      <input type="hidden" name="questions" value="">
      <input type="hidden" name="readConfirmation" value="" v-model="readConfirmation">
      <input type="hidden" name="forwardMessage" value="">
      <input type="hidden" name="replyMessage" value="">
      <input type="hidden" name="replyAllMessage" value="">
      <input type="hidden" name="dontAllowAnser" v-model="dontAllowAnser" value="">
      <textarea class="hidden" name="messageText" v-model="messageText"></textarea>
    </form>

    <div class="bar margin-b-l">
      <button @click="clickHandlerCloseForm()" class="btn btn-grau margin-r-xs"><i class="fa fa-arrow-left"></i></button>
      <button @click="clickHandlerClearForm()" class="btn btn-grau margin-r-xs">Abbrechen</button>
      <button @click="clickHandlerSubmitForm()" class="btn btn-blau margin-r-xs"><i class="fa fa-paper-plane"></i>Senden</button>
      
    </div>

    <ul class="margin-b-m">
      <li class="flex-row margin-b-s">
        <label class="flex-1">Empfänger:</label>
        <div class="flex-6 flex-row">
          <FormRecipient v-show="openRecipients" type="recipients"></FormRecipient>
          <button @click="clickHandlerRecipients()" v-show="!openRecipients" class="btn btn-grau margin-r-s" ><i class="fa fa-plus"></i></button>
          <ul v-show="!openRecipients">
            <li v-bind:key="index" v-for="(item, index) in messageRecipientsArray">{{item.name}}</li>
          </ul>
        </div>
      </li>
      <li class="flex-row margin-b-s">
        <label class="flex-1">Kopieempfänger cc:</label>
        <div class="flex-6 flex-row">
          <FormRecipient v-show="openCcRecipients" type="ccrecipients"></FormRecipient>
          <button @click="clickHandlerCcRecipients()" v-show="!openCcRecipients" class="btn btn-grau margin-r-s" ><i class="fa fa-plus"></i></button>
          <ul v-show="!openCcRecipients">
            <li v-bind:key="index" v-for="(item, index) in messageCcRecipientsArray">{{item.name}}</li>
          </ul>
        </div>
      </li>
      <li class="flex-row margin-b-s">
        <label class="flex-1">Verdeckte Kopieempfänger bcc:</label>
        <div class="flex-6 flex-row">
          <FormRecipient v-show="openBccRecipients" type="bccrecipients"></FormRecipient>
          <button @click="clickHandlerBccRecipients()" v-show="!openBccRecipients" class="btn btn-grau margin-r-s" ><i class="fa fa-plus"></i></button>
          <ul v-show="!openBccRecipients">
            <li v-bind:key="index" v-for="(item, index) in messageBccRecipientsArray">{{item.name}}</li>
          </ul>
        </div>
      </li>
    </ul>

    <ul>
      <li class="flex-row margin-b-m">
        <label class="flex-1">Betreff:</label>
        <input class="flex-6" type="text" v-model="messageSubject" />
      </li>
      <li class="flex-row">
        <label class="flex-1">Nachricht:</label>
        <textarea class="flex-6" v-model="messageText"></textarea>
      </li>
    </ul>


    <ul>
      <li class="flex-row" >
        <label class="flex-1">Dateianhänge</label>
        <div class="flex-6">
          <ul>
            <li v-bind:key="index" v-for="(item, index) in filesAttachment">
              <a v-bind:href="item.attachmentURL" target="_blank">{{item.attachmentFileName}}</a>
              <button v-on:click="deleteFileUpload(item)">Delete</button>
            </li>
          </ul>
          <input type="file" name="attachmentFile" ref="files" v-on:change="handleFileUpload()" />
          <button v-on:click="submitFileUpload()">Datenanhang hochladen</button>
          <p class="help-block">Maximal 10 MB pro Datei. (Office Dokumente, PDF Dateien, ZIP Dateien und Bilder)</p>
        </div>        
      </li>
      <li class="flex-row" v-show="acl.canAskQuestions" >
        <label class="flex-1">Datenabfragen</label>
        <div class="flex-6">
          
        </div>        
      </li>
      <li class="flex-row" v-show="acl.canRequestReadConfirmation" >
        <label class="flex-1">Lesebestätigung anfordern</label>
        <div class="flex-6">
          <input type="checkbox" value="1" v-model="readConfirmation">   
        </div>        
      </li>
      <li class="flex-row">
        <label class="flex-1">Antworten nicht erlauben?</label>
        <div class="flex-6">
          <input type="checkbox" value="1" class="" v-model="dontAllowAnser">  
        </div>        
      </li>
      <li class="flex-row">
        <label class="flex-1">Priorität</label>
        <div class="flex-6">
          <select v-model="priority" class="">
            <option value="low">Niedrige Priorität</option>
            <option value="normal" selected>Normale Priorität</option>
            <option value="high">Hohe Priorität</option>
          </select>  
        </div>        
      </li>
    </ul>
    

  </div>
</template>

<script>

import FormRecipient from './FormRecipient.vue'

const axios = require('axios').default;

export default {
  name: 'Form',
  components: {
    FormRecipient
  },
  props: {
    //messages: Array
  },
  data: function () {
    return {

      // Message:

      messageText: '',
      messageSubject: '',
      
      messageAttachments: '',

      dontAllowAnser: false,
      readConfirmation: false,

      openRecipients: false,
      messageRecipientsArray: [],
      messageRecipients: '',

      openCcRecipients: false,
      messageCcRecipientsArray: [],
      messageCcRecipients: '',

      openBccRecipients: false,
      messageBccRecipientsArray: [],
      messageBccRecipients: '',

      // System:

      acl: {},
      filesUpload: [],
      filesAttachment: []

    }
  },
  computed: {
    

  },
  
  created: function () {

    this.acl = globals.acl;

    EventBus.$on('message--form--set-recipient', data => {

      if (data.type == 'recipients') {
        this.messageRecipients = data.recipientsString;
        this.messageRecipientsArray = data.recipientsArray;
        this.openRecipients = false;

      } else if (data.type == 'ccrecipients') {
        this.messageCcRecipients = data.recipientsString;
        this.messageCcRecipientsArray = data.recipientsArray;
        this.openCcRecipients = false;

      } else if (data.type == 'bccrecipients') {
        this.messageBccRecipients = data.recipientsString;
        this.messageBccRecipientsArray = data.recipientsArray;
        this.openBccRecipients = false;
      }

    });



  },
  methods: {

    checkForm: function ($event) {

      $event.preventDefault();

    },
    clickHandlerSubmitForm : function(){
      this.$refs.form.submit()
    },

    clickHandlerCloseForm: function () {

      EventBus.$emit('message--form--close', {
      })

    },

    clickHandlerClearForm: function () {

      this.messageText = '';
      this.messageSubject = '';
      
      this.messageAttachments = '';

      this.dontAllowAnser = 0;
      this.readConfirmation = 0;

      this.openRecipients = false;
      this.messageRecipientsArray = [];
      this.messageRecipients = '';

      this.openCcRecipients = false;
      this.messageCcRecipientsArray = [];
      this.messageCcRecipients = '';

      this.openBccRecipients = false;
      this.messageBccRecipientsArray = [];
      this.messageBccRecipients = '';

      this.clickHandlerCloseForm();


    },



    clickHandlerRecipients: function () {
      this.openRecipients = true;
    },
    clickHandlerCcRecipients: function () {
      this.openCcRecipients = true;
    },
    clickHandlerBccRecipients: function () {
      this.openBccRecipients = true;
    },

    handleFileUpload: function () {
      this.filesUpload = this.$refs.files.files[0];
    },
    clearFileUpload: function () {
      this.$refs.files.value = '';
      this.filesUpload = '';
    },
    deleteFileUpload: function (item) {

      for(var i = 0; i < this.filesAttachment.length; i++) {

        if (this.filesAttachment[i]['attachmentID'] == item['attachmentID']) {
          this.filesAttachment.splice(i, 1);
        }
      }
      this.clearFileUpload();


    },
    submitFileUpload: function () {

      var that = this;
      let formData = new FormData();
      formData.append('attachmentFile', this.filesUpload );

      axios.post( 'index.php?page=MessageCompose&action=uploadAttachment',
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }
      ).then(function(response){
        //console.log('SUCCESS!!', response);

        if ( response.data ) {

          if ( response.data.uploadOK == true ) {
            that.filesAttachment.push(response.data);
            that.updateAttachmentFields();
          } else {
            console.error('fehler');
          }

        }
      })
      .catch(function(){
        console.log('FAILURE!!');
      })
      .finally(function () {
        // always executed
        that.clearFileUpload();
      }); 


    },
    updateAttachmentFields: function () {
      var fieldValue = [];
      for(var i = 0; i < this.filesAttachment.length; i++) {
        fieldValue.push(this.filesAttachment[i]['attachmentID'] + "#" + this.filesAttachment[i]['attachmentAccessCode']);
      }
      this.messageAttachments = fieldValue.join(";");
    }
 

  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>


</style>
