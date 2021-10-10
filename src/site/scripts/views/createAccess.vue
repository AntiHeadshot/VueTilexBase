<template>
  <div>
    <h3>Zugang erstellen</h3>
    <div class="inputwraper" placeholder="Betreff">
      <input type="text" v-model="subject" placeholder=" " />
    </div>
    <br />
    <a type="submit" name="submit" class="button" v-on:click="create([4]);">&lhblk;</a>
    <a type="submit" name="submit" class="button" v-on:click="create([8]);">&block;</a>
    <a type="submit" name="submit" class="button" v-on:click="create([4,8]);">&lhblk; &block;</a>
    <a type="submit" name="submit" class="button" v-on:click="create([8,8]);">&block; &block;</a>
    <a
      type="submit"
      name="submit"
      class="button"
      v-on:click="create([4,4,8]);"
    >&lhblk; &lhblk; &block;</a>
    <a
      type="submit"
      name="submit"
      class="button"
      v-on:click="create([4,8,8]);"
    >&lhblk; &block; &block;</a>
  </div>
</template>

<script>
import shared from "../shared";
import { AppointmentService } from "../services/appointmentService";

export default {
  data() {
    return {
      shared,
      subject: "",
    };
  },
  methods: {
    create(durations) {
      AppointmentService.create(
        this.subject,
        durations,
        (e) => (this.shared.error = e.message)
      ).then((r) => {
        console.log(r);
        if (r)
          this.$router.push(
            "/displayAccess?token=" + encodeURIComponent(r.token)
          );
      });
    },
  },
};
</script>

<style>
</style>