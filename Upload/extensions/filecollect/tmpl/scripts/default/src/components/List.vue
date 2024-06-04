<template>

  <div v-if="items.length >= 1" class="">

    <div v-bind:key="index" v-for="(item, index) in  items" class="margin-b-l">
      <div class="flex-row si-box">
        <div class="flex-1">
          <h4 v-if="item.done != true" class="text-green">Noch {{ item.endDateNow }}</h4>
          <h3>{{ item.title }}</h3>
          <h4>{{ item.c_title }}</h4>
          <div v-if="item.c_info" class="si-hinweis margin-r-l">{{ item.c_info }}</div>
          <div v-if="item.info" class="si-hinweis margin-r-l">{{ item.info }}</div>
        </div>
        <div class="flex-1 margin-l-l padding-t-l">
          <div><label>Bis:</label> {{ item.endDate }}</div>
          <div v-if="item.done != true"><label>Mögliche Daten:</label> {{ item.anzahl }}</div>
          <div v-else><label>Status:</label> Fertig</div>
          <div v-show="item.files.length <= item.anzahl" class="padding-t-m">
            <button class="si-btn" v-on:click="handlerOpen($event)"><i class="fa fa-upload"></i> Hochladen</button>
            <input ref="file" v-on:change="handleFileUpload(index, item.id, item.anzahl)" multiple="true" type="file"
                   style="opacity: 0;">
          </div>
          <div v-show="item.files.length > 0" class="padding-t-m">
            <label>Daten:</label>

            <div v-bind:key="i" v-for="(file, i) in  item.files" class="">

              <div class="si-box flex-row">
                <div class="flex-1"><i class="fa fa-file margin-r-s"></i> {{ file.filename }}</div>
                <div class=" flex-1 text-right text-small">{{ file.time }}</div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
  <div v-else>
    <div class="si-hinweis ">Keine Uploads erforderlich.</div>
  </div>

</template>


<script>

import {ref} from "vue"
import {getCurrentInstance} from 'vue'

export default {
  components: {},
  name: 'List',
  props: {
    items: Array,
    acl: Object
  },
  setup() {
    const file = ref('file')
    const app = getCurrentInstance()

    const handleFileUpload = async (index, folderid, maxfiles) => {
      // debugger;
      //this.file = this.$refs.file[0];
      console.log(index, file.value);
      //console.log("selected file", file.value.files)
      //Upload to server

      app.appContext.config.globalProperties.$bus.$emit('form--upload', {
        files: file.value[index].files,
        folderid: folderid,
        maxfiles: maxfiles
      });


    }

    return {
      handleFileUpload,
      file
    }
  },

  created: function () {

  },
  mounted() {
  },

  methods: {

    handlerOpen: (e) => {
      e.target.nextSibling.click();
    }
  }
}
</script>
