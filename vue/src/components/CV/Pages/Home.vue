<template>
  <div class="flex flex-wrap row-gap-4">
    <div class="surface-card shadow-2 border-round p-4 min-w-full">
      <div class="text-3xl font-medium text-900 mb-2">{{ data.translations.accessStatus }}</div>
      <div v-if="leftPaidMinutes" class="">
        {{ data.translations.youHavePaidUntil }} {{ addMinutesToCurrentTime(leftPaidMinutes) }}
      </div>
      <div v-else class="">
        <p>{{ data.translations.youHaveNotPaid }}</p>
        <p>{{ data.translations.youCanBuyTheAccessToTheCVFor }}</p>
        <div>
          <Button v-if="!leftPaidMinutes" class="mt-2" :label="data.translations.buy" @click="pay" icon="pi pi-credit-card" />
        </div>
      </div>
    </div>
      <div class="shadow-2 border-round p-4 min-w-full">
        <div class="text-3xl font-medium text-900 mb-2">{{ data.translations.cvPreview }}</div>
        <div class="">
          PDF
        </div>
      </div>
  </div>
</template>

<script>
import dayjs from "dayjs";

export default {
  name: "Home",
  props: {data: {type: Object, required: true}},
  computed: {
    leftPaidMinutes() {
      return this.data.left_minutes_for_payment;
    },
    price() {
      return Number.parseFloat(this.data.stripe_product_1_price)
    },
    hours() {
      return Number.parseInt(this.data.stripe_product_length_in_hours)
    }
  },
  data () {
    return {
      // paid: false
    }
  },
  methods: {
    pay () {
      window.location.replace(this.data.api.payment_redirect);
    },
    addMinutesToCurrentTime(minutes) {
      const now = dayjs();
      const future = now.add(minutes, 'minute');
      return future.format('YYYY-MM-DD HH:mm');
    }
  }
}
</script>

<script setup>
import Button from "primevue/button";
</script>