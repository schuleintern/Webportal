<template>

  <div class="box">
    <AjaxError v-bind:error="error"></AjaxError>
    <AjaxSpinner v-bind:loading="loading"></AjaxSpinner>

    <div v-if="page === 'list'" class="">

      <div class="si-btn-multiple margin-b-m">
        <button v-bind:key="index" v-for="(item, index) in  inboxs" @click="handlerClickInbox(item)"
                class="si-btn si-btn-light" :class="{'si-btn-toggle-on': item == this.inbox}">
          {{ item.title }}
          <span v-if="item.unread" class="margin-l-s label bg-white text-grey">{{item.unread}}</span>

        </button>
        <button v-if="acl.write == 1" class="si-btn" @click="handlerForm()"><i class="fa fa-plus"></i> Neue Nachricht
        </button>
      </div>

      <div class="flex-row">
        <div class="bar flex-2 padding-r-m">
          <div class="flex-row">

            <button v-bind:key="index" v-for="(item, index) in  inbox.folders" @click="handlerClickFolder(item)"
                    class="si-btn si-btn-light width-100p" :class="{'si-btn-active': item == this.inbox.activeFolder}"
                    @drop.prevent="handlerDrop($event, item.id)" @dragenter.prevent
                    @dragover.prevent="handlerDropover($event, item.id)"
                    @dragleave.prevent="handlerDropleave($event)">
              <i v-if="item.id == 1" class="fa fa-inbox"></i>
              <i v-else-if="item.id == 2" class="fa fa-envelope"></i>
              <i v-else-if="item.id == 3" class="fa fa-archive"></i>
              <i v-else-if="item.id == 4" class="fa fa-book"></i>
              <i v-else class="fa fa-folder"></i>
              {{ item.title }}
              <span v-if="item.unread" class="margin-l-s label bg-white text-grey">{{item.unread}}</span>
            </button>

          </div>
        </div>

        <div class="main flex-10 padding-l-l" v-if="inbox.activeFolder">

          <ListSendComponent v-if="inbox.activeFolder.id == 2" :acl="acl" :list="messages" :item="message"></ListSendComponent>
          <ListReadComponent v-else :acl="acl" :list="messages" :item="message"></ListReadComponent>
          <ItemComponent :acl="acl" v-if="message" :item="message" :folder="inbox.activeFolder"></ItemComponent>
        </div>
      </div>
    </div>


    <FormComponent v-if="page === 'form'" :acl="acl" :inbox="inbox" :inboxs="inboxs" :answerToMsg="message"
                   :recipients="recipients" ></FormComponent>


  </div>
</template>

<script>

import AjaxError from './mixins/AjaxError.vue'
import AjaxSpinner from './mixins/AjaxSpinner.vue'
import ListReadComponent from './components/ListReadComponent.vue'
import ListSendComponent from './components/ListSendComponent.vue'
import ItemComponent from './components/ItemComponent.vue'
import FormComponent from './components/FormComponent.vue'

const axios = require('axios').default;

export default {
  name: 'App',
  components: {
    AjaxError, AjaxSpinner,
    ListReadComponent, ItemComponent, FormComponent, ListSendComponent
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      acl: window.globals.acl,
      error: false,
      loading: false,
      page: 'list',
      sort: {
        column: 'datumStart',
        order: false
      },
      sortDates: ['datumStart', 'createdTime'],
      searchColumns: ['status', 'info'],
      searchString: '',
      drag: false,
      dropzone: [1],

      inboxs: window.globals.data,
      inbox: false,

      uploadFolder: false,

      messages: false,
      message: false,

      recipients: false,

      selectedInbox: window.globals.inbox_id,
      selectedMessage: window.globals.message_id,

    };
  },
  computed: {},
  created: function () {

    // INIT
    if (this.inboxs && this.selectedInbox) {
      this.inboxs.forEach((o) => {
        if (o.id == this.selectedInbox) {
          this.handlerClickInbox(o);
          this.selectedInbox = false;
        }
      })
    } else if (this.inboxs && this.inboxs[0]) {
      this.handlerClickInbox(this.inboxs[0]);
    }




    this.$bus.$on('page--open', data => {
      if (data.props) {
        data.item.props = data.props;
      }
      this.handlerPage(data.page, data.item);
    });

    this.$bus.$on('message--read', data => {
      if (data.message) {
        this.message = data.message;
        this.loadFullMessage();
      } else {
        this.message = false;
      }
    });

    this.$bus.$on('message--submit', (data) => {

      if (!data.form) {
        return false;
      }
      if (!data.form.inbox) {
        return false;
      }
      if (!data.form.sender) {
        return false;
      }
      if (!data.form.inbox_cc) {
        data.form.inbox_cc = '';
      }

      this.loading = true;
      var that = this;
      const formData = new FormData();
      formData.append('sender', data.form.sender);
      formData.append('receiver', data.form.inbox);
      formData.append('receiver_cc', data.form.inbox_cc);
      formData.append('subject', data.form.subject);
      formData.append('text', data.form.text);
      formData.append('confirm', data.form.confirm);
      formData.append('priority', data.form.priority);
      formData.append('noAnswer', data.form.noAnswer);
      formData.append('isPrivat', data.form.isPrivat);
      formData.append('files', JSON.stringify(data.form.files));


      axios.post(this.apiURL + '/setMessage', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(function (response) {
        if (response.data) {
          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {
            if (response.data.done == true) {
              that.handlerPage('list');
            }
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      }).catch(function () {
        that.error = 'Fehler beim Laden. 02';
      }).finally(function () {
        // always executed
        that.loading = false;
      });
    });

    this.$bus.$on('message-confirm', (data) => {

      if (!data.item) {
        return false;
      }
      if (!data.item.id) {
        return false;
      }

      this.loading = true;
      var that = this;
      const formData = new FormData();
      formData.append('mid', data.item.id);

      axios.post(this.apiURL + '/setConfirm', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(function (response) {
        if (response.data) {
          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {
            that.message.isConfirm = response.data.isConfirm;
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      }).catch(function () {
        that.error = 'Fehler beim Laden. 02';
      }).finally(function () {
        // always executed
        that.loading = false;
      });
    });

    this.$bus.$on('item--delete', (data) => {

      if (!data.item || !data.item.id) {
        return false;
      }

      this.loading = true;
      var that = this;
      const formData = new FormData();
      formData.append('mid', data.item.id);

      axios.post(this.apiURL + '/deleteMessage', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(function (response) {
        if (response.data) {
          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {
            //that.message.isConfirm = response.data.isConfirm;
            //console.log('done')
            let i = that.messages.find((item) => {
              if (item.id == data.item.id) {
                return true;
              }
            });
            that.messages.splice(that.messages.indexOf(i), 1)
            that.message = false;
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      }).catch(function () {
        that.error = 'Fehler beim Laden. 02';
      }).finally(function () {
        // always executed
        that.loading = false;
      });

    });



  },
  methods: {

    handlerForm() {
      this.$bus.$emit('page--open', {
        page: 'form'
      });
    },
    handlerDropleave(evt) {
      //console.log(evt)
      evt.target.classList.remove('si-btn-active-hover');
    },
    handlerDropover(evt, folder_id) {
      //console.log(evt)
      if (folder_id != 2) { // 2 = Gesendet
        evt.target.classList.add('si-btn-active-hover');
      }

    },
    handlerDrop(evt, folder_id) {
      //console.log(evt);

      evt.target.classList.remove('si-btn-active-hover');

      if (folder_id == 2) { // 2 = Gesendet
        return false;
      }

      const itemID = evt.dataTransfer.getData('itemID')
      //const item = this.messages.find((item) => item.id == itemID)

      //console.log(itemID, 'folder',folder_id );

      if (!itemID || !folder_id) {
        return false;
      }
      this.loading = true;
      var that = this;
      const formData = new FormData();
      formData.append('mid', itemID);
      formData.append('fid', folder_id);
      formData.append('iid', this.inbox.id);
      axios.post(this.apiURL + '/setMessageFolder', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(function (response) {
        if (response.data) {
          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {

            if (response.data.done == true) {
              let i = that.messages.find((item) => {
                if (item.id == itemID) {
                  return true;
                }
              });
              that.messages.splice(that.messages.indexOf(i), 1)
              that.message = false;
            }

          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      }).catch(function () {
        that.error = 'Fehler beim Laden. 02';
      }).finally(function () {
        that.loading = false;
      });


    },


    handlerClickFolder(item) {
      if (this.inbox) {
        this.inbox.activeFolder = item;
      }
      this.loadMessages();
      this.handlerPage('list');
    },

    handlerClickInbox(inbox) {
      //console.log(inbox)
      this.inbox = inbox;
      if (this.inbox) {
        if (!this.inbox.activeFolder) {
          if (this.inbox.folders && this.inbox.folders[0]) {
            this.handlerClickFolder(this.inbox.folders[0]);
          }
        } else {
          this.loadMessages();
          this.handlerPage('list');
        }
      }
    },

    handlerPage(page = 'list', item = false) {
      this.message = false;
      var pageWrapper = document.querySelector('#pageWrapper')
      if (pageWrapper) {
        pageWrapper.scrollTo(0, 0);
      }
      if (page == 'form') {
        if (!this.recipients) {
          this.loadRecipients();
        }
        //this.loadUploadFolder();
        //console.log(item);
        if (item) {
          this.message = item;
        }
      }
      this.page = page;
    },
/*
    loadUploadFolder() {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getUploadFolder').then(function (response) {
        if (response.data) {
          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {
            that.uploadFolder = response.data[0];
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      }).catch(function () {
        that.error = 'Fehler beim Laden. 02';
      }).finally(function () {
        that.loading = false;
      });

    },

 */
    loadRecipients() {

      this.loading = true;
      var that = this;
      axios.get(this.apiURL + '/getRecipients').then(function (response) {
        if (response.data) {
          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {
            that.recipients = response.data;
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      }).catch(function () {
        that.error = 'Fehler beim Laden. 02';
      }).finally(function () {
        that.loading = false;
      });

    },
    loadFullMessage() {

      if (!this.message.id) {
        return false;
      }
      this.loading = true;
      var that = this;
      const formData = new FormData();
      formData.append('message_id', this.message.id);
      axios.post(this.apiURL + '/getMessage', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(function (response) {
        if (response.data) {
          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {
            that.message = response.data;


            that.messages.forEach((o) => {
              if (o.id == that.message.id) {
                o.isRead = that.message.isRead;
              }
            });


          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      }).catch(function () {
        that.error = 'Fehler beim Laden. 02';
      }).finally(function () {
        that.loading = false;
      });

    },
    loadMessages() {

      if (!this.inbox.id) {
        return false;
      }
      if (!this.inbox.activeFolder || !this.inbox.activeFolder.id) {
        return false;
      }
      this.loading = true;
      var that = this;
      const formData = new FormData();
      formData.append('inbox_id', this.inbox.id);
      formData.append('folder_id', this.inbox.activeFolder.id);
      axios.post(this.apiURL + '/getMessages', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(function (response) {
        if (response.data) {
          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {
            that.messages = response.data;

            if (that.inboxs && that.selectedMessage) {
              that.messages.forEach((o) => {
                if (o.id == that.selectedMessage) {
                  that.$bus.$emit('message--read', {
                    message: o
                  });
                  that.selectedMessage = false;
                }
              });
            }

          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      }).catch(function () {
        that.error = 'Fehler beim Laden. 02';
      }).finally(function () {
        // always executed
        that.loading = false;
      });
    },

  }
}
</script>

<style>

</style>
