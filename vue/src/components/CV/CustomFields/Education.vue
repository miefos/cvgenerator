<template>
    <div v-for="(row, row_i) in fieldValue" class="my-2 p-4 grid bg-blue-100 ml-0 mr-0">
      <div class="col-12 flex">
        <Button :label="data.translations.removeRow" class="w-auto p-button-text ml-auto" @click="() => removeRow(row_i)"></Button>
      </div>
      <template v-for="(innerField, field_i) in field.inner_fields" >
        <MyField class="col-12" :class="field_i > 1 ? 'lg:col-6' : ''" :field="{...field}" :section="{...section}" :innerField="{...innerField}" :innerFieldRowId="row_i" :data="{...data}"/>
      </template>
    </div>
  <Button :label="data.translations.addRow" class="p-button-text w-auto" @click="addNewRow"></Button>
</template>

<script>
import {mapState} from "vuex";

export default {
  name: "Education",
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
import Calendar from 'primevue/calendar';
import MyField from "@/components/CV/Components/MyField.vue";
</script>