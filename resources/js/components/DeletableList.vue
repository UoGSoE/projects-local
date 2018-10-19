<script>
export default {
  props: {
    value: { required: true },
    deleteCallback: { default: null }
  },
  methods: {
    removeItem(item) {
      this.$emit("input", this.value.filter(t => t.id !== item.id));
      if (this.deleteCallback) {
        this.deleteCallback(item);
      }
    },
    clear() {
      this.$emit("input", []);
    }
  },
  render() {
    return this.$scopedSlots.default({
      items: this.value,
      removeItem: this.removeItem,
      clear: this.clear
    });
  }
};
</script>