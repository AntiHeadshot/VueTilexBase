<template>
  <div id="messageContainer">
    <transition name="bounce">
      <div id="error" v-if="shared.error!= null && shared.error.length">{{shared.error}}</div>
    </transition>
    <transition name="bounce">
      <div id="message" v-if="shared.message!= null && shared.message.length">{{shared.message}}</div>
    </transition>
  </div>
</template>
<script>
import shared from "../shared";

export default {
  data() {
    return { shared };
  },
  watch: {
    shared: {
      deep: true,
      handler: function (newVal, oldVal) {
        if (newVal.message != "")
          setTimeout(() => (this.shared.message = ""), 5000);
        if (newVal.error != "")
          setTimeout(() => (this.shared.error = ""), 5000);
      },
    },
  },
};
</script>
<style>
#message,
#error {
  border-radius: 0 16px;
  border-width: 1px;
  border-style: solid;
  background: #151515;
  padding: 16px;
}

#error {
  border-color: red;
}

#message {
  border-color: white;
}

#messageContainer {
  overflow: hidden;
  position: fixed;
  z-index: 150;
  top: 0;
  max-width: min(768px, 100vw);
  left: 50%;
  transform: translateX(-50%);
}

.bounce-enter-active {
  transition: transform 0.5s;
}
.bounce-leave-active {
  transition: transform 1s;
}
.bounce-enter, .bounce-leave-to /* .fade-leave-active below version 2.1.8 */ {
  transform: translateY(-200px);
}
</style>