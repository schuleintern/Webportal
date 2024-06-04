<template>

  <div>

    <ul class="noListStyle" v-if="data.counts.label >= 1">

      <li  v-bind:key="index" v-for="(item, index) in  data.counts" class="padding-t-m padding-b-m">
        <h4>Sanktion {{ item.count }}:</h4>

        <div class="flex-row padding-l-l padding-r-l">
          <div v-if="item.createDate" class="flex-1 padding-r-l">
            <label class="">Erstellt:</label>
            <div class="padding-b-m"><i class="fa fa-clock"></i> {{ item.createDate }}</div>
            <User v-if="item.createByUser" :data="item.createByUser"></User>
            <div v-if="item.createInfo" class="si-hinweis">{{ item.createInfo }}</div>
          </div>

          <div v-if="item.doneDate" class="flex-1 padding-l-l">
            <label class="">Beendet:</label>
            <div class="padding-b-m"><i class="fa fa-clock"></i> {{ item.doneDate }}</div>
            <User v-if="item.doneByUser" :data="item.doneByUser"></User>
            <div v-if="item.doneInfo" class="si-hinweis">{{ item.doneInfo }}</div>
          </div>
        </div>

      </li>
    </ul>
    <div v-else >- keine Vorkommnisse -</div>

  </div>
</template>

<script>


export default {

  name: 'App',
  components: {},
  data() {
    return {
      apiURL: window.globals.apiURL,
      acl: window.globals.acl,
      count: window.globals.count,
      data: JSON.parse(window.globals.data)

    };
  },
  created() {


  },
  methods: {}
}
</script>

<style>

</style>
