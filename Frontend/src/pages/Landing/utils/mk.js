import { h } from "vue";

export const mk = (paths) => ({
  render: () =>
    h(
      "svg",
      { viewBox: "0 0 24 24", fill: "none", width: 20, height: 20 },
      paths.map((p) => h(p[0], p[1]))
    ),
});
