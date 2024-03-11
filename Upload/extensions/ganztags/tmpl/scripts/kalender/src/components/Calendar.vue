<template>
  <div>

    <ModalSchueler></ModalSchueler>
    <ModalAktivityInfo></ModalAktivityInfo>

    <div class="si-calendar">
      <div class="si-calendar-header">
        <button class="si-btn  chevron-left" @click="subtractWeek">
          <i class="fa fa-arrow-left"></i>Zurück
        </button>
        <button @click="gotoToday"
                class="si-btn si-btn-light">
          <i class="fa fa-home"></i>Heute
        </button>
        <div class="title"><span class="margin-r-l text-green">{{ room }}</span> <span class="margin-l-l">{{
            $date(firstDayOfWeek).format("DD.")
          }} - {{ $date(lastDayOfWeek).format("DD. MMMM YYYY") }}</span></div>
        <button class="si-btn chevron-right" @click="addWeek">
          <i class="fa fa-arrow-right"></i>Weiter
        </button>
      </div>


      <div class="">
        <div class="flex" :class="{ 'flex-row': !isMobile()}">

          <div v-bind:key="j" v-for="(date, j) in daysInWeek"
               class="flex-1 " :class="{ 'bg-white padding-l-m': isToday(date[0]) == true}">

            <div class="flex-row margin-t-m">
              <h4 :class="{ 'text-orange': isToday(date[0]) == true}" class="flex-1 text-center"
                  v-on:click="handlerOpenDay($event, date)">
                {{ $date(date[0]).format('DD. dd') }}</h4>
              <div class="si-btn-multiple margin-r-m">
                <button class="si-btn si-btn-icon si-btn-border" v-on:click="handlerAddAktivity(date[0])"><i
                    class="fa fa-plus"></i></button>
                <button class="si-btn si-btn-icon si-btn-border" v-on:click="handlerPrintDay(date[0])"><i
                    class="fa fa-print"></i></button>
              </div>
            </div>

            <div class="day-box" >
              <div v-bind:key="j" v-for="(content, j) in getDayContent(date[0])" class="margin-r-m item-box"
                   :class="content.type">

                <div v-if="content.type == 'day-group'" class="si-box curser"
                     :style="'border-color:'+content.color+'; border-left-width: 5px;'"
                     v-on:click="handlerOpenSchueler(content)">
                  <div class="flex-row">
                    <h4 class="flex-2" :style="'color:'+content.color">{{ content.title }}</h4>
                    <div v-if="content.schueler" class="text-right flex-1">
                      <button class="si-btn si-btn-border text-white"
                              v-bind:style=" 'background-color:'+ content.color+'; border-color:'+ content.color ">
                        {{ content.schueler.length }} <i class="fa fa-users"></i>
                      </button>
                    </div>
                  </div>
                  <div class="flex-row flex-space-between padding-t-s">
                    <div v-if="content.leader"><i class="fas fa-user"></i> {{ content.leader.userName }}</div>
                    <div v-if="content.room"><i class="fas fa-map-marker-alt"></i> {{ content.room }}</div>
                  </div>
                  <div v-if="content.info"><i class="fas fa-info-circle"></i> {{ content.info }}</div>
                </div>
                <div v-if="content.type == 'day-activity'" class="si-box curser"
                     :style="'border-color:'+content.color+'; border-left-width: 5px;'"
                     v-on:click="handlerOpenAktivity(content)">
                  <h4>{{ content.title }}</h4>
                  <div class="flex-row">
                    <div class="flex-3">
                      <div v-if="content.room"><i class="fas fa-map-marker-alt"></i> {{ content.room }}</div>
                      <div v-if="content.info"><i class="fas fa-info-circle"></i> {{ content.info }}</div>
                      <div v-if="content.leader_id" :class="{'text-green': myUserID == content.leader_id}"><i
                          class="fas fa-user"></i> {{ content.leader.userName }}
                      </div>
                    </div>

                  </div>
                </div>


                <div v-if="content.type == 'group'" class="si-box curser"
                     :style="coloredBg(content.color)"
                     v-on:click="handlerAddLeader(date[0], content)">
                  <div class="flex-row flex-space-between">
                    <div v-if="content.schueler" class=" text-bold"><i class="fa fa-child"></i>
                      {{ content.schueler.length }}
                    </div>
                    <div>{{ content.title }}</div>
                  </div>
                  {{ /* getWeekSchueler(content.week, date ) */ }}

                </div>
                <div v-if="content.type == 'activity'" class="si-box si-box-stripes curser"
                     :style="coloredBg(content.color)"
                     v-on:click="handlerAddLeader(date[0], content)">
                  <h4>{{ content.title }}</h4>
                  <div class="flex-row flex-space-between">
                    <div v-if="content.info"><i class="fas fa-info-circle"></i> {{ content.info }}</div>
                    <div v-if="content.room"><i class="fas fa-map-marker-alt"></i> {{ content.room }}</div>
                  </div>

                  <div v-if="content.leader_id != 0"><i class="fas fa-user"></i> {{ content.leader.userName }}</div>
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


// import User from "../mixins/User.vue";

import ModalSchueler from "../mixins/ModalSchueler.vue";
import ModalAktivityInfo from "../mixins/ModalAktivityInfo.vue";


export default {
  components: {
    ModalSchueler, ModalAktivityInfo
  },
  name: 'Calendar',
  props: {
    plan: [],
    acl: Object,
    room: String,
    showDays: Object,
    userSelf: Array
  },
  data() {
    return {

      myUserID: globals.myUserID || 0,
      today: this.$date(),
      thisWeek: false,

      prevDays: globals.prevDays,

      //delButtonActivity: false
      //plan: []

      daysInWeekArr: []

    }
  },
  mounted() {

  },
  created: function () {

    //this.plan = [];

    this.thisWeek = this.today;
    this.changedDate();

    this.prevDays = parseInt(this.prevDays);

    var that = this;
    EventBus.$on('calender--reload', data => {
      that.changedDate();
      EventBus.$emit('modal-item--close');
      EventBus.$emit('modal-form--close');
    });


  },
  computed: {


    firstDayOfWeek: function () {
      return this.thisWeek.startOf('week');
    },
    lastDayOfWeek: function () {
      return this.thisWeek.endOf('week');
    },
    daysInWeek: function () {
      var arr = [];
      var foo = this.firstDayOfWeek;
      for (let i = 0; i < 7; i++) {
        if (this.showDays && this.showDays[foo.format('dd')] == 1) {
          arr.push([foo, false]);
        }
        foo = foo.add(1, 'day');
      }
      return arr;
    }

  },
  methods: {
    coloredBg(color) {
      if (color) {
        return 'background-color:'+color+';border-color:'+color+';color:'+this.pickTextColorBasedOnBgColorSimple(color, '#fff', '#000');

      }
    },
    pickTextColorBasedOnBgColorSimple(bgColor, lightColor, darkColor) {
      var color = (bgColor.charAt(0) === '#') ? bgColor.substring(1, 7) : bgColor;
      var r = parseInt(color.substring(0, 2), 16); // hexToR
      var g = parseInt(color.substring(2, 4), 16); // hexToG
      var b = parseInt(color.substring(4, 6), 16); // hexToB
      var uicolors = [r / 255, g / 255, b / 255];
      var c = uicolors.map((col) => {
        if (col <= 0.03928) {
          return col / 12.92;
        }
        return Math.pow((col + 0.055) / 1.055, 2.4);
      });
      var L = (0.2126 * c[0]) + (0.7152 * c[1]) + (0.0722 * c[2]);
      return (L > 0.5) ? darkColor : lightColor;
    },
    handlerOpenDay(event, date) {

      console.log(event.target.parentElement.nextElementSibling);
      console.log(date[1]);
      if (date[1] === true) {
        date[1] = false;
        event.target.parentElement.nextElementSibling.style.display = 'none';
      } else {
        date[1] = true;
        event.target.parentElement.nextElementSibling.style.display = 'block';
      }
    },
    isMobile() {
      if (screen.width <= 760) {
        return true;
      } else {
        return false;
      }
    },
    /*
    handlerDelDayActivity: function () {
      this.delButtonActivity = true;
    },
    handlerDelDayActivitySecond: function (data) {
      if (data.id) {
        EventBus.$emit('date--delete', {
          item: data
        });
      }
      this.delButtonActivity = false;
    },
    */
    getWeekSchueler: function (week, date) {

      if (week && date) {
        console.log(week)

        return week[this.$date(date).format('dd').toLowerCase()];
      }
      return false;

    },

    handlerPrintDay: function (date) {

      if (date) {
        window.open('index.php?page=ext_ganztags&view=kalender&task=printDay&day=' + this.$date(date).format('YYYY-MM-DD'), '_blank').focus();
      }


    },
    handlerOpenSchueler: function (data) {

      //console.log(data);

      EventBus.$emit('modal-schueler--open', {
        schueler: data.schueler,
        item: data
      });


    },
    handlerOpenAktivity: function (data) {

      EventBus.$emit('modal-info--open', {
        item: data
      });


    },
    handlerAddLeader: function (date, item) {

      EventBus.$emit('modal-leader--open', {
        'day': this.$date(date).format('dd'),
        'content': this.getDayContent(date),
        'type': item.type,
        'preselect': item.leader_id,
        'callback': function (form) {

          if (form.leader && date && item.id) {
            EventBus.$emit('date--group', {
              'group': item,
              'leader': form.leader,
              'date': this.$date(date).format('YYYY-MM-DD')
            });
            EventBus.$emit('modal-leader--close');
          }

        }
      });


    },
    handlerAddAktivity: function (date) {
      EventBus.$emit('modal-aktivity--open', {
        'callback': function (form) {
          if (form.activity && form.leader && date) {
            EventBus.$emit('date--aktivity', {
              'activity': form.activity,
              'leader': form.leader,
              'date': this.$date(date).format('YYYY-MM-DD')
            });
            EventBus.$emit('modal-aktivity--close');
          }
        }
      });
    },
    getDayContent: function (date) {
      //console.log(this.plan);

      var ret = false;
      if (this.plan && typeof this.plan === 'object') {
        var day = this.$date(date).format('YYYY-MM-DD');
        for (const o in this.plan) {
          if (this.plan[o].date == day) {
            ret = this.plan[o].content;
          }
        }
      }
      return ret;
    },
    isToday: function (day) {
      if (this.today.isSame(day, 'day')) {
        return true;
      }
      return false;
    },
    isFuture: function (day) {
      if (this.today.isBefore(day, 'day')) {
        return true;
      }
      return false;
    },
    subtractWeek: function () {
      this.thisWeek = this.thisWeek.subtract(1, 'week');
      this.changedDate();
    },
    addWeek: function () {
      this.thisWeek = this.thisWeek.add(1, 'week');
      this.changedDate();
    },
    gotoToday: function () {
      this.thisWeek = this.today;
      this.changedDate();
    },
    changedDate: function () {
      EventBus.$emit('calendar--changedDate', {
        von: this.firstDayOfWeek.unix(),
        bis: this.lastDayOfWeek.unix()
      });
    }

  }
}
</script>
