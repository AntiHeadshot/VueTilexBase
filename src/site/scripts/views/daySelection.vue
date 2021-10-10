<template>
  <div>
    <div>
      <h3 class="coloredText">{{shared.day.day|date}}</h3>
      <svg v-on:click="close()" viewBox="0 0 32 32" width="32">
        <mask id="closeMask" mask-type="alpha">
          <path
            ref="menuIcon"
            id="menuIconPath"
            d="M3 3L29 29 M16 16L16 16M3 29L29 3"
            stroke="white"
            stroke-width="6"
            stroke-linecap="round"
          />
        </mask>
        <image mask="url(#closeMask)" xlink:href="/site/images/ColorBg512.png" width="128" />
      </svg>
    </div>

    <table>
      <tr v-for="hour in hours" :key="hour.time">
        <td v-if="hour.time%(60*60)==0" rowspan="2" class="time">{{hour.time | timeStamp}}</td>
        <td
          :class="['availability'+hour.availability, hour.first?'first':'', hour.last?'last':'', hour.selected?'selected':'']"
          v-on:click="select(hour)"
        ></td>
      </tr>
    </table>

    <div v-if="shared.appointment">
      <h4>Termine:</h4>
      <p
        v-for="part in shared.appointment.parts"
        v-bind:key="part.id"
      >1x {{part.duration}} Stunden: {{part.fromDate|dateTime}} - {{part.toDate|time}}</p>
    </div>
  </div>
</template>

<script>
import shared from "../shared";

export default {
  data() {
    return {
      shared,
      hours: [],
    };
  },
  created() {
    let min = this.shared.day.parts[0].from - 60 * 60;
    let hours = [];
    hours.push({ availability: 4, time: min });
    hours.push({ availability: 4, time: min + 30 * 60 });

    for (let part of this.shared.day.parts) {
      for (let t = 0; t < part.duration; t += 30 * 60)
        hours.push({
          availability: part.availability,
          time: part.from + t,
          first: t == 0,
          last: false,
        });
      hours.slice(-1)[0].last = true;
    }
    let lastTime = hours.slice(-1)[0].time;
    hours.push({ availability: 4, time: lastTime + 30 * 60 });
    hours.push({ availability: 4, time: lastTime + 60 * 60 });

    let currentPart = this.shared.appointment.parts.find(
      (x) =>
        x.fromDate != null &&
        new Date(x.fromDate.getTime()).setHours(0, 0, 0, 0) ==
          this.shared.day.day.getTime()
    );

    if (currentPart != null) {
      let from =
        (currentPart.fromDate.getTime() - this.shared.day.day.getTime()) / 1000;
      let to =
        (currentPart.toDate.getTime() - this.shared.day.day.getTime()) / 1000;

      for (let hour of hours) {
        if (hour.time >= from && hour.time < to) hour.selected = true;
      }
    }

    this.hours = hours;
  },
  methods: {
    close() {
      let query = this.$route.query;
      query.month = this.shared.day.day.getTime();
      this.$router.push({
        path: "/calendarSelection",
        query: query,
      });
    },
    select(hour) {
      if (hour.availability == 1) {
        let currentPart = this.shared.appointment.parts.find(
          (x) =>
            x.fromDate != null &&
            new Date(x.fromDate.getTime()).setHours(0, 0, 0, 0) ==
              this.shared.day.day.getTime()
        );

        if (currentPart != null) {
          currentPart.fromDate = null;
          currentPart.toDate = null;
        }

        let index = this.hours.findIndex((x) => x.time == hour.time);
        let selIndex = this.hours.findIndex((x) => x.selected);

        this.hours.map((x) => (x.selected = false));

        if (index != selIndex) {
          let appPart = this.shared.appointment.parts.find(
            (x) => x.fromDate == null
          );

          let duration = appPart.duration * 60 * 60;
          for (
            ;
            index < this.hours.length &&
            this.hours[index].availability == 1 &&
            duration > 0;
            index++
          ) {
            duration -= 30 * 60;
            this.hours[index].selected = true;
          }

          if (duration > 0) {
            index = this.hours.findIndex((x) => x.time == hour.time) - 1;

            for (
              ;
              index >= 0 && this.hours[index].availability == 1 && duration > 0;
              index--
            ) {
              duration -= 30 * 60;
              this.hours[index].selected = true;
            }

            if (duration > 0)
              shared.message = "Der Zeitraum ist kürzer als veranschlagt.";
          }

          let selected = this.hours.filter((x) => x.selected);

          appPart.fromDate = new Date(
            this.shared.day.day.getTime() +
              Math.min(...selected.map((x) => x.time)) * 1000
          );
          appPart.toDate = new Date(
            this.shared.day.day.getTime() +
              (Math.max(...selected.map((x) => x.time)) + 30 * 60) * 1000
          );
          this.shared.day.selected = true;
        } else {
          this.shared.day.selected = false;
        }
      }
    },
  },
};
</script>

<style scoped>
table {
  max-width: 370px;
  width: 100%;
  display: inline-block;
  border-spacing: 1px;
}

table tr {
  /*display: flex;
    align-items: stretch;*/
  height: 24px;
}

table td.time {
  vertical-align: top;
}

table td.time:after {
  content: "00";
  font-size: 0.5em;
  vertical-align: super;
  margin-right: 6px;
}

table td:last-child {
  width: 100%;
  border-radius: 2px 2px 2px 2px;
}

table td:last-child.availability1:before,
table td:last-child.availability1:after {
  content: "";
  position: absolute;
}

table td:last-child.availability1:before {
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  background-image: url("/site/images/ColorBg256.png");
  background-attachment: fixed;
}

table td:last-child.availability1:after {
  width: calc(100% - 4px);
  height: calc(100% - 4px);
  top: 2px;
  left: 2px;
  background-color: #151515;
}

table td:last-child.availability1 {
  position: relative;
}

table td:last-child.availability1.selected:after {
  background-color: transparent;
}

table td:last-child.first.availability1:after,
table td:last-child.first.availability1:before {
  border-radius: 24px 2px 2px 2px;
}

table td:last-child.last.availability1:after,
table td:last-child.last.availability1:before {
  border-radius: 2px 2px 24px 2px;
}

table td:last-child.availability3 {
  background-color: #252525;
}
table td:last-child.first.availability3 {
  border-radius: 24px 2px 2px 2px;
}

table td:last-child.last.availability3 {
  border-radius: 2px 2px 24px 24px;
}
</style>