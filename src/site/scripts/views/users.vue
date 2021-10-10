<template>
  <div>
    <h3 class="coloredText">Benutzer</h3>
    <table style="display:inline-block; border-spacing: 10px 0;">
      <tr>
        <th align="right">Name</th>
        <th>Aktiv</th>
        <th>User</th>
        <th>Mod</th>
        <th>Admin</th>
      </tr>
      <tr v-for="user in users" :key="user.id">
        <td align="right">{{user.name}}</td>
        <td>
          <checkbox :checked="user.isActive" v-on:click.native="toggleActive(user)"></checkbox>
        </td>
        <td>
          <checkbox :checked="user.privilege>=1" v-on:click.native="setUser(user)"></checkbox>
        </td>
        <td>
          <checkbox :checked="user.privilege>=2" v-on:click.native="setMod(user)"></checkbox>
        </td>
        <td>
          <checkbox :checked="user.privilege>=3" v-on:click.native="setAdmin(user)"></checkbox>
        </td>
      </tr>
    </table>
  </div>
</template>

<script>
import shared from "../shared";
import { UserService } from "../services/userService";

export default {
  data() {
    return {
      users: [],
      shared,
    };
  },
  created() {
    UserService.getUsers(0, 100).then((r) => (this.users = r));
  },
  methods: {
    toggleActive(user) {
      UserService.setState(user.id, !user.isActive).then((r) => {
        user.isActive = !user.isActive;
      });
    },
    setUser(user) {
      UserService.setPrivilege(user.id, 1).then((r) => {
        user.privilege = 1;
      });
    },
    setMod(user) {
      UserService.setPrivilege(user.id, 2).then((r) => {
        user.privilege = 2;
      });
    },
    setAdmin(user) {
      UserService.setPrivilege(user.id, 3).then((r) => {
        user.privilege = 3;
      });
    },
  },
};
</script>