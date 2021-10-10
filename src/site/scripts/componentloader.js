let collection={};
import checkbox from "./components/checkbox.vue";
collection.checkbox = checkbox;
Vue.component("checkbox", checkbox);

import message from "./components/message.vue";
collection.message = message;
Vue.component("message", message);

import navMenu from "./components/navMenu.vue";
collection.navMenu = navMenu;
Vue.component("navMenu", navMenu);

import qr from "./components/qr.vue";
collection.qr = qr;
Vue.component("qr", qr);

import app from "./views/app.vue";
collection.app = app;
Vue.component("app", app);

import calendarDisplay from "./views/calendarDisplay.vue";
collection.calendarDisplay = calendarDisplay;
Vue.component("calendarDisplay", calendarDisplay);

import calendarSelection from "./views/calendarSelection.vue";
collection.calendarSelection = calendarSelection;
Vue.component("calendarSelection", calendarSelection);

import createAccess from "./views/createAccess.vue";
collection.createAccess = createAccess;
Vue.component("createAccess", createAccess);

import datenschutz from "./views/datenschutz.vue";
collection.datenschutz = datenschutz;
Vue.component("datenschutz", datenschutz);

import daySelection from "./views/daySelection.vue";
collection.daySelection = daySelection;
Vue.component("daySelection", daySelection);

import displayAccess from "./views/displayAccess.vue";
collection.displayAccess = displayAccess;
Vue.component("displayAccess", displayAccess);

import e404 from "./views/e404.vue";
collection.e404 = e404;
Vue.component("e404", e404);

import home from "./views/home.vue";
collection.home = home;
Vue.component("home", home);

import impressum from "./views/impressum.vue";
collection.impressum = impressum;
Vue.component("impressum", impressum);

import login from "./views/login.vue";
collection.login = login;
Vue.component("login", login);

import loginCopy from "./views/loginCopy.vue";
collection.loginCopy = loginCopy;
Vue.component("loginCopy", loginCopy);

import register from "./views/register.vue";
collection.register = register;
Vue.component("register", register);

import users from "./views/users.vue";
collection.users = users;
Vue.component("users", users);

import userSettings from "./views/userSettings.vue";
collection.userSettings = userSettings;
Vue.component("userSettings", userSettings);

export default collection;