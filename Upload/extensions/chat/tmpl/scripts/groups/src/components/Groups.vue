<template>
  <div class="groups" >

    <div class="header">
      <button class="si-btn" v-on:click="handlerNew">Neu</button>
    </div>
    <ul v-if="groups.length > 0" class="list">
      <li v-bind:key="index" v-for="(item, index) in  groups" class="" v-on:click="handlerGroupOpen(item)">
        <img class="img" src="cssjs/images/userimages/default.png" />
        <div class="unread">{{item.unread}}</div>
        <div class="item">
          <div class="title">{{item.title}}</div>
          <div class="msgText text-grey text-small">{{item.lastMsgText}}</div>
          <div class="msgTime text-grey text-small">{{item.lastMsgTime}}</div>
        </div>
      </li>
    </ul>
    <ul v-else class="list">
      <li>
        <div v-on:click="handlerNew">Neue Gruppe anlegen</div>
      </li>
    </ul>


  </div>
</template>

<script>

export default {
  components: {

  },
  data() {
    return {

    };
  },
  props: {
    groups: Array
  },
  created: function () {

  },
  methods: {

    handlerNew: function () {
      this.$emit('showGroupForm', {members:[]} )
    },

    handlerGroupOpen: function (item) {

      if (!item.id) {
        return false;
      }
      //console.log(item);
      this.group = false;
      this.group = item;
      //this.loadGroup(item);
      this.$emit('loadGroup', item)
    }

  }

};
</script>

<style scoped>
.groups .list {
  list-style: none;
  padding: 0;
  margin: 0;
  margin-top: 3rem;
}

.groups .list li {
  padding-top: 3rem;
  padding-bottom: 3rem;
  padding-left: 3rem;
  padding-right: 3rem;
  font-size: 120%;
  display: flex;
  cursor: pointer;
}
.groups .list li:hover .title {
  color: #367fa9;
}

.groups .list li:nth-child(even){
  background-color: #f9f9f9;
}
.groups .list li .img {
  min-width: 5vw;
  max-width: 5vw;
  height: 5vw;
  border-radius: 100%;
  margin-right: 3rem;
}
.groups .list li .item {
  display: flex;
  flex-wrap: wrap;
  flex: 1;
}
.groups .list li .item .title {
  flex: 1;
  flex-basis: 100%;
  font-size: 120%;
  font-weight: 300;
  line-height: 100%;
  letter-spacing: 0.75pt;
  padding-top: 1.5rem;
}
.groups .list li .item .msgText {
  flex: 4;
}
.groups .list li .item .msgTime {
  flex: 1;
  text-align: center;
  color: #424242;
}

</style>