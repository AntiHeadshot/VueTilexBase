<template>
  <div>
    <div v-if="appointment">
      <h3 v-if="appointment.parts.length>1">gewählte Termine</h3>
      <h3 v-else>gewählter Termin</h3>
      Komplett Speichern in
      <a :href="appointment.url">Kalender-App</a>,
      <br />oder einzeln in
      <div v-for="part in appointment.parts" v-bind:key="part.id">
        <h3 class="coloredText">{{part.fromDate|dateTimeMonth}} - {{part.toDate|time}}</h3>
        <a target="_blank" :href="part.urlGoogle">Google</a>,
        <a target="_blank" :href="part.urlOutlook">Outlook Web</a>,
        <a target="_blank" :href="part.urlYahoo">Yahoo</a> Kalender
      </div>
      <br />
      <h3>Code für diese Seite</h3>
      <qr :size="256" :href="url" />
    </div>
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

    AppointmentService.getWithCalendar(this.token).then((r) => {
      r.parts.map((x) => {
        x.fromDate = new Date(x.fromDate.replace(/\+(\d\d)(\d\d)/, "+$1:$2"));
        x.toDate = new Date(x.toDate.replace(/\+(\d\d)(\d\d)/, "+$1:$2"));
      });

      this.appointment = r;
    });
  },
};
</script>

<style>
</style>