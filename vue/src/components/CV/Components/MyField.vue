<template>
  <!-- Text -->
  <div v-if="getFieldOrInnerFieldType === 'text'">
    {{ getFieldOrInnerFieldLabel }}<span v-if="field.validation?.includes('required')" class="text-red-600">*</span>
    <InputText v-model="fieldValue" @input="(e) => updateFieldValue(e.target.value)"/>
    <div v-if="errors.hasOwnProperty(field.id)" class="text-red-600">{{ errors[field.id] }}</div>
  </div>
  <!-- Textarea -->
  <div v-else-if="getFieldOrInnerFieldType === 'textarea'">
    {{ getFieldOrInnerFieldLabel }}<span v-if="field.validation?.includes('required')" class="text-red-600">*</span>
    <Textarea v-model="fieldValue" @input="(e) => updateFieldValue(e.target.value)"/>
    <div v-if="errors.hasOwnProperty(field.id)" class="text-red-600">{{ errors[field.id] }}</div>
  </div>
  <!-- Tel -->
  <div v-else-if="getFieldOrInnerFieldType === 'tel'">
    {{ getFieldOrInnerFieldLabel }}<span v-if="field.validation?.includes('required')" class="text-red-600">*</span>
    <InputText v-model="fieldValue" @input="(e) => updateFieldValue(e.target.value)"/>
    <div v-if="errors.hasOwnProperty(field.id)" class="text-red-600">{{ errors[field.id] }}</div>
  </div>
  <!-- Select -->
  <div v-else-if="getFieldOrInnerFieldType === 'select'">
    {{ getFieldOrInnerFieldLabel }}<span v-if="field.validation?.includes('required')" class="text-red-600">*</span>
    <Dropdown v-model="fieldValue" :options="field.options" @change="(e) => updateFieldValue(e.target.value)"/>
    <div v-if="errors.hasOwnProperty(field.id)" class="text-red-600">{{ errors[field.id] }}</div>
  </div>
  <!-- Date  -->
  <template v-else-if="getFieldOrInnerFieldType === 'monthyear'">
    <div v-show="shouldShowFieldDueDependance">
      {{ getFieldOrInnerFieldLabel }}<span v-if="field.validation?.includes('required')" class="text-red-600">*</span>
      <Calendar view="month" dateFormat="mm/yy" v-model="monthyear" @date-select="updateFieldValue"/>
      <div v-if="errors.hasOwnProperty(field.id)" class="text-red-600">{{ errors[field.id] }}</div>
    </div>
  </template>
  <!-- Yes No field  -->
  <div v-else-if="getFieldOrInnerFieldType === 'yesno'">
    {{ getFieldOrInnerFieldLabel }}<span v-if="field.validation?.includes('required')" class="text-red-600">*</span>
    <div>
      <InputSwitch v-model="fieldValue" :trueValue="1" :falseValue="0" @input="(e) => updateFieldValue(e)"/>
    </div>
    <div v-if="errors.hasOwnProperty(field.id)" class="text-red-600">{{ errors[field.id] }}</div>
  </div>
  <!-- Video field  -->
  <div v-else-if="getFieldOrInnerFieldType === 'video'">
    {{ getFieldOrInnerFieldLabel }}<span v-if="field.validation?.includes('required')" class="text-red-600">*</span>
    <div>
      <Video v-model="fieldValue" :field="{...field}" :section="section" :data="{...data}" ></Video>
    </div>
    <div v-if="errors.hasOwnProperty(field.id)" class="text-red-600">{{ errors[field.id] }}</div>
  </div>
  <!-- Custom  -->
  <!-- TODO add validation -->
  <div v-else-if="getFieldOrInnerFieldType === 'json'">
    <Languages v-if="field.extra_type === 'languages'" :field="{...field}" :section="section" :data="{...data}" />
    <WorkExperience v-else-if="field.extra_type === 'work_experience'" :field="{...field}" :section="section" :data="{...data}" />
    <Education v-else-if="field.extra_type === 'education'" :field="{...field}" :section="section" :data="{...data}" />
  </div>
  <!-- Default  -->
  <div v-else>
    {{ field }}
  </div>
</template>
<script>
import dayjs from 'dayjs'
import {mapState} from 'vuex'

export default {
  name: "MyField",
  props: {
    field: Object,
    innerField: {type: Object, default: null},
    innerFieldRowId: {default: null},
    section: Object,
    data: Object,
  },
  created() {
    if (this.getFieldOrInnerFieldType === 'monthyear') {
      this.monthyear = this.convertToMonthYear(this.fieldValue)
    }
  },
  computed: {
    ...mapState(['formData', 'errors']),
    fieldValue() {
      if (this.innerField) {
        return this.formData[this.section.id][this.field.id][this.innerFieldRowId][this.innerField.id];
      } else {
        return this.formData[this.section.id][this.field.id];
      }
    },
    getFieldOrInnerField() {
      return this.innerField? this.innerField : this.field;
    },
    getFieldOrInnerFieldType() {
      return this.innerField? this.innerField.type : this.field.type;
    },
    getFieldOrInnerFieldLabel() {
      return this.innerField? this.innerField.label : this.field.label;
    },
    shouldShowFieldDueDependance() {
      const depends_on = this.getFieldOrInnerField['depends_on']
      if (!depends_on) return true
      let result
      depends_on.forEach(depend_on => {
        const field = depend_on[0]
        const operator = depend_on[1]
        const value = depend_on[2]
        const formDataSectionProxy = this.formData[this.section.id][this.field.id]
        let fieldValue
        if (this.innerFieldRowId !== null) {
          fieldValue = formDataSectionProxy[this.innerFieldRowId]?.[field]
        } else {
          fieldValue = formDataSectionProxy[field]
        }
        switch (operator) {
          case '=': {
            result = fieldValue === value || null == fieldValue
            break;
          }
          default: {
            result = true
            break;
          }
        }
        if (result !== null) {
          return false
        }
      })

      return result
    },
  },
  methods: {
    updateFieldValue(newValue) {
      console.log(newValue)
      this.$store.dispatch('updateFormData', {
        sectionId: this.section.id,
        field: this.field,
        innerField: this.innerField,
        innerFieldRowId: this.innerFieldRowId,
        value: newValue
      });
    },
    convertToMonthYear(date) {
      return dayjs(date).format('MM/YYYY')
    }
  },
  data() {
    return {
      monthyear: ''
    }
  }
}
</script>

<script setup>
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Languages from "@/components/CV/CustomFields/Languages.vue";
import WorkExperience from "@/components/CV/CustomFields/WorkExperience.vue";
import InputSwitch from 'primevue/inputswitch';
import Video from "@/components/CV/CustomFields/Video.vue";
import Education from "@/components/CV/CustomFields/Education.vue";
</script>
