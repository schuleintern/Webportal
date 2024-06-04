<template>

  <div class="">


    <button v-if="!state" class="si-btn si-btn-green" v-on:click="handlerOpenForm">Open</button>

    <div v-if="state == 'done'">
      <button class="si-btn" v-on:click="handlerOpenForm">Change ({{ selectedUserList.length }})</button>
      <div>
        <div v-bind:key="index" v-for="(item, index) in  selectedUserList" class="blockInline margin-r-m">
          <span v-if="item.user">{{ item.user.name }}</span>
          <span v-else-if="item.title">{{ item.title }}</span>
        </div>
      </div>
    </div>
    <div v-if="state == 'form'">
      <div class="si-modalblack" v-on:click.self="handlerCloseForm">
        <div class="si-modalblack-box">
          <div class="si-modalblack-content flex">

            <div class="si-btn-multiple">
              <button class="si-btn" :class="{'si-btn-active':openTab=='pupils'}" @click="handlerOpenTab('pupils')">
                Schüler*innen
              </button>
              <button class="si-btn" :class="{'si-btn-active':openTab=='parents'}" @click="handlerOpenTab('parents')">
                Eltern
              </button>
              <button class="si-btn" :class="{'si-btn-active':openTab=='teacher'}" @click="handlerOpenTab('teacher')">
                Lehrer*innen
              </button>
              <button class="si-btn" :class="{'si-btn-active':openTab=='shool'}" @click="handlerOpenTab('shool')">
                Verwaltung
              </button>
              <button class="si-btn" :class="{'si-btn-active':openTab=='groups'}" @click="handlerOpenTab('groups')">
                Gruppen
              </button>
            </div>

            <div class="flex-row">
              <div class="tabs flex-4">
                <div v-if="openTab == 'pupils'" class="tab flex-row">

                  <div class="flex-1" v-if="recipients.jahrgang">
                    <h3>Klassen</h3>
                    <span v-bind:key="index" v-for="(item, index) in  recipients.jahrgang">
                      <BtnKlasse typ="pupils::klasse" :content="item" :selected="selected"></BtnKlasse>
                    </span>
                  </div>

                  <div class="flex-1">
                    <h3>Schüler*in</h3>
                    <input type="text" v-model="searchString" v-on:keyup="handlerChangeSearch" placeholder="Max 5a"/>
                    <div class="list">
                      <button v-bind:key="index" v-for="(item, index) in  searchUserlist" class="si-btn"
                              :class="{'si-btn-active': selectActive('user', item.id) }"
                              @click="handlerSelect('user', item.id)">{{ item.name }}
                      </button>
                    </div>
                  </div>

                </div>

                <div v-if="openTab == 'parents'" class="tab flex-row">

                  <div class="flex-1" v-if="recipients.jahrgang">
                    <h3>Klassen</h3>
                    <span v-bind:key="index" v-for="(item, index) in  recipients.jahrgang">
                      <BtnKlasse typ="parents::klasse" :content="item" :selected="selected"></BtnKlasse>
                    </span>
                  </div>
                  <div v-else class="flex-1">- kein Postfach vorhanden -</div>

                  <div class="flex-1">
                    <h3>Eltern</h3>

                  </div>

                </div>
                <div v-if="openTab == 'teacher'" class="tab">
                  Contant tab 3
                </div>
                <div v-if="openTab == 'shool'" class="tab">

                  <h3>Verwaltung</h3>

                  <div v-if="recipients.group">
                    <button v-bind:key="index" v-for="(item, index) in  recipients.group" class="si-btn"
                            :class="{'si-btn-active': selectActive('group', item.id) }"
                            @click="handlerSelect('group', item.id)">{{ item.title }}
                    </button>
                  </div>
                  <div v-else>- kein Postfach vorhanden -</div>


                </div>
                <div v-if="openTab == 'groups'" class="tab">
                  Contant tab 5
                </div>
              </div>

              <div class="selected flex-1 text-grey">


                {{ selectedUserList.length }}
                <div v-bind:key="index" v-for="(item, index) in  selectedUserList" class="">
                  <div class="si-user">{{ item.id }}
                    <span v-if="item.user">{{ item.user.name }}</span>
                    <span v-else-if="item.title">{{ item.title }}</span>
                  </div>
                </div>

              </div>
            </div>

            <div>
              <button class="si-btn si-btn-green" @click="handlerSubmit"><i class="fa fa-plus"></i> Ok</button>
            </div>
            {{ selected }}

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

import BtnKlasse from "./BtnKlasse";

const axios = require('axios').default;

export default {
  components: {
    BtnKlasse
  },
  data() {
    return {
      apiURL: window.globals.apiURL,
      state: false,
      openTab: false,
      selected: [],

      searchString: '',
      searchUserlist: []

    };
  },
  props: {
    recipients: Array,
    preselect: Array
  },
  computed: {
    selectedUserList: function () {



      let ret = [];
      this.selected.forEach((o) => {
        if (o.inboxs) {
          //ret = [...ret,...o.userlist]
          //ret = ret.concat(o.userlist)
          o.inboxs.forEach((user) => {

            // Doppelte vermeiden
            var found = false;
            ret.forEach((m) => {
              if (m.id == user.id) {
                found = true;
              }
            })
            if (found == false) {
              ret.push(user);
            }

          })
        }
      })
      return ret;

    }
  },
  mounted: function () {

  },
  created: function () {


    if (this.preselect) {
      //console.log('pre',this.preselect);
      this.selected = JSON.parse(this.preselect);
      //console.log('selec',this.selected);
      this.state = 'done';
    }

    var that = this;

    this.$bus.$on('handlerSelect', data => {
      that.handlerSelect(data.typ, data.content)
    });
    this.$bus.$on('handlerSelectGroup', data => {
      that.handlerSelectGroup(data.typ, data.content)
    });
    /*
    this.$bus.$on('selectActive', data => {
      that.selectActive(data.typ, data.content)
    });

     */


  },
  methods: {

    handlerSubmit() {


      //console.log(this.selected);


      let list = [];
      /*
      this.selectedUserList.forEach((o) => {
        if (o.id) {
          list.push(parseInt(o.id));
        }
      })
      */

      this.selected.forEach((o) => {
        if (o.typ && o.content) {

          let inboxs = [];
          if (o.inboxs) {
            o.inboxs.forEach((inbox) => {
              if (inbox.id) {
                inboxs.push(inbox.id);
              }
            });
          }

          list.push({
            typ: o.typ,
            content: o.content,
            inboxs: inboxs
          });
        }
      })

      console.log(list);

      this.$emit('submit', list)
      this.state = 'done';

    },
    selectActive(typ, content) {
      let ret = false;
      this.selected.forEach((o) => {
        if (o.typ == typ && o.content == content) {
          ret = true;
        }
        if (o.inboxs && typ == 'user') {
          o.inboxs.forEach((o) => {
            if (o.id == content) {
              ret = true;
            }
          })
        }
      })
      return ret;
    },
    addRecipients(typ, content, list) {

      this.selected.forEach((o) => {
        if (o.typ == typ && o.content == content) {
          o.inboxs = list;
        }
      })

    },
    loadRecipients(typ, content) {

      //console.log(typ, content);

      if (!typ || !content) {
        return false;
      }

      this.loading = true;
      var that = this;

      const formData = new FormData();
      formData.append('typ', typ);
      formData.append('content', content);

      axios.post(this.apiURL + '/getInboxes', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(function (response) {
        if (response.data) {
          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {
            that.addRecipients(typ, content, response.data);
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
    handlerSelectGroup(typ, content) {
      if (!content) {
        return false;
      }
      content.forEach((o) => {
        this.handlerSelect(typ, o);
      })
    },
    handlerSelect(typ, content) {


      let found = false;
      this.selected.forEach((select, i) => {
        if (select.typ == typ && select.content == content) {
          found = true;
          this.selected.splice(i, 1);
        }
        if (!found) {
          if (select.inboxs && typ == 'user') {
            select.inboxs.forEach((user) => {
              if (user.id == content) {
                found = true;
              }
            })
          }
        }
      })

      if (!found) {
        this.selected.push({typ: typ, content: content});
        this.loadRecipients(typ, content);
      }
    },
    handlerChangeSearch() {

      if (this.searchString == '') {
        return false;
      }

      this.loading = true;
      var that = this;

      if (this.ajaxRequest) {
        this.ajaxRequest.cancel();
      }
      this.ajaxRequest = axios.CancelToken.source();

      let filterType = 'isPupil';

      axios.get('rest.php/GetUser/' + this.searchString + '/' + filterType, {cancelToken: this.ajaxRequest.token}).then(function (response) {
        if (response.data) {
          if (response.data.error) {
            that.error = '' + response.data.msg;
          } else {
            //console.log(response.data);
            that.searchUserlist = response.data;
          }
        } else {
          that.error = 'Fehler beim Laden. 01';
        }
      }).catch(function (err) {
        //that.error = 'Fehler beim Laden. 02';
        if (axios.isCancel(err)) {
          //console.log('Previous request canceled, new request is send', err.message);
          //that.loading = false;
        } else {
          // handle error
          that.error = 'Fehler beim Laden. 02';
          that.users = [];
          that.loading = false;
        }

      }).finally(function () {
        that.loading = false;
      });

    },
    handlerOpenTab(tab) {
      if (tab) {
        this.openTab = tab;
      }
    },
    handlerOpenForm() {
      this.state = 'form';
    },
    handlerCloseForm() {
      this.state = false;
    }

  }

};
</script>

<style scoped>

</style>