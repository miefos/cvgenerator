<template>
  <div v-for="(row, row_i) in fieldValue" class="my-2 p-4 gap-2 grid bg-blue-100 ml-0 mr-0">
    <template v-for="inner_field in field.inner_fields" >
      <Dropdown :editable="true" class="sm:col-12 lg:col-4 my-2" v-model="row[inner_field.id]" :options="inner_field.options" :placeholder="inner_field.label"/>
    </template>
    <div class="col vertical-align-middle p-0">
      <div class="flex">
        <Button :label="data.translations.removeRow" class="w-auto p-button-text ml-auto" @click="() => removeRow(row_i)"></Button>
      </div>
    </div>
  </div>
  <Button :label="data.translations.addRow" class="p-button-text w-auto" @click="addNewRow"></Button>
</template>

<script>
import {mapState} from "vuex";

export default {
  name: "Languages",
  props: {
    section: Object,
    field: Object,
    data: Object
  },
  computed: {
    ...mapState(['formData']),
    fieldValue() {
      return this.formData[this.section.id][this.field.id];
    }
  },
  methods: {
    addNewRow() {
      this.$store.dispatch('addNewRow', {
        sectionId: this.section.id,
        field: this.field,
        rowValue: {}
      });
    },
    removeRow(i) {
      this.$store.dispatch('removeRow', {
        sectionId: this.section.id,
        field: this.field,
        rowId: i
      });
    }
  },
}
</script>

<script setup>
import Dropdown from 'primevue/dropdown'
import Button from 'primevue/button'
</script>