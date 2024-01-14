<template>

  <div class="">

    <button v-if="!state" class="si-btn si-btn-green" v-on:click="handlerOpenForm">Empfänger wählen</button>

    <div v-if="state == 'done'" class="flex-row">
      <button class="si-btn" v-on:click="handlerOpenForm"><i class="fa fa-user"></i> {{ selectedUserLength() }} Empfänger</button>
      <div class="flex-1 margin-l-l">
        <button v-if="selectedUserLength() > 5" v-on:click="handlerShowSelectedtUserList" class="si-btn si-btn-border">Empfängerliste anzeigen</button>
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
          <div class="si-modalblack-content flex padding-l" style="overflow: hidden">

            <div class="flex-row flex-space-between">
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
              <div class="si-btn-multiple">
                <button class="si-btn si-btn-green" @click="handlerSubmit"><i class="fa fa-check"></i>
                  {{ selectedUserList.length }} Empfänger wählen
                </button>
                <button class="si-btn si-btn-border si-btn-icon" @click="handlerCloseForm"><i class="fa fa-times"></i>
                </button>
              </div>
            </div>


            <div class="flex-row">
              <div class="tabs flex-4">
                <div v-if="openTab == 'pupils'" class="tab flex-row">

                  <div class="flex-1" v-if="recipients.klassen">
                    <h3>Schüler*in der Klasse</h3>
                    <span v-bind:key="index" v-for="(item, index) in  recipients.klassen">
                      <BtnKlasse typ="pupils::klasse" :content="item" :selected="selected"></BtnKlasse>
                    </span>
                  </div>

                  <div class="flex-1">
                    <h3>Schüler*in</h3>
                    <input type="text" v-model="searchString" v-on:keyup="handlerChangeSearch('isPupil')" placeholder="Max 5a"/>
                    <div class="list padding-t-m scrollable-y height_70">
                      <button v-bind:key="index" v-for="(item, index) in  searchUserlist" class="si-btn margin-r-s"
                              :class="{'si-btn-active': selectActive('user', item.id) }"
                              @click="handlerSelect('user', item.id)">{{ item.name }}
                      </button>
                    </div>
                  </div>

                </div>

                <div v-if="openTab == 'parents'" class="tab flex-row">

                  <div class="flex-1" v-if="recipients.klassen">
                    <h3>Eltern der Klasse</h3>
                    <span v-bind:key="index" v-for="(item, index) in  recipients.klassen">
                      <BtnKlasse typ="parents::klasse" :content="item" :selected="selected"></BtnKlasse>
                    </span>
                  </div>
                  <div v-else class="flex-1">- kein Postfach vorhanden -</div>

                  <div class="flex-1">
                    <h3>Eltern von</h3>
                    <input type="text" v-model="searchString" v-on:keyup="handlerChangeSearch('isPupil')" placeholder="Max 5a"/>
                    <div class="list padding-t-m scrollable-y height_70">
                      <button v-bind:key="index" v-for="(item, index) in  searchUserlist" class="si-btn"
                              :class="{'si-btn-active': selectActive('user', item.id) }"
                              @click="handlerSelect('parent', item.id)">{{ item.name }}
                      </button>
                    </div>
                  </div>

                </div>
                <div v-if="openTab == 'teacher'" class="tab flex-row">
                  <div class="flex-1 padding-r-l">
                    <h3 @click="handlerAccoTeacher('default')" class="curser">Alle Lehrer*innen der Klasse</h3>
                    <div v-if="accoTeacher == 'default'">
                      <span v-bind:key="index" v-for="(item, index) in  recipients.klassen">
                        <BtnKlasse typ="teachers::klasse" :content="item" :selected="selected"></BtnKlasse>
                      </span>
                    </div>
                    <h3 @click="handlerAccoTeacher('leader')" class="curser">Klassenleitung</h3>
                    <div v-if="accoTeacher == 'leader'">
                      <span v-bind:key="index" v-for="(item, index) in  recipients.klassen">
                        <BtnKlasse typ="leaders::klasse" :content="item" :selected="selected"></BtnKlasse>
                      </span>
                    </div>
                    <h3 @click="handlerAccoTeacher('fachschaft')" class="curser">Fachschaften</h3>
                    <div v-if="accoTeacher == 'fachschaft'">
                      <button v-bind:key="index" v-for="(item, index) in  recipients.fachschaft" class="si-btn margin-r-s"
                              :class="{'si-btn-active': selectActive('fachschaft', item.id) }"
                              @click="handlerSelect('fachschaft', item.id)">{{ item.title }}
                      </button>
                    </div>
                    
                  </div>
                  <div class="flex-1">
                    <h3>Lehrer*in</h3>

                    <input type="text" v-model="searchString" v-on:keyup="handlerChangeSearch('isTeacher')" placeholder="Müller"/>
                    <div class="list padding-t-m scrollable-y height_70">
                      <button v-bind:key="index" v-for="(item, index) in  searchUserlist" class="si-btn"
                              :class="{'si-btn-active': selectActive('user', item.id) }"
                              @click="handlerSelect('user', item.id)">{{ item.name }}
                      </button>
                    </div>



                  </div>
                </div>
                <div v-if="openTab == 'shool'" class="tab">

                  <h3>Verwaltung</h3>

                  <div class="list">
                    <button v-bind:key="index" v-for="(item, index) in  recipients.verwaltung" class="si-btn margin-r-s"
                            :class="{'si-btn-active': selectActive('verwaltung', item.id) }"
                            @click="handlerSelect('verwaltung', item.id)">{{ item.title }}
                    </button>
                  </div>


                </div>
                <div v-if="openTab == 'groups'" class="tab">
                  
                  <h3>Gruppen</h3>

                  <div v-if="recipients.group">
                    <button v-bind:key="index" v-for="(item, index) in  recipients.group" class="si-btn margin-r-s"
                            :class="{'si-btn-active': selectActive('group', item.id) }"
                            @click="handlerSelect('group', item.id)">{{ item.title }}
                    </button>
                  </div>
                  <div v-else>- kein Postfach vorhanden -</div>

                </div>

              </div>


              <div class="selected flex-1 margin-l-l">

                <div class="scrollable-y height-60vh padding-b-m margin-t-l">
                  <div v-bind:key="index" v-for="(item, index) in  selectedUserList"
                       class="margin-b-s line-oddEvenDark padding-s padding-l-m">
                    <div class="">
                      <!--
                      <span v-if="item.user">{{ item.user.name }}
                        <span v-if="item.user.klasse" class="text-small">{{ item.user.klasse }}</span>
                      </span>
                      <span v-else-if="item.title">{{ item.title }}</span>
                    -->

                      {{ item.title }}
                    </div>
                  </div>
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
      openTab: false,
      selected: [],

      searchString: '',
      searchUserlist: [],

      selectedUserShow: false,

      accoTeacher: false
    };
  },
  props: {
    recipients: Array,
    preselect: Array,
    cache: Object
  },
  computed: {
    cachedUserList: function () {


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

    },
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

      //this.selected = JSON.parse(this.preselect);


/*
      let obj = JSON.parse(this.preselect);
      //console.log(obj)
      obj.forEach((o) => {
        //console.log('TODO no cahce? ')
        //this.handlerSelect(o.typ, o.content);
        //this.loadRecipients(o.typ, o.content);
      })
      */

      //this.handlerSubmit();

      //this.$emit('submit', obj, obj)




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

    handlerAccoTeacher: function (item) {

      if (this.accoTeacher == item) {
        this.accoTeacher = false;
      } else {
        this.accoTeacher = item;
      }
    },
    selectedUserLength: function () {


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

      console.log('loadRecipients', typ, content);

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

      //console.log('handlerSelectGroup',typ, content);

      if (!content) {
        return false;
      }
      content.forEach((o) => {
        this.handlerSelect(typ, o);
      })
    },
    handlerSelect(typ, content) {

      console.log('handlerSelect',typ, content);

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
    handlerChangeSearch(type) {

      if (this.searchString == '') {
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

      axios.get('rest.php/GetUser/' + this.searchString + '/' + type, {cancelToken: this.ajaxRequest.token}).then(function (response) {
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
      if (tab == 'teacher') {
        this.searchUserlist = [];
        this.searchString = '';
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

</style>