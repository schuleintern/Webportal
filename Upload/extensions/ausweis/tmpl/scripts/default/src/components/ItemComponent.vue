<template>
  <div class="">

    <div v-if="antrag[0] || ausweis[0]" class="flex-row flex-space-between">
      <div class="flex-1">
      </div>
      <div class="flex-1 flex-row flex-end">
        <button v-if="acl.write == 1" class="si-btn si-btn-light" @click="handlerForm()"><i class="fa fa-check"></i>
          Neuen Ausweis beantragen</button>
      </div>
    </div>

    <div v-else class="flex-row flex-space-between">
      <button v-if="acl.write == 1" class="si-btn " @click="handlerForm()"><i class="fa fa-check"></i> Ausweis
        beantragen</button>
      <div v-else class="si-hinweis">Sie können leider keinen Ausweis beantragen</div>
    </div>


    <div v-if="ausweis.user">
      <h3>Ausweis</h3>
      <div class="flex-row">
        <div class="flex-row si-box ">
          <div class="flex-1 margin-r-l">
            <img :src="selfURL + '&task=getFile&path=' + ausweis.front_path" width="200" />
          </div>
          <div class="flex-5 margin-r-m">

            <div class="padding-b-s">
              <div class="text-label padding-b-s">Datum</div>
              {{ ausweis.createdTime }}
            </div>
            <div class="padding-b-s">
              <div class="text-label padding-b-s">Name</div>
              {{ ausweis.user.name }}
            </div>
            <div>
              <a v-if="ausweis.state == 1" :href="selfURL + '&task=getFile&path=' + ausweis.front_path" target="_blank"
                class="si-btn"><i class="far fa-file"></i> Öffnen</a>
            </div>
          </div>
        </div>
      </div>
    </div>


    <div v-if="antrag[0]">
      <h3>Anträge</h3>
      <table class="si-table">
        <thead>
          <tr>
            <th></th>
            <th>Datum</th>
            <th>Benutzer*in</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-bind:key="index" v-for="(item, index) in  antrag" class="">

            <td><img :src="selfURL + item.imagePath" width="100" /></td>
            <td width="10%">{{ item.createdTime }}</td>
            <td>{{ item.user.name }}</td>
            <td>
              <button v-if="item.state == 1" class="si-btn si-btn-off text-orange">Offen</button>
              <button v-if="item.state == 2" class="si-btn si-btn-off text-green">Freigegeben</button>
              <button v-if="item.state == 3" class="si-btn si-btn-off text-red">Gesperrt</button>
            </td>

          </tr>
        </tbody>
      </table>

    </div>


  </div>
</template>

<script>

export default {
  name: 'ItemComponent',
  data() {
    return {

      required: '',
      deleteBtn: false,
      selfURL: window.globals.selfURL
    };
  },
  props: {
    acl: Array,
    ausweis: [],
    antrag: Array
  },
  created: function () {

  },
  methods: {


    handlerForm: function () {
      this.$bus.$emit('page--open', {
        page: 'form'
      });
    },


  }


};
</script>

<style></style>