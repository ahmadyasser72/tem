<?php

$items = [
	[
		"label" => "Dashboard",
		"icon" => "lucide:chart-column",
		"url" => "/dashboard",
	],
	[
		"label" => "Pangkat",
		"icon" => "lucide-lab:star-north",
		"url" => "/dashboard/organisasi/pangkat",
	],
	[
		"label" => "Jabatan",
		"icon" => "lucide:badge-check",
		"url" => "/dashboard/organisasi/jabatan",
	],
	[
		"label" => "Unit Kerja",
		"icon" => "lucide:building-2",
		"url" => "/dashboard/organisasi/unit-kerja",
	],
	[
		"label" => "Pegawai",
		"icon" => "lucide:users",
		"url" => "/dashboard/organisasi/pegawai",
	],
]; ?>

<div class="drawer-side is-drawer-close:overflow-visible lg:p-1">
	<label for="app-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
	<div
		class="bg-base-200 is-drawer-close:w-14 lg:rounded-box is-drawer-open:w-64 flex min-h-full flex-col items-start shadow-sm">

		<ul
			id="sidebar-menu"
			class="menu w-full grow"
			hx-boost="true"
			hx-target="main"
			hx-select="main"
			hx-swap="outerHTML"
			hx-select-oob="#sidebar-menu, #navbar-title">

			<?php foreach ($items as $item): ?>
				<li>
					<a
						href="<?= htmlspecialchars($item["url"]) ?>"
						class="<?= implode(
											" ",
											array_filter([
												"is-drawer-close:tooltip is-drawer-close:tooltip-right",
												$_SERVER["REQUEST_URI"] === $item["url"]
													? "menu-active"
													: "",
											]),
										) ?>"
						data-tip="<?= htmlspecialchars($item["label"]) ?>">
						<iconify-icon icon="<?= htmlspecialchars(
																	$item["icon"],
																) ?>" width="none"></iconify-icon>
						<span class="is-drawer-close:hidden"><?= htmlspecialchars(
																										$item["label"],
																									) ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
