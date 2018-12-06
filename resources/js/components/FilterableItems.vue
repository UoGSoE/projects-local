<script>
export default {
  props: ["items", "searchables"],

  computed: {
    filteredItems() {
      return this.items
        .filter(item => {
          return this.itemToString(item)
            .join("")
            .toLowerCase()
            .includes(this.filterText.toLowerCase());
        })
        .sort((a, b) => {
          if (this.sortOrder) {
            return this.sortColumn
              ? b[this.sortColumn]
                  .toString()
                  .localeCompare(a[this.sortColumn].toString())
              : 0;
          }
          return this.sortColumn
            ? a[this.sortColumn]
                .toString()
                .localeCompare(b[this.sortColumn].toString())
            : 0;
        });
    }
  },

  data() {
    return {
      filterText: "",
      sortColumn: null,
      sortOrder: false // false = a-z, true = z-a
    };
  },

  methods: {
    itemToString(item) {
      if (!this.searchables) {
        return Object.values(item); // only get searchables key values?
      }
      return this.searchables.split(",").map(term => item[term]);
    },

    setSortColumn(column) {
      if (this.sortColumn == column) {
        this.sortOrder = !this.sortOrder;
      } else {
        this.sortOrder = true;
      }
      this.sortColumn = column;
    }
  },

  render() {
    return this.$scopedSlots.default({
      items: this.filteredItems,
      inputAttrs: {
        value: this.filterText
      },
      inputEvents: {
        input: e => {
          this.filterText = e.target.value;
        }
      },
      sortOn: this.setSortColumn
    });
  }
};
</script>