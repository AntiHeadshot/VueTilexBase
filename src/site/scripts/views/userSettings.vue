<template>
  <div>
    <h3>Google Kalender</h3>
    <div v-if="shared.user.isConnected">
      <h4 class="coloredText">Erfolgreich verknüpft.</h4>
      <table style="display:inline-block; border-spacing: 10px 0;">
        <tr>
          <th align="right">Name</th>
          <th>aktiv</th>
          <th>primär</th>
        </tr>
        <tr v-for="calendar in calendars" :key="calendar.id">
          <td align="right">{{calendar.name}}</td>
          <td>
            <checkbox :checked="calendar.isActive" v-on:click.native="toggleActive(calendar)" />
          </td>
          <td>
            <checkbox :checked="calendar.isPrimary" v-on:click.native="setPrimary(calendar)" />
          </td>
        </tr>
      </table>
      <br />
      <a class="button" v-on:click="update">Kalender aktualisieren</a>
      <br />
      <a class="button" v-on:click="disconnect">Kalender trennen</a>
    </div>
    <div v-else>
      <a class="button" :href="connectUrl">Kalender verknüpfen</a>
    </div>
    <br />
    <h3>Password / E-Mail ändern</h3>
    <form action="api/v1.0/user/register" method="POST">
      <div class="inputwraper" placeholder="aktuelles Passwort">
        <input type="password" name="password" v-model="passwordOld" placeholder=" " />
      </div>
      <br />
      <div class="inputwraper" placeholder="Benutzername">
        <input type="text" autocomplete="off" v-model="name" placeholder=" " />
      </div>
      <div class="inputwraper" placeholder="E-Mail">
        <input type="email" autocomplete="off" v-model="email" placeholder=" " />
      </div>
      <br />
      <div class="inputwraper" placeholder="neues Passwort">
        <input
          type="password"
          autocomplete="off"
          v-model="passwordNew"
          name="passwordNew"
          placeholder=" "
        />
      </div>
      <div class="inputwraper" placeholder="neues Passwort">
        <input
          type="password"
          autocomplete="off"
          v-model="passwordNew2"
          name="passwordNew"
          placeholder=" "
        />
      </div>
      <br />
      <a type="submit" name="submit" class="button" v-on:click="updateUser">Ändern</a>
    </form>
  </div>
</template>

<script>
import shared from "../shared";
import { CalendarService } from "../services/calendarService";
import { UserService } from "../services/userService";

export default {
  data() {
    return {
      connectUrl: "",
      calendars: [],
      shared,
      email: shared.user.email,
      name: shared.user.name,
      passwordOld: "",
      passwordNew: "",
      passwordNew2: "",
    };
  },
  created() {
    CalendarService.connect().then((r) => (this.connectUrl = r));
    CalendarService.get().then((r) => (this.calendars = r));
  },
  methods: {
    toggleActive(calendar) {
      CalendarService.set(calendar.id, !calendar.isActive, null).then((r) => {
        calendar.isActive = !calendar.isActive;
      });
    },
    setPrimary(calendar) {
      CalendarService.set(calendar.id, null, true).then((r) => {
        for (const c of this.calendars) {
          c.isPrimary = false;
        }
        calendar.isPrimary = true;
      });
    },
    disconnect() {
      CalendarService.remove().then(() => {
        CalendarService.connect().then((r) => (this.connectUrl = r));
        shared.user.isConnected = false;
      });
    },
    update() {
      CalendarService.update().then(() => {
        CalendarService.get().then((r) => (this.calendars = r));
      });
    },
    updateUser() {
      if (this.passwordNew != this.passwordNew2) {
        this.shared.error = "Die neuen Passwörter stimmen nicht überein.";
        return;
      }
      UserService.update(
        this.passwordOld,
        this.email,
        this.name,
        this.passwordNew
      ).then((r) => {
        this.shared.message = "Accountdaten gespeichert.";
      });
    },
  },
};
</script>
<style>
</style>