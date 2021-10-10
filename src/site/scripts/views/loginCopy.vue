<template>
  <div>
    <h3>Anmelden</h3>
    <div class="inputwraper" placeholder="E-Mail">
      <input type="email" v-model="email" placeholder=" " />
    </div>
    <div class="inputwraper" placeholder="Passwort">
      <input type="password" v-model="password" placeholder=" " />
    </div>
    <br />
    <a type="submit" name="submit" class="button" v-on:click="login()">Login</a>
  </div>
</template>

<script>
import shared from "../shared";
import { LoginService } from "../services/loginService";
import { UserService } from "../services/userService";
import { ApiService } from '../services/apiService';

export default {
  data: function () {
    return {
      email: "",
      password: "",
      shared,
    };
  },
  methods: {
    login: async function () {
      let out = await LoginService.login(this.email, this.password, (x) => {
        this.shared.error = x.message;
      }).then(async (r) => {

        ApiService.setToken(r.token);

        await UserService.getUser().then((u) => {
          if (u) {
            shared.user = u;
            this.$router.push("/");
          }
        });
      });
    },
  },
};
</script>

<style>
</style>