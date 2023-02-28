<template>
  <div class="flex flex-wrap row-gap-4">
      <div class="shadow-2 border-round p-4 min-w-full">
        <div class="text-3xl font-medium text-900 mb-2">CV Preview</div>
        <div class="">
          PDF
        </div>
      </div>
      <div class="surface-card shadow-2 border-round p-4 min-w-full">
        <div class="text-3xl font-medium text-900 mb-2">Access status</div>
        <div v-if="paid" class="">
          You have paid for the access to the CV until Feb 26, 16:35
        </div>
        <div v-else class="">
          <p>You do not have paid for the access to the CV.</p>
          <p>You can buy the access to the CV for 29.99 EUR.</p>
          <div>
            <Button v-if="!paid" class="mt-2" label="Buy" @click="pay" icon="pi pi-credit-card" />
          </div>
        </div>
      </div>
  </div>
</template>

<script>
export default {
  name: "Home",
  props: {data: {type: Object, required: true}},
  data () {
    return {
      paid: false
    }
  },
  methods: {
    pay () {
      console.log(this.data.api.payment_redirect)
      window.location.replace(this.data.api.payment_redirect);
      this.paid = true
    }
  }
}
</script>

<script setup>
import Button from "primevue/button";
</script>