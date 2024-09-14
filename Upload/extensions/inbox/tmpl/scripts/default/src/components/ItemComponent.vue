<template>
  <div class="inbox-msg-read padding-b-l margin-b-l margin-t-m si-box">

    <div class="flex-row flex-end  padding-r-m padding-b-m">

      <!-- Entwurf -->
      <div v-if="folder.id == 4" class="si-btn-multiple">
        <button v-if="allowAnswer(item)" class="si-btn si-btn-light si-btn-small" @click="handlerUse()"><i class="fa fa fa-reply"></i> Benutzen</button>
        <button class="si-btn si-btn-light si-btn-small" @click="handlerDelete()"><i class="fa fa fa-trash"></i></button>
      <button class="si-btn si-btn-light si-btn-small si-btn-icon" @click="handlerBack()"><i class="fa fa-times-circle"></i></button>
      </div>
      <div v-else class="si-btn-multiple">
        <button v-if="allowAnswer(item)" class="si-btn si-btn-light si-btn-small" @click="handlerAnswer()"><i class="fa fa-reply"></i> Antworten</button>
        <button v-if="allowAnswer(item)" class="si-btn si-btn-light si-btn-small" @click="handlerAnswerAll()"><i class="fa fa-reply"></i> Allen Antworten</button>
        <button v-if="allowAnswer(item, true)" class="si-btn si-btn-light si-btn-small" @click="handlerForward()"><i class="fa fa-share"></i> Weiterleiten</button>
        <button class="si-btn si-btn-light si-btn-small si-btn-icon" @click="handlerSetUnred()" title="Als ungelesen markieren"><i class="far fa-envelope"></i></button>
        <button class="si-btn si-btn-light si-btn-small si-btn-icon" @click="handlerPrint()" title="Als pdf Speichern"><i class="fa fa-download"></i></button>
        <button class="si-btn si-btn-light si-btn-small si-btn-icon" @click="handlerDelete()" title="Löschen"><i class="fa fa-trash"></i></button>
      <button class="si-btn si-btn-light si-btn-small si-btn-icon" @click="handlerBack()"><i class="far fa-times-circle"></i></button>
      </div>

    </div>



    <ul class="noListStyle mailHeader" id="mailHeader">




      <li class="line-oddEven padding-s" v-if="item.from">
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
      <li class="line-oddEven padding-s flex-row" v-if="item.isRead && item.isReadDate && item.isReadUser">
        <label>Gelesen von:</label>
        <div class="blockInline">
          <div>{{item.isReadDate}} - {{ item.isReadUser.name }}</div>

          <div v-if="item.isReadList" class="flex">
            <div v-bind:key="i" v-for="(obj, i) in  item.isReadList">{{obj.isReadDate}} - {{ obj.isReadUser.name }}</div>
          </div>
        </div>
      </li>
      <li class="line-oddEven padding-s" v-if="item.isAnswer">
        <label>Beantwortet:</label>
        <span>{{item.isAnswer }}</span>
      </li>
      <li class="line-oddEven padding-s" v-if="item.isForward">
        <label>Weitergeleitet:</label>
        <span>{{item.isForward }}</span>
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

      <!-- Lesebestätigung:  Gesendet -->
      <li v-if="folder.id == 2" class="margin-b-m">
        <div v-if="item.isConfirm == 1 && item.confirmList" class="flex-row">
          <label>Lesebestätigungen</label>
          <div class="blockInline">
            <ul v-if="item.showConfirm || item.confirmList.to.length == 1" class="noListStyle">
              <li v-if="item.confirmList.to.length > 0">
                <label>Empfänger:</label>

                <div v-bind:key="i" v-for="(obj, i) in  item.confirmList.to">

                  <h4>{{ obj.title }}</h4>

                  <div v-bind:key="n" v-for="(inbox, n) in  obj.inboxs">

                    <span v-if="inbox.inbox.user" class="blockInline width-20rem">{{ inbox.inbox.user.name }}</span>
                    <span v-else class="blockInline width-20rem">{{ inbox.inbox.title }}</span>
                    <span class="blockInline width-3rem"></span>
                    <span v-if="inbox.msg.confirmTime" ><i class="fa fa-check text-green"></i> {{ inbox.msg.confirmTime }}</span>
                    <span v-else class="text-red"><i class="fa fa-times-circle"></i></span>

                  </div>
                </div>

              </li>

              <li v-if="item.confirmList.toCC.length > 0">
                <label>Kopieempfänger:</label>

                <div v-bind:key="i" v-for="(obj, i) in  item.confirmList.toCC">

                  <span v-if="obj.inbox.user">{{ obj.inbox.user.name }}</span>
                  <span v-else>{{ obj.inbox.title }}</span>

                  <span v-if="obj.msg.confirmTime"><i class="fa fa-check"></i>{{ obj.msg.confirmTime }}</span>
                  <span v-else><i class="fa fa-times-circle"></i></span>

                </div>

              </li>
            </ul>
            <button v-else @click="handlerOpenConfirm(item)" class="si-btn si-btn-border"><i class="fa fa-check-double"></i> Öffnen</button>
          </div>
        </div>

      </li>
      <!-- Lesebestätigung: Entwurf -->
      <li v-else-if="folder.id == 4" class="margin-b-m">

      </li>
      <!-- Lesebestätigung -->
      <li v-else class="  ">

        <ul class="noListStyle">
          <li class="line-oddEven padding-s" v-if="item.isConfirm == 1">
            <label>Lesebestätigung:</label>
            <button class="si-btn si-btn-green" @click="handlerConfirm"><i class="fa fa-check"></i>  Gelesen</button>
          </li>
          <li class="line-oddEven padding-s " v-if="item.isConfirm > 1" >
            <label>Lesebestätigung:</label>
            <span>Der Empfang wurde bestätigt am {{ item.confirmTime }}</span>
          </li>
        </ul>
      </li>

      <li v-if="isMobile" class="line-oddEven text-big-2 padding-s padding-t-m">
        <span class="">{{ item.subject }}</span>
      </li>
      <li v-else class="line-oddEven text-big-2 padding-s padding-t-m">
        <label class="">Betreff:</label>
        <span class="">{{ item.subject }}</span>
      </li>



      <li v-if="item.umfrage" class="line-oddEven padding-s">
        <label>Umfrage:</label>
        <span v-if="folder.id == 2">
          <a class="si-btn si-btn-border" target="_blank" :href="'index.php?page=ext_umfragen&view=list&lid='+item.umfrage"><i class="fa fa-poll"></i> Antworten anzeigen</a>
        </span>
        <span v-else>
          <div v-if="item.umfragen && item.umfragen.answers" class="padding-l-m">
            <UmfragenResult :form="item.umfragen"></UmfragenResult>
          </div>
          <div class="si-form" v-else-if="item.umfragenOpen && item.umfragen.childs">
            <UmfragenAnswer :form="item.umfragen" :parent_id="item.id" btnSave="true"></UmfragenAnswer>
          </div>
          <button class="si-btn" v-else-if="item.umfragen.childs" @click="handlerOpenUmfrage(item)"><i class="fa fa-poll"></i> Fragen beantworten</button>
        </span>
      </li>
      <li class="padding-l body">
        <QuillEditor theme="" enable="false" toolbar="" :content="item.text" contentType="html" readOnly="true" />
      </li>
    </ul>

  </div>

</template>

<script>

import UmfragenAnswer from "@/mixins/UmfragenAnswer.vue";
import UmfragenResult from "@/mixins/UmfragenResult.vue";
import html2canvas from "html2canvas";
import { jsPDF } from "jspdf";

export default {
  name: 'ItemComponent',
  components: {UmfragenAnswer, UmfragenResult},
  data() {
    return {
      //umfragenOpen: false,
      printLogo: window.globals.printLogo,
      printSystem: window.globals.printSystem,
      printDate: window.globals.printDate,
      isMobile: window.globals.isMobile
    };
  },
  props: {
    acl: Array,
    item: [],
    folder: Array
  },
  created: function () {

  },
  methods: {

    handlerOpenConfirm(item) {
      item.showConfirm = true;
    },
    allowAnswer(item, force) {
      if(force === true){
        return true;
      }
      if (item.isPrivat) {
        return false;
      }
      if (item.noAnswer) {
        return false;
      }
      if(item.folder_id == 2){
        return false
      }
      return true;
    },
    handlerPrint() {

      window.html2canvas = html2canvas;

      var doc = new jsPDF(
          'p', 'mm', 'a4'
      );

      let htmlTemp = document.createElement('div');
      htmlTemp.style.fontSize = '14pt';

      let imgHead = document.createElement('img');
      imgHead.src = this.printLogo;
      imgHead.style.width = '20mm';
      imgHead.style.height = '20mm';
      imgHead.style.position = 'relative';
      imgHead.style.display = 'block';
      imgHead.style.marginBottom = '2rem';
      htmlTemp.appendChild(imgHead);

      let node = document.getElementById('mailHeader');
      htmlTemp.appendChild(node.cloneNode(true));

      let htmlFooter = document.createElement('div');
      htmlFooter.innerText = this.printSystem+' - '+this.printDate;
      htmlFooter.style.fontSize = '10pt';
      htmlFooter.style.width = '100%';
      htmlFooter.style.textAlign = 'center';
      htmlFooter.style.paddingTop = '10mm';
      htmlFooter.style.color = '#ccc';
      htmlTemp.appendChild(htmlFooter);

      //console.log(htmlTemp)
      //node.appendChild(htmlTemp)

      doc.html(htmlTemp, {
        callback: function (doc) {
          doc.save("Nachricht.pdf");
        },
        x: 20,
        y: 10,
        width: 170,
        windowWidth: 1024
      });

      //doc.html(node, 10, 10);
      //doc.save("a4.pdf");

    },
    handlerOpenUmfrage(item) {
      item.umfragenOpen = true;
    },
    handlerSetUnred() {

      this.$bus.$emit('message-setUnread', {
        item: this.item
      });

    },
    handlerForward() {

      this.$bus.$emit('page--open', {
        page: 'form',
        item: this.item,
        props: {
          forward: true
        }
      });

    },
    handlerUse() {
      this.$bus.$emit('page--open', {
        page: 'form',
        item: this.item,
        props: {
          use: true
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
  min-width: 15rem;
}
</style>