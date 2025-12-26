<?php
$rows = $db
	->query(
		"
    SELECT id_jabatan, parent_id, nama_jabatan, tipe_jabatan
    FROM jabatan
    ORDER BY tipe_jabatan, level_jabatan
",
	)
	->fetch_all(MYSQLI_ASSOC);

$items = [];
$byTipe = [];

foreach ($rows as $r) {
	$r["children"] = [];
	$items[$r["id_jabatan"]] = $r;
	$byTipe[$r["tipe_jabatan"]][] = $r["id_jabatan"];
}

foreach ($items as &$item) {
	if ($item["parent_id"] && isset($items[$item["parent_id"]])) {
		$items[$item["parent_id"]]["children"][] = &$item;
	}
}
unset($item);

$treeData = [
	"name" => "Jabatan",
	"children" => [],
];

foreach ($byTipe as $tipe => $ids) {
	$node = [
		"name" => $tipe,
		"tipe" => $tipe,
		"children" => [],
	];

	foreach ($ids as $id) {
		if (empty($items[$id]["parent_id"])) {
			$node["children"][] = $items[$id];
		}
	}

	$treeData["children"][] = $node;
}
?>


<dialog class="modal modal-bottom sm:modal-middle">
  <div class="modal-box space-y-4 max-w-4xl">

    <form method="dialog">
      <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
    </form>

    <h3 class="text-lg font-bold">
      Hirarki Jabatan
    </h3>

    <button onclick="saveSvgAsPng('#hierarchy', 'hirarki-jabatan.png')" class="btn btn-primary w-full">Simpan</button>

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
  let i = 0;

  const svg = d3.select("#hierarchy"),
      width = +svg.attr("width"),
      height = +svg.attr("height");

  const data = JSON.parse(svg.attr("data-tree"));

  const colors = {
    "Struktural": "#3B82F6",
    "Fungsional": "#10B981",
    "Pelaksana": "#F59E0B"
  };

  const zoomLayer = svg.append("g");

  const zoom = d3.zoom()
    .scaleExtent([0.4, 2])
    .on("zoom", e => zoomLayer.attr("transform", e.transform));
  svg.call(zoom);

  const g = zoomLayer.append("g");

  const tree = d3.tree()
    .nodeSize([50, 220]); // vertical, horizontal spacing

  const root = d3.hierarchy(data);

  tree(root);

  const nodes = root.descendants();
  const minX = d3.min(nodes, d => d.x);
  const maxX = d3.max(nodes, d => d.x);
  const minY = d3.min(nodes, d => d.y);
  const maxY = d3.max(nodes, d => d.y);

  const padding = 100;
  const treeWidth = maxY - minY + padding * 2;
  const treeHeight = maxX - minX + padding * 2;

  const links = root.links();

  // LINKS
  g.selectAll("path.link")
    .data(links)
    .enter()
    .append("path")
    .attr("class", "link")
    .attr("fill", "none")
    .attr("stroke", "#CBD5E1")
    .attr("stroke-width", 2)
    .attr("d", d => `
      M ${d.source.y},${d.source.x}
      C ${(d.source.y + d.target.y) / 2},${d.source.x}
        ${(d.source.y + d.target.y) / 2},${d.target.x}
        ${d.target.y},${d.target.x}
    `);

  // NODES
  const node = g.selectAll("g.node")
    .data(nodes)
    .enter()
    .append("g")
    .attr("class", "node")
    .attr("transform", d => `translate(${d.y},${d.x})`);

  node.append("circle")
    .attr("r", 8)
    .attr("fill", d => colors[d.data.tipe] || "#6B7280");

  node.append("text")
    .attr("dy", "0.32em")
    .attr("x", d => d.children ? -12 : 12)
    .attr("text-anchor", d => d.children ? "end" : "start")
    .style("font-size", "13px")
    .text(d => d.data.nama_jabatan || d.data.name);

  const scale = Math.min(
    width / treeWidth,
    height / treeHeight,
    0.7
  );

  const translateX = width / 2 - (minY + (maxY - minY) / 2) * scale;
  const translateY = height / 2 - (minX + (maxX - minX) / 2) * scale;

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
})()
</script>
