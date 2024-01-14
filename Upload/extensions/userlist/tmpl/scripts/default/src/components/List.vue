<template>

  <div class="">

    <button v-if="acl.write == 1" class="si-btn" v-on:click="handlerOpenForm()"><i class="fa fa-plus"></i> Hinzufügen</button>


    <table class="si-table si-table-style-allLeft">
      <thead>
        <tr>
          <th width="10%" >Favorite</th>
          <th width="20%">Titel</th>
          <th>Mitglieder</th>
          <th>Teilen</th>
        </tr>
      </thead>
      <tbody>
        <tr v-bind:key="index" v-for="(item, index) in  items">
          <td class="">
            <button v-if="item.favorite == 1" v-on:click="handlerSetFavorite(item)" class="si-btn si-btn-toggle-on si-btn-icon" ><i class="fa fas fa-star"></i></button>
            <button v-else v-on:click="handlerSetFavorite(item)" class="si-btn si-btn-toggle-off si-btn-icon" ><i class="fa fas fa-star"></i></button>
          </td>
          <td class="padding-l-m">
            <a :href="'#'+item.id" v-on:click="handlerOpenItem(item)">{{item.title}}</a>
          </td>
          <td class="">
            <button class="si-btn si-btn-off text-bold">{{item.stats.count}}</button>
            <span class="si-btn-multiple">
              <button class="si-btn si-btn-off">{{item.stats.isPupil}} Schüler </button>
              <button class="si-btn si-btn-off">{{item.stats.isEltern}} Eltern</button>
              <button class="si-btn si-btn-off">{{item.stats.isTeacher}} Lehrer</button>
              <button class="si-btn si-btn-off">{{item.stats.isNone}} Sonstige</button>
            </span>
          </td>
          <td>
            <button v-if="item.owners.length" class="si-btn si-btn-off"><i class="fa fa-share-alt"></i> {{item.owners.length}}</button>
          </td>
        </tr>
      </tbody>
    </table>


  </div>

</template>


<script>

export default {
  components: {
  },
  name: 'List',
  props: {
    items: Array,
    acl: Object
  },
  data(){
    return {
    }
  },
  created: function () {
  },
  mounted() {
  },
  methods: {
    handlerSetFavorite: function (item) {
      if (item) {
        EventBus.$emit('item--favorite', {
          item: item
        });
      }
    },
    handlerOpenForm: function () {
      EventBus.$emit('form--open');
    },
    handlerOpenItem: function (item) {
      if (item) {
        EventBus.$emit('item--open', {
          item: item
        });
      }
    }

  }
}
</script>
