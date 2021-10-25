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
    <div>
      {{ shared.item | json }}
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
      id: this.$route.query.id,
    };
  },
  created() {
    if (this.shared.item == null || this.shared.item.id != this.id) {
      this.shared.item = null;
      MaterialService.getItems([this.id]).then((x) => {
        this.shared.item = x[0];
      });
    }
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