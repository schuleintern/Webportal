<template>
  <div>
    <div v-bind:key="index" v-for="(obj, index) in  data" class="si-box ">
      <div class="flex-row padding-l">
        <div class="flex-4 padding-l-l">
          <div class="text-big-2">
            {{ obj.vorname }} {{ obj.nachname }}
          </div>
          <div>
            {{ obj.strasse }} {{ obj.nummer }}
          </div>
          <div>
            {{ obj.plz }} {{ obj.ort }}
          </div>
          <div class="margin-t-m text-small">
            <div class="text-grey">Briefkopf:</div>
            <div v-html="obj.anschrift"></div>
          </div>
          <div class="margin-t-m text-small">
            <div v-bind:key="it" v-for="(telefon, it) in  obj.telefon" class="">
              <div class="si-btn-multiple">
                <div class="si-btn si-btn-off">{{ telefon.typAsText }}:</div>
                <a class="si-btn si-btn-border text-black" :href="'tel: '+telefon.nummer">{{ telefon.nummer }}</a>
              </div>
            </div>
          </div>

        </div>
        <div class="flex-1">
          <div>
            <span v-if="obj.isErziehungsberechtigter" class="si-btn si-btn-small si-btn-border"><i
                class="fa fa-user"></i>
              Erziehungsberechtigter
            </span>
            <span v-if="obj.isWeitererErziehungsberechtigter" class="si-btn si-btn-small si-btn-border"><i
                class="fa fa-user"></i>
              Weiterer Erziehungsberechtigter
            </span>
            <span v-if="obj.isSchueler" class="si-btn si-btn-small si-btn-border"><i
                class="fa fa-user"></i> Schüler</span>
            <span v-if="obj.isWeiterer" class="si-btn si-btn-small si-btn-border"><i class="fa fa-check"></i> Weitere Adresse</span>
            <span v-if="obj.isAuskunftsberechtigt" class="si-btn si-btn-small si-btn-border"><i class="fa fa-check"></i>
              Auskunftsberechtigt
            </span>
            <span v-if="obj.isHauptansprechpartner" class="si-btn si-btn-small  si-btn-green"><i
                class="fa fa-check"></i>
              Hauptansprechpartner
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  components: {},
  data() {
    return {};
  },
  props: {
    data: Object
  },
  created: function () {
  },
  methods: {}
};
</script>