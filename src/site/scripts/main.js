import comps from './componentloader';
import { UserService } from './services/userService';

import shared from './shared';

if (shared.isDebug)
    require('./debug/debugConsole');

require('./filter');

UserService.getUser().then(r => {
    if (r) {
        shared.user = r;
    }

    //tell vue to use the router
    Vue.use(VueRouter);
    //define your routes
    const routes = [{
            path: "/",
            redirect: () => {
                if (shared.user == null)
                    return 'login';
                else if (!shared.user.isConnected)
                    return 'userSettings';
                else
                    return 'createAccess';
            }
        },
        { path: "/login", component: comps.login },
        { path: "/register", component: comps.register },
        { path: "/userSettings", component: comps.userSettings },
        { path: "/createAccess", component: comps.createAccess },
        { path: "/displayAccess", component: comps.displayAccess },
        { path: "/calendarDisplay", component: comps.calendarDisplay },
        { path: "/calendarSelection", component: comps.calendarSelection },
        { path: "/daySelection", component: comps.daySelection },
        { path: "/impressum", component: comps.impressum },
        { path: "/datenschutz", component: comps.datenschutz },
        { path: "/users", component: comps.users },
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
        if (to.path == '/login' || to.path == '/register') {
            if (shared.user != null) {
                if (!shared.user.isConnected)
                    next('userSettings')
                else
                    next('createAccess')
            } else
                next();
        } else if ((to.path == '/userSettings' || to.path == '/createAccess') && shared.user == null)
            next('login')
        else if (to.path == '/daySelection' && (shared.appointment == null || shared.day == null)) {
            next({ path: '/calendarSelection', query: to.query });
        } else next()
    })

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
    document.addEventListener("touchstart", function() {}, true);

    //fix firefox colored Text
    if (typeof InstallTrigger !== 'undefined') { //if firefox
        var scrollTimer = null; //timer identifier
        var doneScrollingInterval = 100; //time in ms, 5 second for example

        document.onscroll = function() {
            clearTimeout(scrollTimer);
            if (scrollTimer == null) {
                for (let elem of document.getElementsByClassName("coloredText")) {
                    if (!elem.classList.contains("fixed") &&
                        elem.style.backgroundPosition == ""
                    ) {
                        elem.style.backgroundAttachment = "scroll";
                        let rect = elem.getBoundingClientRect();
                        elem.style.backgroundPosition = `${
                        rect.x + window.pageXOffset
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
});