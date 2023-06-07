<template>
  <div v-if="shared.item">
    <div class="itemContainer">
      <img
        class="itemImage"
        :src="shared.item.icon"
        :title="shared.item.name"
      />
      <h3 class="itemName coloredText">
        {{ shared.item.name }}
      </h3>
    </div>
    <div v-if="spidy">
      <graph
        :width="100"
        :height="400"
        :data="spidy"
        xAxisKey="datetimeEpoc"
        yAxisKey="unit_price"
      />
    </div>
  </div>
</template>

<script>
import shared from "../shared";
import { MaterialService } from "../services/materialService";
import { SpidyService } from "../services/spidyService";

export default {
  data: function () {
    return {
      shared,
      id: this.$route.query.id,
      data: null,
      label: null,
      spidy: null,
    };
  },
  async created() {
    if (this.shared.item == null || this.shared.item.id != this.id) {
      this.shared.item = null;
      this.shared.item = (await MaterialService.getItems([this.id]))[0];
    }

    let results = (await SpidyService.getItemSell(this.id)).results;
    this.spidy = Array.from(SpidyService.getBuys(results));
    console.log(this.spidy);
  },
  methods: {},
};
</script>

<style>
.itemImage {
  margin: 2px;
  height: 64px;
  display: inline-flex;
}
.itemName {
  display: inline-flex;
}
.itemContainer {
  display: flex;
  justify-content: center;
}
</style>