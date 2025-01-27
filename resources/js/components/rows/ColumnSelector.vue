<template>
  <vue-modal
    :name="name"
    :scrollable="true"
    :draggable="true"
    :clickToClose="true"
    :height="'auto'"
    v-cloak
  >
    <eva-icon
      @click="$modals.close(name)"
      name="close-circle"
      class="text-grey-80 cursor-pointer inset-y-0 m-2 absolute right-0"
    ></eva-icon>
    <div class="p-6 bg-grey-40 text-grey-80">
      <span class="block text-2xl text-grey-80 mb-4"
        >Choose column layout:</span
      >
      <ul class="list-unstyled relative flex flex-wrap">
        <li
          class="column-section-wrapper flex-shrink-0 flex-grow px-2 mb-0 cursor-pointer"
          @click="changeLayout(layout)"
          v-for="layout in layouts"
          :key="layout"
        >
          <div class="mb-2 flex w-full buildamic-row bg-grey-60 rounded p-1">
            <div
              class="buildamic-column"
              v-for="(colClass, index) in gridConversion(layout)"
              :key="name + index"
              :class="colClass"
            >
              <div class="bg-grey-30 h-12"></div>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </vue-modal>
</template>

<script>
import { createModule } from "../../factories/modules/moduleFactory";
import Optionsfields from "../../mixins/OptionsFields";

export default {
  name: "column-selector",
  data: function() {
    return {
      // A simple array that turns whatever numbers are here into bootstrap columns that match
      // Note the array is formatted to be 2 by 2 so you can easily create symmetry thumbnails
      layouts: [
        "12",
        "6 6",
        "4 4 4",
        "3 3 3 3",
        "2 2 2 2 2 2",
        "3 6 3",
        "10 2",
        "2 10",
        "9 3",
        "3 9",
        "8 4",
        "4 8",
        "7 5",
        "5 7",
        "1 1 1 1 1",
      ],
    };
  },
  methods: {
    changeLayout(layout) {
      const newLayout = [];

      // Turn the clicked string into an array at each space e,g ["6", "6"]
      const layoutArr = layout.split(" ");

      // Loop the new array and create a column for each item
      layoutArr.forEach((col) => {
        let newCol = createModule("Column");

        // If we are divisible by 2, set the md breakpoint to 6 (translates to col-md-6)
        if (layoutArr.length % 2 === 0) {
          newCol.config.buildamic_settings.columnSizes.md = 6;
        }

        // Set the lg size to seleted value
        newCol.config.buildamic_settings.columnSizes.lg = col;

        // Not the smallest "xs" (mobile) size will remain unchanged from the newColumnStructure object which is 12
        newLayout.push(newCol);
      });

      const oldModules = {};
      const colCount = this.columns.length;

      if (colCount) {
        this.columns.forEach((component, index) => {
          if (component.value.length) {
            oldModules[index] = [];
            component.value.forEach((module) => {
              oldModules[index].push(module);
            });
          }
        });
      }

      // Change column layout
      this.columns.splice(0, colCount, ...newLayout);
      // Add old modules to new layout
      Object.keys(oldModules).forEach((key) => {
        if (this.columns[key]) {
          this.columns[key].value.push(...oldModules[key]);
        } else {
          this.columns[0].value.push(...oldModules[key]);
        }
      });

      this.$modals.close(this.name);

      // Send this off to vuex for mutatin'
      // this.changeColumnLayout({
      //     id: this.component.id,
      //     newLayout: newLayout
      // })
    },
    gridConversion(string) {
      // Turn the clicked string into an array at each space e,g ["6", "6"]
      let array = string.split(" ");

      // Loop through and create the (currently single-sized) preview thumbnail classes
      // So the same grid you click is what the builder uses
      array.forEach((column, index) => {
        array[index] = "col-" + column;
      });

      // Return the array e.g ["col-md-6", "col-md-6"]
      return array;
    },
  },
  mixins: [Optionsfields],
  props: {
    name: String,
    columns: Array,
  },
};
</script>

<style scoped>
.column-section-wrapper {
  flex-basis: 50%;
  max-width: 50%;
  box-sizing: border-box;
  transition: transform 0.2s;
}

.column-section-wrapper:hover {
  transform: scale(1.04);
}

.column-section-wrapper:active {
  transform: scale(0.9);
}
</style>
