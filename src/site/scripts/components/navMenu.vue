<template>
  <div id="menu">
    <transition name="fade">
      <div class="fade" v-show="show" v-on:click="toggleMenu"></div>
    </transition>
    <transition name="fall">
      <div class="window" v-show="show">
        <slot></slot>
      </div>
    </transition>
    <slot name="header"></slot>
    <svg id="toggleButton" v-on:click="toggleMenu" viewBox="0 0 32 32" width="32">
      <mask id="navMenuMask" mask-type="alpha">
        <path
          ref="menuIcon"
          id="menuIconPath"
          d="M3 6L29 6 M3 16L29 16M3 26L29 26"
          stroke="white"
          stroke-width="6"
          stroke-linecap="round"
        >
          <animate
            attributeType="XML"
            attributeName="d"
            from="M3 6L29 6 M3 16L29 16M3 26L29 26"
            to="M3 3L29 29 M16 16L16 16M3 29L29 3"
            dur="0.2s"
            begin="indefinite"
            fill="freeze"
            ref="toCross"
          />
          <animate
            attributeType="XML"
            attributeName="d"
            from="M3 3L29 29 M16 16L16 16M3 29L29 3"
            to="M3 6L29 6 M3 16L29 16M3 26L29 26"
            dur="0.2s"
            begin="indefinite"
            fill="freeze"
            ref="toBars"
          />
        </path>
      </mask>
      <image mask="url(#navMenuMask)" xlink:href="/site/images/ColorBg512.png" width="128" />
    </svg>
  </div>
</template>
<script>
export default {
  data: function () {
    return {
      show: false,
    };
  },
  methods: {
    toggleMenu() {
      this.showMenu(!this.show);
    },
    showMenu(x) {
      if (x != this.show) {
        this.show = x;
        if (x) {
          this.$refs.toCross.beginElement();
        } else {
          this.$refs.toBars.beginElement();
        }
      }
    },
  },
  watch: {
    $route(newVal) {
      this.showMenu(false);
    },
  },
};
</script>
<style>
#menu {
  position: fixed;
  top: 0;
  width: 100%;
  max-width: 768px;
  margin: 0 auto;
  padding: 0;
}

#menu .fade {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;

  background-color: #151515e5;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}

.fall-enter-active,
.fall-leave-active {
  transition: transform 0.5s;
}
.fall-enter, .fall-leave-to /* .fade-leave-active below version 2.1.8 */ {
  transform: translateY(-500px);
}

#menu #toggleButton {
  position: absolute;
  right: 16px;
  top: 16px;
}

#menu .window {
  position: absolute;
  top: 32px;
  right:0;
  left:0;
  background-color: #151515;
  border: 3px solid Black;
  border-radius: 0 14px 0 16px;
  padding-top: 16px;
  padding-right: 8px;
  text-align: right;
}

#menu li{
  list-style-type: none;
  font-weight: bold;
  margin: 12px 0;
}

#menu li a{
  text-decoration: none;
}
</style>