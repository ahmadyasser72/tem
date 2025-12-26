<?php
$rows = $db
	->query(
		"
    SELECT
        id_unit,
        induk_unit,
        nama_unit,
        jenis_unit
    FROM unit_kerja
    ORDER BY nama_unit
",
	)
	->fetch_all(MYSQLI_ASSOC);

/**
 * index by id
 */
$items = [];
foreach ($rows as $r) {
	$r["children"] = [];
	$items[$r["id_unit"]] = $r;
}

/**
 * build parent-child relation
 */
$roots = [];
foreach ($items as $id => &$item) {
	if ($item["induk_unit"] && isset($items[$item["induk_unit"]])) {
		$items[$item["induk_unit"]]["children"][] = &$item;
	} else {
		$roots[] = &$item;
	}
}
unset($item);

/**
 * final tree
 */
$treeData = [
	"name" => "Unit Kerja",
	"children" => $roots,
];
?>

<dialog class="modal modal-bottom sm:modal-middle">
  <div class="modal-box space-y-4 max-w-4xl">

    <form method="dialog">
      <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
    </form>

    <h3 class="text-lg font-bold">
      Hirarki Unit Kerja
    </h3>

    <button onclick="saveSvgAsPng('#hierarchy', 'hirarki-unit-kerja.png')" class="btn btn-primary w-full">Simpan</button>

    <div class="bg-base-100 p-4 rounded shadow overflow-hidden">
      <svg id="hierarchy" data-tree='<?= json_encode(
      	$treeData,
      	JSON_UNESCAPED_UNICODE,
      ) ?>' width="1200" height="600"></svg>
    </div>
  </div>

  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>

<script>
(function () {
  const svg = d3.select("#hierarchy"),
        width = +svg.attr("width"),
        height = +svg.attr("height");

  const data = JSON.parse(svg.attr("data-tree"));

  const colors = {
    "Kantor Pusat": "#1E3A8A",
    "Bidang": "#2563EB",
    "Seksi": "#22C55E",
    "Distrik": "#F97316",
    "Posko": "#E11D48",
    "Regu": "#A855F7"
  };

  const zoomLayer = svg.append("g");

  const zoom = d3.zoom()
    .scaleExtent([0.4, 2])
    .on("zoom", e => zoomLayer.attr("transform", e.transform));

  svg.call(zoom);

  const g = zoomLayer.append("g");

  const tree = d3.tree().nodeSize([140, 60]);

  const root = d3.hierarchy(data);
  tree(root);

  const nodes = root.descendants();
  const links = root.links();

  const minX = d3.min(nodes, d => d.x);
  const maxX = d3.max(nodes, d => d.x);
  const minY = d3.min(nodes, d => d.y);
  const maxY = d3.max(nodes, d => d.y);

  const padding = 120;
  const treeWidth = maxY - minY + padding * 2;
  const treeHeight = maxX - minX + padding * 2;

  // LINKS
  g.selectAll("path.link")
    .data(links)
    .enter()
    .append("path")
    .attr("fill", "none")
    .attr("stroke", "#CBD5E1")
    .attr("stroke-width", 2)
    .attr("d", d => `
      M ${d.source.x},${d.source.y}
      C ${d.source.x},${(d.source.y + d.target.y) / 2}
        ${d.target.x},${(d.source.y + d.target.y) / 2}
        ${d.target.x},${d.target.y}
    `);

  // NODES
  const node = g.selectAll("g.node")
    .data(nodes)
    .enter()
    .append("g")
    .attr("transform", d => `translate(${d.x},${d.y})`);

  node.append("circle")
    .attr("r", 8)
    .attr("fill", d => colors[d.data.jenis_unit] || "#6B7280");

  node.append("text")
    .attr("dy", "0.32em")
    .attr("x", 0)
    .attr("dy", d => d.children ? "-0.8em" : "1.2em")
    .attr("text-anchor", "middle")
    .style("font-size", "13px")
    .text(d => d.data.nama_unit || d.data.name);

  const scale = Math.min(
    width / treeWidth,
    height / treeHeight,
    0.9
  );

  const translateX =
    width / 2 - (minX + (maxX - minX) / 2) * scale;

  const translateY =
    80 - minY * scale; // kasih margin atas

  svg.call(
    zoom.transform,
    d3.zoomIdentity
      .translate(translateX, translateY)
      .scale(scale)
  );

  const wrapper = document.querySelector("#hierarchy").parentElement;
  requestAnimationFrame(() => {
    wrapper.scrollLeft =
      (wrapper.scrollWidth - wrapper.clientWidth) / 2;
    wrapper.scrollTop =
      (wrapper.scrollHeight - wrapper.clientHeight) / 2;
  });
})();
</script>
