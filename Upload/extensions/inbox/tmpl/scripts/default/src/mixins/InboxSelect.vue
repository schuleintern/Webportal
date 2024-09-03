<template>

  <div class="">

    <button v-if="!state" class="si-btn si-btn-green" v-on:click="handlerOpenForm">Empfänger wählen</button>

    <div v-if="state == 'done'" class="flex-row">
      <button class="si-btn" v-on:click="handlerOpenForm"><i class="fa fa-user"></i> {{ selectedUserLength() }}
        Empfänger
      </button>
      <div class="flex-1 margin-l-l">
        <button v-if="selectedUserLength() >= 5" v-on:click="handlerShowSelectedtUserList" class="si-btn si-btn-border">
          Empfängerliste anzeigen
        </button>
        <div v-if="selectedUserLength() < 5 || selectedUserShow == true">
          <div v-bind:key="index" v-for="(item, index) in  cachedUserList" class="blockInline margin-r-m margin-b-s">
            <span v-if="item.user">{{ item.user.name }}</span>
            <span v-else-if="item.title">{{ item.title }}</span>
          </div>
        </div>

      </div>
    </div>
    <div v-if="state == 'form'">
      <div class="si-modalblack" v-on:click.self="handlerCloseForm">
        <div class="si-modalblack-box">
          <div class="si-modalblack-content flex-row flex-space-between" style="overflow: hidden">

            <div class="left flex-1 margin-r-l">


              <div class="si-btn-multiple tabs-head">
                <button class="si-btn margin-r-s text-bold" :class="{'si-btn-active':openTab=='default'}"
                        @click="handlerOpenTab('default')">
                  <i class="fa fa-home margin-l-m"></i>
                </button>
                <button class="si-btn margin-r-s text-bold" :class="{'si-btn-active':openTab=='pupils'}"
                        @click="handlerOpenTab('pupils')"
                        v-if="recipients.acl.pupils.klassen || recipients.acl.pupils.single || recipients.acl.pupils.own">
                  Schüler*innen
                </button>
                <button class="si-btn margin-r-s text-bold" :class="{'si-btn-active':openTab=='parents'}"
                        @click="handlerOpenTab('parents')"
                        v-if="recipients.acl.parents.klassen || recipients.acl.parents.single || recipients.acl.parents.own">
                  Eltern
                </button>
                <button class="si-btn margin-r-s text-bold" :class="{'si-btn-active':openTab=='teacher'}"
                        @click="handlerOpenTab('teacher')"
                        v-if="recipients.acl.teachers.klassen || recipients.acl.teachers.single  || recipients.acl.teachers.leitung  || recipients.acl.teachers.fachschaft || recipients.acl.teachers.own">
                  Lehrer*innen
                </button>
                <button class="si-btn text-bold" :class="{'si-btn-active':openTab=='groups'}"
                        @click="handlerOpenTab('groups')"
                        v-if="recipients.acl.inboxs.inboxs || recipients.acl.inboxs.groups">
                  Sonstige
                </button>
              </div>

              <div class="tabs flex-4 bg-white text-black">
                <div v-if="openTab == 'default'" class="tab flex-row">
                  <div class="padding-l ">



                  </div>
                </div>
                <div v-if="openTab == 'pupils'" class="tab flex-row">

                  <div class="flex-1 padding-l">
                    <span v-if="recipients.klassen && recipients.acl.pupils.klassen">
                      <h3 @click="handlerAccoTeacher('klassen')" class="curser line-oddEven">
                        <i v-if="accoTeacher == 'klassen'" class="fa fa-chevron-down text-small margin-r-m"></i>
                        <i v-else class="fa fa-chevron-right text-small margin-r-m"></i>
                        Klassen</h3>
                      <div v-if="accoTeacher == 'klassen'" class="tabs-content">
                        <span v-bind:key="index" v-for="(item, index) in  recipients.klassen">
                          <BtnKlasse typ="pupils::klasse" :content="item" :selected="selected"
                                     @submit="handlerBtnSubmit"></BtnKlasse>
                        </span>
                      </div>
                    </span>
                    <span v-if="recipients.acl.pupils.single">
                      <h3 @click="handlerAccoTeacher('search')" class="curser line-oddEven">
                        <i v-if="accoTeacher == 'search'" class="fa fa-chevron-down text-small margin-r-m"></i>
                        <i v-else class="fa fa-chevron-right text-small margin-r-m"></i>
                        Suchen</h3>
                      <div v-if="accoTeacher == 'search'" class="tabs-content">
                        <input type="text" v-model="searchString" v-on:keyup="handlerChangeSearch('isPupil')"
                               placeholder="Max 5a"/>
                        <div class="list padding-t-m scrollable-y height_50">
                          <button v-bind:key="index" v-for="(item, index) in  searchUserlist" class="si-btn margin-r-s"
                                  :class="{'si-btn-active': selectActive('user', item.id) }"
                                  @click="handlerSelect('user', item.id)">{{ item.name }} - {{ item.klasse }}
                          </button>
                        </div>
                      </div>
                    </span>

                    <span v-if="recipients.acl.pupils.own && recipients.own.pupils">
                      <div v-bind:key="index" v-for="(items, index) in  recipients.own.pupils">
                        <h3>Klasse: {{items.title}}</h3>
                        <div class="si-btn-multiple">
                        <button class="si-btn margin-r-s si-btn-border"
                                :class="{'si-btn-active': selectActive('pupils::klasse', items.title) }"
                                @click="handlerSelect('pupils::klasse', items.title)">Alle Schüler*innen der Klasse {{items.title}}</button>

                        <button v-bind:key="i" v-for="(item, i) in  items.inboxs"
                                class="si-btn margin-r-s"
                                :class="{'si-btn-active': selectActive('user', item.user_id) }"
                                @click="handlerSelect('user', item.user_id)">{{ item.userName }}</button>
                        </div>
                      </div>
                    </span>
                  </div>


                </div>
                <div v-if="openTab == 'parents'" class="tab flex-row">

                  <div class="flex-1 padding-l">
                    <span v-if="recipients.klassen && recipients.acl.parents.klassen">
                      <h3  @click="handlerAccoTeacher('klassen')" class="curser line-oddEven">
                        <i v-if="accoTeacher == 'klassen'" class="fa fa-chevron-down text-small margin-r-m"></i>
                        <i v-else class="fa fa-chevron-right text-small margin-r-m"></i>
                        Klassen</h3>
                      <div v-if="accoTeacher == 'klassen'" class="tabs-content">
                        <span v-bind:key="index" v-for="(item, index) in  recipients.klassen">
                        <BtnKlasse typ="parents::klasse" :content="item" :selected="selected"
                                   @submit="handlerBtnSubmit"></BtnKlasse>
                        </span>
                      </div>
                    </span>
                    <span v-if="recipients.acl.parents.single">
                      <h3 @click="handlerAccoTeacher('search')" class="curser line-oddEven">
                        <i v-if="accoTeacher == 'search'" class="fa fa-chevron-down text-small margin-r-m"></i>
                        <i v-else class="fa fa-chevron-right text-small margin-r-m"></i>
                        Suchen</h3>
                      <div v-if="accoTeacher == 'search'" class="tabs-content">
                        <input type="text" v-model="searchString" v-on:keyup="handlerChangeSearch('isPupil')"
                               placeholder="Max 5a"/>
                        <div class="list padding-t-m scrollable-y height_50">
                          <button v-bind:key="index" v-for="(item, index) in  searchUserlist" class="si-btn margin-r-s"
                                  :class="{'si-btn-active': selectActive('parent', item.id) }"
                                  @click="handlerSelect('parent', item.id)">{{ item.name }} - {{item.klasse}}
                          </button>
                        </div>
                      </div>
                    </span>

                    <span v-if="recipients.acl.parents.own && recipients.own.parents">
                      <div v-bind:key="index" v-for="(items, index) in  recipients.own.parents">
                        <h3>Klasse: {{items.title}}</h3>
                        <div class="si-btn-multiple">
                        <button class="si-btn margin-r-s si-btn-border"
                                :class="{'si-btn-active': selectActive('parents::klasse', items.title) }"
                                @click="handlerSelect('parents::klasse', items.title)">Alle Eltern der Klasse {{items.title}}</button>

                        <button v-bind:key="i" v-for="(item, i) in  items.inboxs"
                                class="si-btn margin-r-s"
                                :class="{'si-btn-active': selectActive('parent', item.user_id) }"
                                @click="handlerSelect('parent', item.user_id)">{{ item.userName }}</button>
                        </div>
                      </div>
                    </span>
                  </div>

                </div>
                <div v-if="openTab == 'teacher'" class="tab flex-row">
                  <div class="flex-1 padding-l">

                    <span v-if="recipients.acl.teachers.single">
                      <h3 @click="handlerAccoTeacher('default')" class="curser line-oddEven">
                        <i v-if="accoTeacher == 'default'" class="fa fa-chevron-down"></i>
                        <i v-else class="fa fa-chevron-right"></i>
                        Suche</h3>
                      <div v-if="accoTeacher == 'default'" class="padding-l-l">
                        <button class="si-btn margin-r-l"
                                :class="{'si-btn-active': selectActive('teachers::all', 'all') }"
                                @click="handlerSelect('teachers::all', 'all')">Alle
                        </button>

                        <input type="text" v-model="searchString" v-on:keyup="handlerChangeSearch('isTeacher')"
                               placeholder="Müller"/>
                        <div class="si-btn-multiple blockInline margin-l-m">
                          <button class="si-btn si-btn-icon si-btn-border margin-l-s" @click="handlerSetSearch('*','isTeacher')"><i class="fa fa-list"></i></button>
                          <button class="si-btn si-btn-icon si-btn-border margin-l-s" @click="handlerSetSearch('','isTeacher')"><i class="fa fa-times"></i></button>
                        </div>
                        <div class="list padding-t-m scrollable-y height_50">
                          <button v-bind:key="index" v-for="(item, index) in  searchUserlist" class="si-btn margin-r-s"
                                  :class="{'si-btn-active': selectActive('user', item.id) }"
                                  @click="handlerSelect('user', item.id)">{{ item.name }}
                          </button>
                        </div>
                      </div>
                    </span>
                    <span v-if="recipients.klassen && recipients.acl.teachers.klassen">
                      <h3 @click="handlerAccoTeacher('klassen')" class="curser line-oddEven">
                        <i v-if="accoTeacher == 'klassen'" class="fa fa-chevron-down"></i>
                        <i v-else class="fa fa-chevron-right"></i>
                        Klassen</h3>
                      <div v-if="accoTeacher == 'klassen'" class="padding-l-l">
                        <span v-bind:key="index" v-for="(item, index) in  recipients.klassen">
                          <BtnKlasse typ="teachers::klasse" :content="item" :selected="selected"
                                     @submit="handlerBtnSubmit"></BtnKlasse>
                        </span>
                      </div>
                    </span>
                    <span v-if="recipients.klassen && recipients.acl.teachers.leitung">
                      <h3 @click="handlerAccoTeacher('leader')" class="curser line-oddEven">
                        <i v-if="accoTeacher == 'leader'" class="fa fa-chevron-down"></i>
                        <i v-else class="fa fa-chevron-right"></i>
                        Klassenleitung</h3>
                      <div v-if="accoTeacher == 'leader'" class="padding-l-l">
                        <span v-bind:key="index" v-for="(item, index) in  recipients.klassen">
                          <BtnKlasse typ="leaders::klasse" :content="item" :selected="selected"
                                     @submit="handlerBtnSubmit"></BtnKlasse>
                        </span>
                      </div>
                    </span>
                    <span v-if="recipients.fachschaft && recipients.acl.teachers.fachschaft">
                      <h3 @click="handlerAccoTeacher('fachschaft')" class="curser line-oddEven">
                        <i v-if="accoTeacher == 'fachschaft'" class="fa fa-chevron-down"></i>
                        <i v-else class="fa fa-chevron-right"></i>
                        Fachschaften</h3>
                      <div v-if="accoTeacher == 'fachschaft'" class="padding-l-l">
                        <button v-bind:key="index" v-for="(item, index) in  recipients.fachschaft"
                                class="si-btn margin-r-s"
                                :class="{'si-btn-active': selectActive('fachschaft', item.id) }"
                                @click="handlerSelect('fachschaft', item.id)">{{ item.title }}
                        </button>
                      </div>
                    </span>

                    <span v-if="recipients.acl.teachers.own && recipients.own.teachers">
                      <div v-bind:key="index" v-for="(items, index) in  recipients.own.teachers">
                        <h3>Klasse: {{items.title}}</h3>
                        <div class="si-btn-multiple">
                        <button class="si-btn margin-r-s si-btn-border"
                                :class="{'si-btn-active': selectActive('teachers::klasse', items.title) }"
                                @click="handlerSelect('teachers::klasse', items.title)">Alle Lehrer*innen der Klasse {{items.title}}</button>

                        <button v-bind:key="i" v-for="(item, i) in  items.inboxs"
                                class="si-btn margin-r-s"
                                :class="{'si-btn-active': selectActive('user', item.user_id) }"
                                @click="handlerSelect('user', item.user_id)">{{ item.userName }}</button>
                        </div>
                      </div>
                    </span>

                  </div>

                </div>
                <div v-if="openTab == 'groups'" class="tab">

                  <div class="flex-1 padding-l">

                    <span v-if="recipients.acl.inboxs.inboxs">
                      <div v-if="recipients.inboxs">
                        <button v-bind:key="index" v-for="(item, index) in  recipients.inboxs" class="si-btn margin-r-s"
                                :class="{'si-btn-active': selectActive('inbox', item.id) }"
                                @click="handlerSelect('inbox', item.id)">{{ item.title }}
                        </button>
                      </div>
                    </span>

                    <span v-if="recipients.acl.inboxs.groups">
                      <div v-if="recipients.group">
                        <button v-bind:key="index" v-for="(item, index) in  recipients.group" class="si-btn margin-r-s"
                                :class="{'si-btn-active': selectActive('group', item.id) }"
                                @click="handlerSelect('group', item.id)">{{ item.title }}
                        </button>
                      </div>
                    </span>

                    <span >
                      <div v-if="recipients.inboxUsers	">
                        <button v-bind:key="index" v-for="(item, index) in  recipients.inboxUsers" class="si-btn margin-r-s"
                                :class="{'si-btn-active': selectActive('inbox', item.id) }"
                                @click="handlerSelect('inbox', item.id)">{{ item.title }}
                        </button>
                      </div>
                    </span>
                  </div>

                </div>

              </div>

            </div>

            <div class="right">
              <div class="si-btn-multiple">
                <button class="si-btn si-btn-green  text-bold" @click="handlerSubmit"><i class="fa fa-check"></i>
                  {{ selectedList.length }} Empfänger wählen
                </button>
                <button class="si-btn si-btn-border si-btn-icon" @click="handlerCloseForm"><i class="fa fa-times"></i>
                </button>
              </div>
              <div class="selected flex-1 margin-l-l margin-t-m height_70 scrollable-y">
                <div v-bind:key="index" v-for="(item, index) in  selectedList"
                     :class="{'text-red': item.inboxs && item.inboxs.length < 1}"
                     class="margin-b-s line-oddEvenDark padding-s padding-l-m">
                  {{ item.title }} - <span v-if="item.inboxs"><i class="fa fa-users"></i> {{
                    item.inboxs.length
                  }}</span>
                </div>
              </div>
            </div>


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
      openTab: 'default',
      selected: [],

      searchString: '',
      searchUserlist: [],

      selectedUserShow: false,

      accoTeacher: 'default'
    };
  },
  props: {
    recipients: Array,
    preselect: Array,
    cache: Object
  },
  computed: {
    cachedUserList: function () {

      //console.log(this.cache)

      return this.cache;
      /*
      let ret = [];
      if (this.cache) {
        this.cache.forEach((o) => {
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
      }
      return ret;
      */

    },
    selectedList: function () {
      //console.log(this.selected)
      if (this.selected.length > 0) {
        return this.selected;
      }
      return false;
    },

    selectedUserList: function () {

      return true;
      /*
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
      */
    }
  },
  mounted: function () {

  },
  created: function () {


    if (this.preselect) {
      //console.log('pre',this.preselect);

      //this.selected = JSON.parse(this.preselect);

      /*

                  let obj = JSON.parse(this.preselect);
                  //console.log(obj)
                  obj.forEach((o) => {
                    //console.log('TODO no cahce? ')
                    this.handlerSelect(o.typ, o.content);
                    //this.loadRecipients(o.typ, o.content);
                  })
            */


      //this.handlerSubmit();

      //this.$emit('submit', obj, obj)


      //console.log('selec',this.selected);

      this.state = 'done';

    }


  },
  methods: {

    handlerSetSearch(str, type) {
      this.searchString = str;
      this.handlerChangeSearch(type);
    },
    handlerBtnSubmit: function (data) {
      //console.log('handlerBtnSubmit', data)
      if (data.typ && data.content) {

        if (typeof data.content === 'object') {
          data.content.forEach((o) => {
            this.handlerSelect(data.typ, o);
          })

        } else {
          this.handlerSelect(data.typ, data.content)
        }

      }
    },
    handlerAccoTeacher: function (item) {

      if (this.accoTeacher == item) {
        this.accoTeacher = false;
      } else {
        this.accoTeacher = item;
      }
    },
    selectedUserLength: function () {

      if (this.cache) {
        return this.cache.length;
      }
      return 0;

      /*
          let ret = 0;
          if (this.cache) {
            this.cache.forEach((o) => {
              if (o.inboxs) {
                o.inboxs.forEach((user) => {
                  if (user.id == user.id) {
                    ret++;
                  }
                })
              }
            })
          }
          return ret;
          */


    },

    handlerShowSelectedtUserList() {
      this.selectedUserShow = !this.selectedUserShow;
    },
    handlerSubmit() {


      //console.log(this.selected);


      let list = [];

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


      //console.log(list);

      this.$emit('submit', list, this.selected)


      this.handlerCloseForm();

      //this.state = 'done';

      /*
      if (this.selected.length < 1) {
        this.state = false;
      }
      */

    },
    selectActive(typ, content) {
      let ret = false;
      this.selected.forEach((o) => {
        if (o.typ == typ && o.content == content) {
          ret = true;
        }
        if (o.inboxs && typ == 'user') {
          o.inboxs.forEach((o) => {
            if (o.user_id == content) {
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
          o.inboxs = list.data;
          o.title = list.title;
        }
      })


    },
    loadRecipients(typ, content) {

      //console.log('loadRecipients', typ, content);

      if (!typ || !content) {
        return false;
      }

      this.loading = true;
      var that = this;

      const formData = new FormData();
      formData.append('typ', typ);
      formData.append('content', content);

      let sessionID = localStorage.getItem('session');
      if (sessionID) {
        sessionID = sessionID.replace('__q_strn|', '');
      }

      axios.post(this.apiURL + '/getInboxes', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
          'auth-app': window.globals.apiKey,
          'auth-session': sessionID
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
    handlerSelect(typ, content) {

      //console.log('handlerSelect',typ, content);

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
      });

      if (!found) {
        this.selected.push({typ: typ, content: content});
        //console.log(this.selected)
        this.loadRecipients(typ, content);
      }
      return false;
    },
    handlerChangeSearch(type) {

      if (this.searchString == '') {
        this.searchUserlist = [];
        return false;
      }

      this.loading = true;
      var that = this;

      if (this.ajaxRequest) {
        this.ajaxRequest.cancel();
      }
      this.ajaxRequest = axios.CancelToken.source();

      if (!type || type == '') {
        type = 'isPupil';
      }

      //let filterType = 'isPupil';
      let sessionID = localStorage.getItem('session');
      if (sessionID) {
        sessionID = sessionID.replace('__q_strn|', '');
      }
      axios.get(this.apiURL + '/getSystemUser/' + this.searchString + '/' + type, {
        cancelToken: this.ajaxRequest.token,
        headers: {
          'auth-app': window.globals.apiKey,
          'auth-session': sessionID
        }

      }).then(function (response) {
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

        if (tab == 'teacher') {
          this.searchUserlist = [];
          this.searchString = '';
        }
        if (this.openTab == 'teacher' && (tab == 'pupils' || tab == 'parents')) {
          this.searchUserlist = [];
          this.searchString = '';
        }

        this.openTab = tab;
      }

    },
    handlerOpenForm() {


      if (this.cache) {
        this.selected = this.cache;
      } else {
        this.selected = [];
      }

      this.state = 'form';

    },
    handlerCloseForm() {


      if (this.selected.length >= 1) {
        this.state = 'done';
      } else {
        this.state = false;
      }

      this.selected = [];


    }

  }

};
</script>

<style scoped>

.tabs-head button {
  padding-top: 2rem;
  padding-bottom: 1.6rem;
}

.tabs-head button:first-child {
  border-bottom-left-radius: 0;
}

.tabs-head button:last-child {
  border-bottom-right-radius: 0;
}

.tabs {
  border-bottom-left-radius: 3rem;
  border-bottom-right-radius: 3rem;
  border-top-right-radius: 3rem;

}

.tabs h3 {
  margin-top: 0;
  margin-bottom: 0;
  padding: 1.3rem;
}

.tabs-content {
  padding: 1rem;
  padding-left: 5rem;
}

.tab {
  min-height: 10vh;
  max-height: 70vh;
  overflow-y: scroll;
}
</style>