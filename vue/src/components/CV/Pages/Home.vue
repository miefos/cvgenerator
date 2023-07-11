<template>
  <div class="m-2 lg:flex gap-2">
    <div class="shadow-1 p-4 lg:flex-auto lg:justify-content-center">
      <div class="text-3xl font-medium text-900 mb-2">{{ data.translations.accessStatus }}</div>
      <div v-if="leftPaidMinutes" class="">
        <div>
          {{ data.translations.youHavePaidUntil }} {{ addMinutesToCurrentTime(leftPaidMinutes) }}
        </div>
        <a target="_blank" :href="data.api.download_cv">
          <Button :label="data.translations.downloadCV" icon="pi pi-download" />
        </a>
      </div>
      <div v-else class="">
        <p>{{ data.translations.youHaveNotPaid }}</p>
        <p>{{ data.translations.youCanBuyTheAccessToTheCVFor }}</p>
        <div>
          <Button class="mt-2" :label="data.translations.buy" @click="pay" icon="pi pi-credit-card" />
        </div>
      </div>
    </div>
    <div class="shadow-1 p-4 lg:flex-auto lg:justify-content-center">
      <div class="text-3xl font-medium text-900 mb-2">{{ data.translations.cvPreview }}</div>
      <div style="width: 500px; height: 300px; overflow-y: scroll; max-width: 100%;">
        <Image :src="data.api.get_cv_preview" alt="Image" width="500" />
      </div>
      <Message class="scalein animation-ease-in-out animation-duration-1000" v-if="!data.left_minutes_for_payment" severity="info">{{ data.translations.youHaveNotPaid }}</Message>
    </div>
  </div>
</template>

<script>
import dayjs from "dayjs";
import {mapState} from "vuex";

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
import Image from "primevue/image";
</script>