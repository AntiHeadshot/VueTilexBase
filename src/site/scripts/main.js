import "regenerator-runtime/runtime.js";
import comps from './componentloader';

import shared from './shared';

if (shared.isDebug)
    require('./debug/debugConsole');

require('./filter');

//tell vue to use the router
Vue.use(VueRouter);
//define your routes
const routes = [
    {
        path: "/",
        redirect: () => {
            return 'home';
        }
    },
    { path: "/home", component: comps.home },
    { path: "/view1", component: comps.view1 },
    { path: "/impressum", component: comps.impressum },
    { path: "/datenschutz", component: comps.datenschutz },
    { path: "*", component: comps.e404 }
];

// Create the router instance and pass the `routes` option
// You can pass in additional options here, but let's
// keep it simple for now.
const router = new VueRouter({
    routes, // short for routes: routes
    mode: "history",
    scrollBehavior(to, from, savedPosition) {
        return { x: 0, y: 0 }
    }
});

router.beforeEach((to, from, next) => {
    //rerout to diferent page if path is incorrect
    next()
});

let app = comps.app;

//instatinat the vue instance
var application = new Vue({
    //define the selector for the root component
    el: "#app",
    //pass the template to the root component
    template: '<app :key="componentKey"/>',
    //declare components that the root component can access
    components: { app },
    //pass in the router to the Vue instance
    router,
    data() {
        return {
            componentKey: 0,
            shared: shared,
        };
    },
    methods: {
        forceRerender() {
            this.componentKey += 1;
        },
    }
});

application.$mount("#app"); //mount the router on the app


//fix ios
document.addEventListener("touchstart", function () { }, true);

//fix firefox colored Text
if (typeof InstallTrigger !== 'undefined') { //if firefox
    var scrollTimer = null; //timer identifier
    var doneScrollingInterval = 100; //time in ms, 5 second for example

    document.onscroll = function () {
        clearTimeout(scrollTimer);
        if (scrollTimer == null) {
            for (let elem of document.getElementsByClassName("coloredText")) {
                if (!elem.classList.contains("fixed") &&
                    elem.style.backgroundPosition == ""
                ) {
                    elem.style.backgroundAttachment = "scroll";
                    let rect = elem.getBoundingClientRect();
                    elem.style.backgroundPosition = `${rect.x + window.pageXOffset
                        }px ${rect.y + window.pageYOffset}px`;
                }
            }
        }
        scrollTimer = setTimeout(doneScrolling, doneScrollingInterval);
    };

    function doneScrolling() {
        scrollTimer = null;
    }
}