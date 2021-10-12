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

import datenschutz from "./views/datenschutz.vue";
collection.datenschutz = datenschutz;
Vue.component("datenschutz", datenschutz);

import e404 from "./views/e404.vue";
collection.e404 = e404;
Vue.component("e404", e404);

import home from "./views/home.vue";
collection.home = home;
Vue.component("home", home);

import impressum from "./views/impressum.vue";
collection.impressum = impressum;
Vue.component("impressum", impressum);

import loading from "./views/loading.vue";
collection.loading = loading;
Vue.component("loading", loading);

import view1 from "./views/view1.vue";
collection.view1 = view1;
Vue.component("view1", view1);

export default collection;