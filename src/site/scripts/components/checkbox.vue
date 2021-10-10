<template>
<span :class="checked ?'true':'false'"></span>
</template>
<script>
export default {
  props: {
    checked : Boolean,
  },
  model: {
    prop: "checked",
    event: "change",
  }
};
</script>
<style scoped>
span {
  width: 24px;
  height: 24px;
  border: 1px solid #ccc;
  display: inline-block;
  border-radius: 50%;
  margin: 0 8px;
  position: relative;
  background-color: transparent;
}

span:before{
  background: url('/site/images/ColorBg512.png');
  background-size: 256px 256px;
  background-attachment: fixed;
  content: "";
  width: 100%;
  height: 100%;
  border-radius: 50%;
  display: inline-block;
  position: absolute;
  top: 0;
  left: 0;

  transition: all linear 0.3s;  
  opacity: 0;
}

span.true:before {
  
  opacity: 1;
}

span.true:after {
  content: "";
  top: 3px;
  left: 8px;
  border-bottom: 4px solid #151515;
  border-right: 4px solid #151515;
  height: 12px;
  width: 5px;
  transform: rotate(45deg);
  position: absolute;
}
</style>