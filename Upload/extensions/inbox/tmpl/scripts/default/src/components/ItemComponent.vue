<template>
  <div class="inbox-msg-read padding-b-l margin-b-l margin-t-m si-box">

    <div class="flex-row flex-end si-btn-multiple padding-r-m padding-b-m">

      <button v-if="!item.isPrivat" class="si-btn si-btn-light si-btn-small" @click="handlerAnswer()"><i class="fa fa fa-reply"></i> Antworten</button>
      <button v-if="!item.isPrivat" class="si-btn si-btn-light si-btn-small" @click="handlerAnswerAll()"><i class="fa fa fa-reply"></i> Allen Antworten</button>

      <button v-if="!item.isPrivat" class="si-btn si-btn-light si-btn-small" @click="handlerForward()"><i class="fa fa fa-share"></i> Weiterleiten</button>

      <button class="si-btn si-btn-light si-btn-small" @click="handlerDelete()"><i class="fa fa fa-trash"></i></button>
      <button class="si-btn si-btn-light si-btn-small si-btn-icon" @click="handlerBack()"><i class="fa fa-times-circle"></i></button>
    </div>





    <ul class="noListStyle mailHeader">

      <li v-if="folder.id == 2" class="margin-b-m">
        <div v-if="item.isConfirm == 1 && item.confirmList">
          <h3>Lesebestätigungen</h3>
          <ul>
            <li v-if="item.confirmList.to.length > 0">
              <label>Empfänger</label>
              <span>
              <div v-bind:key="i" v-for="(obj, i) in  item.confirmList.to">

                <h4>{{ obj.title }}</h4>

                <div v-bind:key="n" v-for="(inbox, n) in  obj.inboxs">

                  <span v-if="inbox.inbox.user">{{ inbox.inbox.user.name }}</span>
                  <span v-else>{{ inbox.inbox.title }}</span>

                  <span v-if="inbox.msg.confirmTime"><i class="fa fa-check"></i>{{ inbox.msg.confirmTime }}</span>
                  <span v-else><i class="fa fa-times-circle"></i></span>

                </div>
              </div>
            </span>
            </li>

            <li v-if="item.confirmList.toCC.length > 0">
              <label>Kopieempfänger</label>
              <span>
              <div v-bind:key="i" v-for="(obj, i) in  item.confirmList.toCC">

                <span v-if="obj.inbox.user">{{ obj.inbox.user.name }}</span>
                <span v-else>{{ obj.inbox.title }}</span>

                <span v-if="obj.msg.confirmTime"><i class="fa fa-check"></i>{{ obj.msg.confirmTime }}</span>
                <span v-else><i class="fa fa-times-circle"></i></span>

              </div>
            </span>
            </li>
          </ul>
        </div>

      </li>
      <li v-else class="  ">

      <ul>
        <li class="line-oddEven padding-s" v-if="item.isConfirm == 1">
          <label>Lesebestätigung:</label>
          <button class="si-btn si-btn-green" @click="handlerConfirm"><i class="fa fa-check"></i>  Senden</button>
        </li>
        <li class="line-oddEven padding-s " v-if="item.isConfirm > 1" >
          <label>Lesebestätigung:</label>
          <span>Der Empfang wurde bestätigt am {{ item.confirmTime }}</span>
        </li>
        </ul>
      </li>


      <li class="line-oddEven padding-s">
        <label>Sender:</label>
        <span v-if="item.from.user">{{ item.from.user.name }}</span>
        <span v-else>{{ item.from.title }}</span>
      </li>

      <li class="line-oddEven padding-s">
        <label>Empfänger:</label>
        <span v-bind:key="i" v-for="(inbox, i) in  item.to">
          {{ inbox.title }} ({{ inbox.count }})
        </span>
      </li>
      <li v-if="item.toCC" class="line-oddEven padding-s">
        <label>Kopieempfänger:</label>
        <span v-bind:key="i" v-for="(inbox, i) in  item.toCC">
            {{ inbox.title }} ({{ inbox.count }})
          </span>
      </li>
      <li class="line-oddEven padding-s">
        <label>Datum:</label>
        <span>{{ item.date }}</span>
      </li>
      <li class="line-oddEven padding-s" v-if="item.isRead && item.isReadDate && item.isReadUser">
        <label>Gelesen von:</label>
        <span>{{item.isReadDate}} - {{ item.isReadUser.name }}</span>
      </li>
      <li v-if="item.files" class="line-oddEven padding-s">
        <label>Anhang:</label>
        <span class="blockInline">
          <div class="flex">
          <div v-bind:key="i" v-for="(obj, i) in  item.files">
            <a :href="'index.php?page=ext_inbox&view=file&fid='+obj.uniqid" target="_blank">{{obj.name}}</a>
          </div>
            </div>
        </span>
      </li>
      <li class="line-oddEven  padding-s padding-t-m">
        <label>Betreff:</label>
        <span class="text-big-2">{{ item.subject }}</span>
      </li>
      <li class="padding-l">
        <QuillEditor theme="" enable="false" toolbar="" :content="item.text" contentType="html" readOnly="true" />
      </li>
    </ul>

  </div>

</template>

<script>

export default {
  name: 'ItemComponent',
  data() {
    return {};
  },
  props: {
    acl: Array,
    item: [],
    folder: Array
  },
  created: function () {

  },
  methods: {

    handlerForward() {

      this.$bus.$emit('page--open', {
        page: 'form',
        item: this.item,
        props: {
          forward: true
        }
      });

    },
    handlerAnswer() {

      this.$bus.$emit('page--open', {
        page: 'form',
        item: this.item,
        props: {
          answer: true
        }
      });

    },
    handlerAnswerAll() {

      this.$bus.$emit('page--open', {
        page: 'form',
        item: this.item,
        props: {
          answerAll: true
        }
      });

    },
    handlerConfirm() {
      this.$bus.$emit('message-confirm', {
        item: this.item
      });
    },
    handlerBack() {
      this.$bus.$emit('page--open', {
        page: 'list'
      });
    },

    handlerDelete() {

      if (confirm("Die Nachricht endgültig löschen?") == true) {
        this.$bus.$emit('item--delete', {
          item: this.item
        });
      }


    }

  }


};
</script>

<style>

.mailHeader li label {
  width: 10vw;
  margin-bottom: 0;
}
</style>