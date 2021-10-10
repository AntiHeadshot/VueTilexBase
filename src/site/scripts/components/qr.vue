<template>
  <svg :height="size" :width="size">
    <defs>
      <filter
        :id="'qrFilter'+id"
        x="-20%"
        y="-20%"
        width="140%"
        height="140%"
        filterUnits="objectBoundingBox"
        primitiveUnits="userSpaceOnUse"
        color-interpolation-filters="linearRGB"
      >
        <feColorMatrix
          type="matrix"
          values="-1 0 0 0 1
0 -1 0 0 1
0 0 -1 0 1
0 0 0 0 1"
          x="0%"
          y="0%"
          width="100%"
          height="100%"
          in="SourceGraphic"
          result="colormatrix8"
        />
      </filter>
    </defs>
    <mask :id="'qrMask'+id" mask-type="luminance">
      <image
        :xlink:href="'https://chart.googleapis.com/chart?chs='+ size +'x'+ size +'&cht=qr&chl='+ encodeURI(href) +'&choe=UTF-8&chld=M|0'"
        :filter="'url(#qrFilter'+id+')'"
      />
    </mask>
    <image :mask="'url(#qrMask'+id+')'" xlink:href="/site/images/ColorBg512.png" :width="size" />
  </svg>
</template>
<script>
let idCnt = 1;

export default {
  props: {
    size: {
      type: Number,
      default: 128
    },
    href: {
      type: String,
      default: "https://www.google.de"
    }
  },
  data: ()=>{return{id:idCnt++};}
};
</script>