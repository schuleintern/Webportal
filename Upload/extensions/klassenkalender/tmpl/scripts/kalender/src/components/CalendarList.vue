<template>

  <div v-if="isMobile" class="flex-row margin-b-s">
    <div class="flex-1 ">
      <button  class="si-btn si-btn-green" @click="handlerShowKalenders" ><i class="fa fa-calendar"></i> {{selected.length}} Kalender</button>
    </div>
    <div class="si-btn-multiple padding-t-m padding-b-m" v-if="showKalendar">
      <button v-bind:key="index" v-for="(item, index) in  kalenders"
              class="si-btn si-btn-light margin-r-s"
              :style="styleButton(item.id, item.color)"
              @click="handlerSelect($event, item)"
      > {{ item.title }}
      </button>
    </div>
  </div>

  <div v-else class="padding-b-m flex-row ">

    <div class="flex-1 ">
      <div class="si-btn-multiple blockInline">
        <button v-bind:key="index" v-for="(item, index) in  kalenders"
                class="si-btn si-btn-light margin-r-s"
                :style="styleButton(item.id, item.color)"
                @click="handlerSelect($event, item)"
        > {{ item.title }}
        </button>

      </div>



    </div>
  </div>

</template>

<script>

export default {
  name: 'CalendarList',
  data() {
    return {
      isMobile: window.globals.isMobile,
      selected: [],
      showKalendar: false
    };
  },
  props: {
    kalenders: Array,
    selectedKalenders: Array,
    suggest: Boolean,
    ics: Boolean,
    acl:Object
  },
  created: function () {

    this.$bus.$on('kalenders--preSelected', (data) => {
      this.selected = data.selected;
    });

  },
  methods: {


    handlerShowKalenders() {
      this.showKalendar = !this.showKalendar;
    },
    styleButton(id, color) {

      if (this.isSelected(id) === true) {
        if (color) {
          return {
            backgroundColor: color,
            borderColor: color,
            color: this.pickTextColorBasedOnBgColorSimple(color, '#fff', '#000')
          };
        } else {
          return {
            backgroundColor: '#FF851B',
            borderColor: '#FF851B'
          }
        }

      } else {
        return {borderLeft: '5px solid ' + color};
      }
    },
    isSelected(id) {
      return this.selected.includes(parseInt(id));
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
      return (L > 0.4) ? darkColor : lightColor;
    },

    handlerSelect($event, item) {

      if (item.id) {
        let index = this.selected.indexOf(parseInt(item.id));
        if (index >= 0) {
          this.selected.splice(index, 1);
          //$event.target.classList.remove('si-btn-active');
        } else {
          this.selected.push(parseInt(item.id));
          //$event.target.classList.add('si-btn-active');
        }
      }
      this.$bus.$emit('kalenders--selected', {
        selected: this.selected
      });

    }


  }


};
</script>

<style>

</style>