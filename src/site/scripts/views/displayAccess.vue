<template>
  <div>
    <h3>neuer Zugang erstellt</h3>
    <div v-if="appointment">
      <span v-for="part in appointment.parts" :key="part.id">{{part.duration}}h&nbsp;</span>
    </div>
    <br />
    <qr :href="url" :size="256"></qr>
    <br />
    <a :href="url">Teile diesen Link</a>
    oder nutze
    <br />
    <a
      :href="'whatsapp://send?text=bitte%20w%C3%A4hle%20hier%20%3E%20'+encodeURIComponent(url)+'%20%3C%20noch%20einen%20passenden%20Termin%20aus%F0%9F%98%89.'"
    >Whatsapp</a>
    oder
    <br />
    <a
      :href="'mailto:?subject=Terminwahl&body=Hallo%2C%0D%0A%0D%0Abitte%20w%C3%A4hle%20hier%20%3E%20'+encodeURIComponent(url)+'%20%3C%20noch%20einen%20passenden%20Termin%20aus%F0%9F%98%89.%0D%0A%0D%0ALiebe%20Gr%C3%BC%C3%9Fe'"
    >E-Mail</a>
  </div>
</template>

<script>
import shared from "../shared";
import { AppointmentService } from "../services/appointmentService";

export default {
  data() {
    return {
      shared,
      token: "",
      url: "",
      appointment: null,
    };
  },
  created() {
    this.token = this.$route.query.token;
    this.url =
      shared.baseUrl +
      "calendarSelection?token=" +
      encodeURIComponent(this.token);

    AppointmentService.get(this.token).then((r) => {
      this.appointment = r;
    });
  },
};
</script>

<style>
</style>