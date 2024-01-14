<template>
  <div class="page-chat" >

    <div class="header">
      <button class="si-btn si-btn-light" v-on:click="handlerBack">Zurück</button>
      <div class="title">{{group.title}}</div>
      <button class="si-btn si-btn-light" v-on:click="handlerSettings">Settings</button>
    </div>


    <ul class="list" ref="messagesContainer">
      <li v-bind:key="index" v-for="(item, index) in group.chat" class="" >
        <div class="user" v-if="item.from.name">
          <img class="img" src="cssjs/images/userimages/default.png" />
          <div class="vorname">{{item.from.vorname}}</div>
          <div class="nachname">{{item.from.nachname}}</div>
        </div>
        <div class="item" :class="{'from': item.from != true, 'self': item.from == true}">
          <div class="text" v-html="item.msg"></div>
          <div class="timeCreate">{{item.timeCreate}}</div>
        </div>
      </li>
    </ul>

    <div class="footer">
      <textarea v-model="form.msg" ></textarea>
      <button class="si-btn" v-on:click="handlerFormSubmit"><i class="fas fa-paper-plane"></i></button>
    </div>

  </div>
</template>

<script>

const axios = require('axios').default;

import User from '../mixins/User.vue'

export default {
  components: {
    User
  },
  data() {
    return {
      apiURL: globals.apiURL,
      error: false,
      interval: false
    };
  },
  props: {
    group: Array,
    form: Object,
    loading: Boolean
  },
  created: function () {

    var that = this;




/*
    this.interval = setInterval(function () {

      if(that.group.id) {
        axios.get( that.apiURL+'/getChat/'+that.group.id)
        .then(function(response){
          if ( response.data ) {
            if (!response.data.error) {
              //console.log(response.data);
              that.group.chat = response.data;
            } else {
              that.error = ''+response.data.msg;
            }
          } else {
            that.error = 'Fehler beim Laden. 01';
          }
        })
        .catch(function(){
          that.error = 'Fehler beim Laden. 02';
        })
        .finally(function () {
        });
      }

    }, 5000);

*/

  },
  updated() {

    //this.$nextTick(() => this.scrollToEnd());
  },
  watch: {
    loading: {
      immediate: true,
      handler (val, oldVal) {
        //console.log(val, oldVal);
        this.scrollToEnd();
      }
    }
  },
  methods: {

    handlerSettings: function () {
      clearInterval(this.interval);
      this.$emit('form', this.group)
    },

    scrollToEnd: function () {
      var content = this.$refs.messagesContainer;
      if (content) {
        content.scrollTop = content.scrollHeight
        //console.log("scroll height is " + content.scrollHeight + " scroll Top is " +  content.scrollTop);
      }
    },

    handlerBack: function () {

      clearInterval(this.interval);
      this.$emit('close')
    },
    handlerFormSubmit: function () {

      if (!this.form.msg) {
        return false;
      }
      this.$emit('submit', this.form)
    }

  }

};
</script>

<style scoped>

.list {
  padding: 0;
  margin: 0;
  list-style: none;
  height: 60vh;
  overflow-y: auto;

}
.list li {
  display: flex;
}
.list .item {
  margin: 1rem;
  padding-left: 2rem;
  padding-right: 2rem;
  padding-top: 1rem;
  padding-bottom: 1rem;
  border-radius: 2rem;
  position: relative;
  min-height: 6rem;
}
.list .item.from {
  background-color: #f9f9f9;
  margin-right: 45%;
  border-top-left-radius: 0;
  flex: 2;
}
.list .item.self {
  background-color: #b7c7ce;
  margin-left: 45%;
  border-top-right-radius: 0;
  flex: 2;
}
.list .item .text {
  padding-bottom: 1rem;
}
.list .item .timeCreate {
  bottom: 1rem;
  right: 1rem;
  position: absolute;
  font-size: 90%;
  color: #424242;
}

.list .user {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem;
}
.list .user img {
  border-radius: 100%;
  width: 5rem;
  height: 5rem;
  margin-bottom: 1rem;
}
.list .user .vorname {
  text-align: center;
  letter-spacing: 0.75pt;
  font-weight: 300;
  font-size: 100%;
  color: #738b96;
}
.list .user .nachname {
  text-align: center;
  letter-spacing: 0.75pt;
  font-weight: 300;
  font-size: 80%;
}

.footer {
  padding-left: 3rem;
  padding-right: 3rem;
}
.footer textarea {
  flex: 1;
  border: 1px solid #ccc;
  margin-left: 0.5rem;
  margin-right: 1rem;
  margin-bottom: 0.2rem;
  margin-top: 0.2rem;
  border-radius: 3rem;
  padding-top: 1rem;
  padding-left: 2rem;
  padding-right: 2rem;
  resize: none;
  -moz-appearance: none;
  -webkit-appearance: none;
  appearance: none;
}

.footer .si-btn i {
  margin: 0;
}
</style>