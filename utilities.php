<?php

function h($str)
{
	return htmlspecialchars($str ?? "", ENT_QUOTES, "UTF-8");
}

function sel($value, $target)
{
	return $value === $target ? "selected" : "";
}

function add_toast(string $type, string $message): void
{
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	$_SESSION["toasts"][] = [
		"type" => $type,
		"message" => $message,
	];
}

function get_and_clear_toasts(): array
{
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	$toasts = $_SESSION["toasts"] ?? [];
	unset($_SESSION["toasts"]);

	return $toasts;
}

function render_search_input(
	string $targetId,
	string $keyword,
	?string $selectOobId = null,
): void {
	$selectOobAttr = $selectOobId ? ' hx-select-oob=" #' . h($selectOobId) . '"' : '';

	echo '<label class="input max-sm:w-full">';
	echo '<iconify-icon icon="lucide:search" width="none" class="size-4"></iconify-icon>';
	echo '<input'
		. ' type="search"'
		. ' name="search"'
		. ' hx-get'
		. ' hx-trigger="input changed delay:500ms"'
		. ' hx-target="#' . h($targetId) . '"'
		. ' hx-swap="outerHTML transition:true"'
		. ' hx-select="#' . h($targetId) . '"'
		. $selectOobAttr
		. ' value="' . h($keyword) . '"'
		. ' />';
	echo '</label>';
}

function view_transition_attrs(string $prefix, int|string $id): string
{
	$name = $prefix . '-' . $id;
	return 'id="' . h($name) . '" style="view-transition-name: ' . h($name) . ';"';
}

function render_pagination_join(
	string $targetId,
	int $page,
	int $totalPages,
	string $basePath = "",
	array $extraParams = [],
): void {
	if ($totalPages <= 1) {
		return;
	}

	$params = $extraParams;
	$params["page"] = 1;
	$queryFirst = http_build_query($params);
	$params["page"] = max(1, $page - 1);
	$queryPrev = http_build_query($params);
	$params["page"] = min($totalPages, $page + 1);
	$queryNext = http_build_query($params);
	$params["page"] = $totalPages;
	$queryLast = http_build_query($params);

	$disabledFirstPrev = $page <= 1 ? "disabled" : "";
	$disabledNextLast = $page >= $totalPages ? "disabled" : "";

	$html = "";
	$html .= '<div class="flex justify-center py-4">';
	$html .=
		"<div" .
		' class="join"' .
		' hx-target="#' .
		$targetId .
		'"' .
		' hx-swap="outerHTML transition:true show:top"' .
		' hx-select="#' .
		$targetId .
		'"' .
		">";

	$html .=
		'<button class="join-item btn btn-sm"' .
		' hx-get="' .
		$basePath .
		"?" .
		$queryFirst .
		'"' .
		" " .
		$disabledFirstPrev .
		">«</button>";

	$html .=
		'<button class="join-item btn btn-sm"' .
		' hx-get="' .
		$basePath .
		"?" .
		$queryPrev .
		'"' .
		" " .
		$disabledFirstPrev .
		">‹</button>";

	$html .= '<select hx-get name="page" class="join-item select select-sm">';
	for ($i = 1; $i <= $totalPages; $i++) {
		$isSelected = $i === $page ? " selected" : "";

		$html .=
			'<option value="' .
			$i .
			'"' .
			$isSelected .
			">" .
			"Halaman " .
			$i .
			" / " .
			$totalPages .
			"</option>";
	}
	$html .= "</select>";

	$html .=
		'<button class="join-item btn btn-sm"' .
		' hx-get="' .
		$basePath .
		"?" .
		$queryNext .
		'"' .
		" " .
		$disabledNextLast .
		">›</button>";

	$html .=
		'<button class="join-item btn btn-sm"' .
		' hx-get="' .
		$basePath .
		"?" .
		$queryLast .
		'"' .
		" " .
		$disabledNextLast .
		">»</button>";

	$html .= "</div>";
	$html .= "</div>";

	echo $html;
}
