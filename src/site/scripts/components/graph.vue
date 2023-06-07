<template>
  <canvas ref="chart" :width="width" :height="height"></canvas>
</template>
<script>
import { Color } from "../model/Color";

let idCnt = 1;

export default {
  props: {
    width: {
      type: Number,
      default: 400,
    },
    height: {
      type: Number,
      default: 800,
    },
    data: Array,
    xAxisKey: String,
    yAxisKey: String,
  },
  data: () => {
    return { id: idCnt++, chart: null };
  },
  mounted: function () {
    this.initChartData();
  },
  methods: {
    initChartData() {
      const ctx = this.$refs.chart.getContext("2d");

      let xData = this.data.map((x) => eval("x." + this.xAxisKey));

      let min = xData.min();
      let max = xData.max();
      let diff = max - min;

      let colorFrom = Color.fromHEX("#FF0015");
      let colorTo = Color.fromHEX("#FF5D03");

      let colors = xData.map((d) =>
        colorFrom.lerpRGB(colorTo, (d - min) / diff)
      );

      let bgColors = colors.map((c) => c.clone().fadeout(0.8));

      this.chart = new Chart(ctx, {
        type: "line",
        data: {
          datasets: [
            {
              data: this.data,
              backgroundColor: colors.map((c) => c.toCssRGBA()),
              borderWidth: 0,
            },
          ],
        },
        options: {
          indexAxis: "y",
          parsing: {
            xAxisKey: this.xAxisKey,
            yAxisKey: this.yAxisKey,
          },
          scales: {
            y: [
              { position: "left", display: true },
              { position: "rigth", display: true },
              { position: "top", display: true },
              { position: "bottom", display: true },
            ],
            x: {
              //       barThickness: 1,
              type: "time",
              time: {
                unit: "day",
              },
            },
          },
        },
      });
    },
  },
};
</script>