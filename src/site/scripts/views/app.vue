<template>
  <div id="app">
    <message />
    <div id="content">
      <router-view></router-view>
    </div>
    <div id="footer">
      <div id="footerTopL"></div>
      <div id="footerTopR"></div>
      <div id="footerContent">
        <a :href="shared.baseUrl">
          <img width="16" src="/site/images/refresh.svg" />
        </a>
        <router-link class="link" to="impressum">Impressum</router-link>
        <router-link class="link" to="datenschutz">Datenschutz</router-link>
      </div>
    </div>
    <navMenu v-if="shared.user != null">
      <ul>
        <li>
          <router-link to="userSettings">Einstellungen</router-link>
        </li>
        <li v-if="shared.user.privilege >= 3">
          <router-link to="users">Benutzer-Verwaltung</router-link>
        </li>
        <li>
          <a v-on:click="logout">Logout</a>
        </li>
      </ul>
      <template v-slot:header>
        <div id="header">
          <router-link to="/" id="headerImg">
            <img src="/site/images/icon@2x.png" srcset="/site/images/icon@4x.png 2x" />
          </router-link>
          <div id="titleContainer">
            <div id="title">
              <h1 class="coloredText fixed">!#AppName#!</h1>
            </div>
          </div>
        </div>
      </template>
    </navMenu>
    <navMenu v-else-if="shared.isDebug">
      <ul>
        <li>
          <router-link to="login">login</router-link>
        </li>
        <li>
          <router-link to="register">register</router-link>
        </li>
        <li>
          <router-link to="userSettings">userSettings</router-link>
        </li>
        <li>
          <router-link to="createAccess">createAccess</router-link>
        </li>
        <li>
          <router-link to="displayAccess">displayAccess</router-link>
        </li>
        <li>
          <router-link to="calendarDisplay">calendarDisplay</router-link>
        </li>
        <li>
          <router-link to="calendarSelection">calendarSelection</router-link>
        </li>
        <li>
          <router-link to="daySelection">daySelection</router-link>
        </li>
      </ul>
      <template v-slot:header>
        <div id="header">
          <router-link to="/" id="headerImg">
            <img src="/site/images/icon@2x.png" srcset="/site/images/icon@4x.png 2x" />
          </router-link>
          <div id="titleContainer">
            <div id="title">
              <h1 class="coloredText fixed">!#AppName#!</h1>
            </div>
          </div>
        </div>
      </template>
    </navMenu>
    <template v-else>
      <div id="header">
        <router-link to="/" id="headerImg">
          <img src="/site/images/icon@2x.png" srcset="/site/images/icon@4x.png 2x" />
        </router-link>
        <div id="titleContainer">
          <div id="title">
            <h1 class="coloredText fixed">!#AppName#!</h1>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
<script>
import shared from "../shared";
import { Cookies } from "../services/cookies";
import { LoginService } from "../services/loginService";

export default {
  data: () => {
    return {
      shared: shared,
    };
  },
  methods: {
    logout: async function () {
      await LoginService.logout().then(() => {
        shared.user = null;
        this.$router.push("/");
        Cookies.deleteAll();
      });
    },
  },
};
</script>
<style>
#app {
  max-width: 768px;
  position: relative;
  min-height: 100vh;
  margin: 0 auto;
  padding: 0 0 0 0;
}

#header {
  position: fixed;
  top: 0;
  width: 100%;
  max-width: 768px;
  height: 60px;
  margin: 0 auto;
  background-color: #151515;
  box-shadow: 0 24px 20px -10px #151515;
}

@media (max-height: 450px) {
  #header {
    position: absolute;
  }
}

#headerImg {
  display: block;
  float: left;
  margin: 4px 16px 0px 16px;
}

#titleContainer {
  margin-left: 96px;
  position: relative;
  border-bottom: 1px solid white;
  padding: 4px 16px 4px 16px;
}

#title h1 {
  display: inline-block;
  height: 100%;
  margin: 0 auto 0 auto;
  position: relative;
}

#content {
  box-shadow: 0 -10px 20px #151515;
  background: #151515;
  position: relative;
  z-index: 0;
  margin: 0px 0px 4px 0px;
  padding: 64px 16px 16px 16px;
  text-align: center;
  border-radius: 0 0 16px 16px;
  min-height: calc(100vh - 150px);
}

#footer {
  background-color: #151515b4;
  margin-top: 12px;
  margin-left: -4px;
  margin-right: -4px;
  padding: 16px;
  height: 26px;
  position: relative;
  font-size: 16px;
}

#footerTopR,
#footerTopL {
  position: absolute;
  height: 24px;
  width: 24px;
  top: -24px;
  overflow: hidden;
}

#footerTopR {
  right: 0;
}

#footerTopL {
  left: 0;
}

#footerTopR::before,
#footerTopL::before {
  content: "";
  position: absolute;
  top: -100%;
  height: 200%;
  width: 200%;
  border-radius: 100%;
  box-shadow: 10px 10px 5px 100px #151515b4;
  z-index: -1;
}

#footerTopR::before {
  left: -100%;
}

#footerContent {
  float: right;
}

#footer .link {
  margin: 12px;
}

#footer .link:before {
  content: "<";
}

#footer .link:after {
  content: ">";
}
</style>