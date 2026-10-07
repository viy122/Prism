# PRISM DFD ? orthogonal layout

The diagram retains the exact 63 flows from v2, including the 32 context flows, with eight processes, three data stores and exactly one box per user role.

Connectors use right-angle routing. Line bridges distinguish crossing routes from connections; crossings have not been removed by dropping flows. Renderer checks confirm zero shared line segments and zero overlapping arrow-label boxes.

- prism-dfd.png: high-resolution diagram.
- prism-dfd.svg: zoomable vector diagram.
- flows.json: unchanged flow definitions.
- layout.json: calculated node and connector positions.
- render.cjs: ELK-based SVG renderer, using the temporary cached elkjs bundle.
