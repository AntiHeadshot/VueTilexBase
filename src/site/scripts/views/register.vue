<template>
  <div>
    <h3>Registrieren</h3>
    <form action="api/v1.0/user/register" method="POST">
      <div class="inputwraper" placeholder="Benutzername">
        <input type="text" v-model="user" placeholder=" " />
      </div>
      <div class="inputwraper" placeholder="E-Mail">
        <input type="email" v-model="email" placeholder=" " />
      </div>
      <br />
      <div class="inputwraper" placeholder="Passwort">
        <input type="password" v-model="password" placeholder=" " />
      </div>
      <div class="inputwraper" placeholder="Passwort">
        <input type="password" v-model="password2" placeholder=" " />
      </div>
      <br />
      <a type="submit" name="submit" class="button" v-on:click="register">Anmelden</a>
    </form>
  </div>
</template>
<script>
import shared from "../shared";
import { LoginService } from "../services/loginService";

export default {
  data: function () {
    return {
      user: "",
      email: "",
      password: "",
      password2: "",
      shared,
    };
  },
  methods: {
    async register() {
      if (this.password != this.password2) {
        this.shared.error = "Die Passwörter stimmen nicht überein.";
        return;
      }

      let out = await LoginService.register(
        this.user,
        this.email,
        this.password,
        (x) => {
          this.shared.error = x.message;
        }
      ).then((r) => {
        this.$router.push("/");
      });
    },
  },
};
</script>

<style>
</style>