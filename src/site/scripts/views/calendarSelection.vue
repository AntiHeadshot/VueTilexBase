<template>
  <div>
    <h3>Terminwahl</h3>
    <div class="calendar">
      <table>
        <tr>
          <td colspan="7">
            <a class="monthSwitchL" v-on:click="changeMonth(-1)">{{(month+11)%12 |month}}</a>
            <div class="month">
              {{year}}
              <br />
              {{month|month}}
            </div>
            <a class="monthSwitchR" v-on:click="changeMonth(1)">{{(month+1)%12 |month}}</a>
          </td>
        </tr>
        <tr>
          <th>Mo</th>
          <th>Di</th>
          <th>Mi</th>
          <th>Do</th>
          <th>Fr</th>
          <th>Sa</th>
          <th>So</th>
        </tr>
        <tr v-for="i in Math.ceil(days.length / 7)" v-bind:key="i">
          <template v-for="day in days.slice((i - 1) * 7, i * 7)">
            <td v-if="day<0" v-bind:key="day"></td>
            <td
              v-else
              :class="['availability'+day.availability, day.selected?'selected':'']"
              class="day"
              v-bind:key="day.day.getTime()"
            >
              <div>
                <a v-on:click="selectDay(day)">{{day.day.getDate()}}</a>
              </div>
            </td>
          </template>
        </tr>
      </table>

      <br />

      <div v-if="shared.appointment">
        <h4>Termine:</h4>
        <p
          v-for="part in shared.appointment.parts"
          v-bind:key="part.id"
        >1x {{part.duration}} Stunden: {{part.fromDate|dateTime}} - {{part.toDate|time}}</p>
      </div>
      <a class="button" v-on:click="send()">Abschicken</a>

      <br />
      <table id="legend">
        <tr>
          <td colspan="2">Legende</td>
        </tr>
        <tr>
          <td class="day available">
            <div>
              <a>7</a>
            </div>
          </td>
          <td>komplett frei</td>
        </tr>
        <tr>
          <td class="day partial">
            <div>
              <a>7</a>
            </div>
          </td>
          <td>zu Teilen frei</td>
        </tr>
        <tr>
          <td class="day full">
            <div>
              <a>7</a>
            </div>
          </td>
          <td>voll</td>
        </tr>
        <tr>
          <td class="day blocked">
            <div>
              <a>7</a>
            </div>
          </td>
          <td>geblockt</td>
        </tr>
      </table>
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
      days: [],
      year: null,
      month: null,
      newYear: new Date().getFullYear(),
      newMonth: new Date().getMonth(),
    };
  },
  created() {
    this.token = this.$route.query.token;
    this.url =
      shared.baseUrl +
      "calendarSelection?token=" +
      encodeURIComponent(this.token);
    if (
      this.shared.appointment == null ||
      this.shared.appointment.token != this.token
    )
      AppointmentService.get(this.token).then((r) => {
        this.shared.appointment = r;
        if (this.shared.appointment.isTaken)
          this.$router.push({
            path: "/calendarDisplay",
            query: this.$route.query,
          });
        this.updateDays();
      });
    else {
      if (this.shared.appointment.isTaken)
        this.$router.push({
          path: "/calendarDisplay",
          query: this.$route.query,
        });
      this.updateDays();
    }
  },
  methods: {
    changeMonth(val) {
      let newMonth = this.month + val;
      let newYear = this.year;
      if (newMonth < 0) {
        newYear = this.year - 1;
        newMonth = 11;
      } else if (newMonth > 11) {
        newYear = this.year + 1;
        newMonth = 1;
      }
      this.newYear = newYear;
      this.newMonth = newMonth;

      this.updateDays();
    },
    updateDays() {
      if (this.$route.query.month) {
        let date = new Date(Number(this.$route.query.month));
        console.log(date);

        this.newYear = date.getFullYear();
        this.newMonth = date.getMonth();
        this.$route.query.month = undefined;
      }

      AppointmentService.getDays(
        this.token,
        this.newYear,
        this.newMonth + 1
      ).then((r) => {
        let days = r;

        for (let day of days) {
          day.day = new Date(day.day.replace(/\+(\d\d)(\d\d)/, "+$1:$2"));
          day.selected = shared.appointment.parts.some(
            (x) =>
              x.fromDate != null &&
              new Date(x.fromDate.getTime()).setHours(0, 0, 0, 0) ==
                day.day.getTime()
          );
        }

        let offset = (days[0].day.getDay() + 6) % 7; // make Monday 0

        while (offset > 0) {
          days.unshift(-offset);
          offset--;
        }

        this.days = days;
        this.month = this.newMonth;
        this.newMonth = null;
        this.year = this.newYear;
        this.newYear = null;
      });
    },
    selectDay(day) {
      if (day.availability < 3) {
        this.shared.day = day;
        this.$router.push({ path: "/daySelection", query: this.$route.query });
      }
    },
    send() {
      if (this.shared.appointment.parts.some((x) => x.fromDate == null)) {
        shared.error = "Nicht alle Termine wurden gewählt.";
        return;
      }
      let parts = this.shared.appointment.parts
        .slice()
        .sort((a, b) => a.fromDate > b.fromDate);
      let last = 0;
      for (let part of parts) {
        if (part.duration < last) {
          shared.error = "Ein kürzerer Termin liegt nach einem längeren.";
          return;
        }
        last = part.duration;
      }

      AppointmentService.setAppointment(
        this.token,
        this.shared.appointment.parts
          .sort((a, b) =>
            a.duration === b.duration
              ? a.fromDate < b.fromDate
                ? -1
                : 1
              : a.duration < b.duration
              ? -1
              : 1
          )
          .map((x) => {
            return {
              from: x.fromDate.toISOString(),
              to: x.toDate.toISOString(),
            };
          }),
        (e) => (shared.error = e.message)
      ).then((r) => {
        if (r) {
          console.log(r);
          if (r) shared.appointment.r;
          this.$router.push({
            path: "/calendarDisplay",
            query: this.$route.query,
          });
        }
      });
    },
  },
};
</script>

<style>
.calendar {
  display: inline-block;
}

.calendar {
  display: inline-block;
}

.calendar .month {
  font-weight: bold;
  display: inline-block;
}

.calendar .monthSwitchL,
.calendar .monthSwitchR {
  position: absolute;
  display: inline-block;
  bottom: 0;
}

.calendar .monthSwitchL {
  left: 0;
}

.calendar .monthSwitchR {
  right: 0;
}

.calendar table {
  border-spacing: 6px;
}

.calendar a {
  cursor: pointer;
}

.calendar td {
  position: relative;
}

.calendar th {
  font-weight: normal;
  color: #b5b5b5;
}

.calendar .day {
  background-image: url("/site/images/ColorBg512.png");
  background-size: 256px 256px;
  background-attachment: fixed;
  border: 2px solid #3c3c3c;
  border-radius: 50% 0 50% 0;
  height: 42px;
  width: 42px;
  padding: 0;
  font-size: 24px;
}

.calendar .partial,
.calendar .availability2 {
  border-radius: 50% 0 50% 50%;
  background: none;
}

.calendar .full,
.calendar .availability3 {
  border-radius: 50%;
}

.calendar .blocked,
.calendar .availability4 {
  border-color: transparent;
  background: none;
}

.calendar .full,
.calendar .availability3 {
  background: none;
}

.calendar .day div {
  width: calc(100% - 6px);
  height: calc(100% - 8px);
  vertical-align: bottom;
  display: inline-block;
}

.calendar .day a {
  position: relative;
  left: -0.5px;
  color: #151515;
  font-weight: bold;
  text-decoration: none;
}

.calendar .partial div,
.calendar .availability2 div {
  background-image: url("/site/images/ColorBg512.png");
  background-size: 256px 256px;
  background-attachment: fixed;
  border-radius: 50%;
}

.calendar .day div {
  padding-top: 2px;
}

.calendar .full a,
.calendar .blocked a,
.calendar .availability3 a,
.calendar .availability4 a {
  color: #5b5b5b;
}

.calendar .day.selected,
.calendar .day.selected div {
  border-color: white;
  background-image: none;
}

.calendar .day.selected a {
  color: white;
}

.calendar .day.selected.availability4 {
  border-color: red;
  background-image: none;
}

.calendar .day.selected.availability4 a {
  color: red;
}

.legend {
  text-align: center;
}

#legend {
  text-align: left;
}

#legend > * {
  text-align: center;
}
</style>