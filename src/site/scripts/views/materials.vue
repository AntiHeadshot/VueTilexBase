<template>
  <div>
    <h3>Materials</h3>
    <div
      class="itemGroup"
      v-for="material in materials"
      v-bind:key="material.id"
    >
      <h2 class="coloredText">{{ material.name }}</h2>
      <div class="itemGroupContainer">
        <div class="item" v-for="item in material.items" v-bind:key="item.id">
          <img
            v-on:click="open(item)"
            :src="item.icon"
            :title="item.name"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import shared from "../shared";
import { MaterialService } from "../services/materialService";

export default {
  data: function () {
    return {
      shared,
      materials: [],
      items: {},
    };
  },
  async created() {
    let materials = await MaterialService.getMaterials();

    materials.sort((a, b) => a.order - b.order);

    let itemIds = materials.map((x) => x.items).reduce((a, b) => a.concat(b));

    let items = await MaterialService.getItems(itemIds);
    let itemDict = {};
    for (let i of items) itemDict[i.id] = i;
    this.items = itemDict;

    for (let group of materials) {
      let items = Array();
      for (let itemId of group.items) {
        items.push(this.items[itemId]);
      }
      group.items = items;
    }
    this.materials = materials;
  },
  methods: {
    open(item) {
      this.shared.item = item;
      this.$router.push({ path: "/item", query: { id: item.id } });
    },
  },
};
</script>

<style>
.item {
  display: inline-block;
  margin: 2px;
  height: 64px;
}
.itemGroupContainer {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
}
</style>