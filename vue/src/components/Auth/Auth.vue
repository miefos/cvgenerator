<template>
  <div id="authform" class="shadow-2 border-round-md p-4">
    <div class="text-xl font-semibold">Login or register</div>
    <form @submit.prevent="() => submit()">
      <Message v-if="form.response" class="" :severity="form.response?.status === 'ok' ? 'success' : 'error'">{{ form.response.msg }}</Message>
      <div class="pt-1">
        <span class="p-float-label my-4">
          <InputText style="width:100%;" id="email" type="email" v-model="form.email" @input="changedEmail"/>
          <label for="email">{{ data.translations.email_label }}</label>
        </span>
        <div v-if="!form.emailVerified">
          <Button type="submit" style="width:100%;"  :label="data.translations.submit_email" />
        </div>
        <div v-else>
          <span class="p-float-label my-4">
            <InputText style="width:100%;" v-model="form.otp" />
            <label for="otp">{{ data.translations.otp_label }}</label>
          </span>
          <Button type="submit" style="width:100%;" :label="data.translations.submit_attempt_email_otp" />
        </div>
        <div v-if="shouldShowWillBeAbleToResendIn">
          <small>{{ data.translations.wait_until_can_be_resent }} {{ secondsUntilResend }}</small>
        </div>
        <div v-else-if="secondsUntilResend <= 1">
          <div @click="() => submit(true)">
            {{ data.translations.resend_label }}
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  props: {data: Object},
  data() {
    return {
      form: {
        [this.data.nonce_name]: this.data.nonce, // assign nonce field
        email: '',
        otp: '',
        response: null,
        emailVerified: false,
      },
      shouldShowWillBeAbleToResendIn: false,
      secondsUntilResend: 10000,
      untilResendCounterId: null
    }
  },
  methods: {
    changedEmail() {
      this.form.emailVerified = false
      this.form.response = null
      this.form.otp = ''
      this.secondsUntilResend = 10000
      this.shouldShowWillBeAbleToResendIn = false
      clearInterval(this.untilResendCounterId)
    },
    submit(resend = false) {
      if (resend) {
        this.submit_email()
        this.secondsUntilResend = 10000
      } else if (this.form.emailVerified) { // email should be verified at this point
        this.submit_otp()
      } else {
        this.submit_email()
      }
    },
    submit_email() {
      const data = this.form

      axios.post(this.data.api.verify_email_and_send_otp, data, { withCredentials: true})
          .then(response => {
            this.form.response = response.data
            if (response.data.status === 'ok') {
              this.form.emailVerified = true
              setTimeout(() => {
                this.shouldShowWillBeAbleToResendIn = true
                this.secondsUntilResend = this.data.waiting_time_until_can_be_resent
                this.untilResendCounterId = setInterval(() => {
                  if (this.secondsUntilResend <= 1 ) {
                    clearInterval(this.untilResendCounterId)
                    this.shouldShowWillBeAbleToResendIn = false
                  }
                  this.secondsUntilResend -= 1
                }, 1000);
              }, this.data.waiting_time_until_info_about_can_be_resent_is_shown * 1000)
            }
          })
          .catch(error => {
            console.log("Error sending OTP");
          });
    },
    submit_otp() {
      const data = this.form

      axios.post(this.data.api.verify_otp, data, {withCredentials: true})
          .then(response => {
            this.form.response = response.data
            if (response.data.status === 'ok') {
              location.reload()
            }
          })
          .catch(error => {
            console.log("Error sending OTP");
          });
    },
  }
}
</script>

<script setup>
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Message from 'primevue/message';
import InputMask from 'primevue/inputmask';
</script>
