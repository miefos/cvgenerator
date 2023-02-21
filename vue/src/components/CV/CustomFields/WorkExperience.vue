<template>
    <div v-for="(row, row_i) in fieldValue" class="my-2 p-4 grid bg-blue-100">
      <template v-for="(innerField, field_i) in field.inner_fields" >
        <MyField class="col-12" :class="field.extra_type !== 'education' ? (field_i > 3 ? 'lg:col-12' : 'lg:col-6') : 'lg:col-3'" :field="{...field}" :section="{...section}" :innerField="{...innerField}" :innerFieldRowId="row_i" :data="{...data}"/>
      </template>
      <div class="col-3 relative" style="min-height: 40px;">
        <Button :label="data.translations.removeRow" class="w-auto p-button-text absolute" style="bottom: 8px;" @click="() => removeRow(row_i)"></Button>
      </div>
    </div>
  <Button :label="data.translations.addRow" class="p-button-text w-auto" @click="addNewRow"></Button>
</template>

<script>
import {mapState} from "vuex";

export default {
  name: "WorkExperience",
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