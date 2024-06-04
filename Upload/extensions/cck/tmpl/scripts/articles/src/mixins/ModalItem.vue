<template>
  <div class="si-modal" v-if="open" v-on:click.self="handlerClose">

    <div class="si-modal-box" >
      <button class="si-modal-btn-close" v-on:click="handlerClose"></button>
      <div class="si-modal-content">


        <ul>
          <li v-bind:key="index" v-for="(item, index) in article.fields">
            {{item}}
          </li>
        </ul>

        <div>
          {{article.template}}
        </div>



      </div>
    </div>

  </div>
</template>n

<script>


export default {

  components: {

  },
  data() {
    return {
      open: false,
      item: false
    };
  },
  props: {
    article: Object | Boolean
  },
  created: function () {

    var that = this;
    EventBus.$on('modal-item--open', data => {
      that.item = data.item;
      that.open = true;

      EventBus.$emit('modal-content--get', {
        article_id: that.item.id
      });

    });

    EventBus.$on('modal-item--close', data => {
      that.open = false;
    });

  },
  methods: {

    handlerClose: function () {
      EventBus.$emit('modal-item--close');
    }

  }


};
</script>

<style>

</style>