<?php

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/utilities.php";
require_once __DIR__ . "/vendor/autoload.php";

ob_start();
require_once __DIR__ . "/routes.php";
$page = ob_get_clean();
$title = htmlentities($title);
$toasts = get_and_clear_toasts();

$currentPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) ?: "/";
$isLoginPage = $currentPath === "/login";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
  <meta name="viewport" content="width=device-width" />
  <title><?= $title ?></title>

  <link rel="stylesheet" href="/styles.css">

  <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.8/dist/htmx.min.js" integrity="sha384-/TgkGk7p307TH7EXJDuUlgG3Ce1UVolAOFopFekQkkXihi5u/6OCvVKyz1W+idaz" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@3.0.2/dist/iconify-icon.min.js" integrity="sha256-Am/FVmljwtoGse/9sLaiw+q1O2G65SznGIbU3ErcSQw=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://d3js.org/d3.v7.min.js"></script>

  <script>
    document.addEventListener("input", (e) => {
      if (e.target instanceof HTMLInputElement && e.target.matches(".theme-controller")) {
        localStorage.setItem("theme", e.target.value);
      }
    })

    htmx.onLoad((element)=> {
      if (element instanceof HTMLDialogElement) {
        element.showModal();
        element.addEventListener("close", () => {
          element.addEventListener("transitionend", () => element.remove())
        })

        element.addEventListener("click", (e) => {
          if (e.target && e.target.textContent === "Batal") {
            e.preventDefault();
            element.close();
          }
        });

        const form = element.querySelector("form[hx-boost]");
        if (form instanceof HTMLFormElement) {
          form.addEventListener("submit", () => {
            element.close()
            element.addEventListener("transitionend", () => element.remove())
          })
        }
      }

      const toast =
        element.matches("#toast") ? element : element.querySelector("#toast")
      if (toast) {
        const toasts = Array.from(toast.children);
        toasts.forEach((toast, idx) => {
          toast.addEventListener("transitionend", () => toast.remove())
          setTimeout(
            () => toast.classList.add("transition-opacity", "opacity-0"),
            5000 + idx * 1500
          )
        })
      }

      const theme = localStorage.getItem("theme");
      const activeThemeController = element.querySelector(`input[value=${theme}]`);
      if (!!theme && activeThemeController)
        activeThemeController.checked = true
    })

    function saveSvgAsPng(selector, name = "image.png") {
      const svg = document.querySelector(selector);
      const zoomLayer = svg.querySelector("g");

      // clone svg
      const clone = svg.cloneNode(true);
      const cloneZoomLayer = clone.querySelector("g");

      // RESET TRANSFORM (INI KUNCI NYAN 💥)
      cloneZoomLayer.setAttribute("transform", "translate(0,0) scale(1)");

      // hitung bbox SETELAH reset
      document.body.appendChild(clone); // WAJIB agar bbox valid
      const bbox = cloneZoomLayer.getBBox();
      document.body.removeChild(clone);

      // set viewBox sesuai ukuran asli tree
      clone.setAttribute(
        "viewBox",
        `${bbox.x} ${bbox.y} ${bbox.width} ${bbox.height}`
      );
      clone.setAttribute("width", bbox.width);
      clone.setAttribute("height", bbox.height);

      const serializer = new XMLSerializer();
      const svgStr = serializer.serializeToString(clone);

      const canvas = document.createElement("canvas");
      const scale = 2; // HD nyan ✨
      canvas.width = bbox.width * scale;
      canvas.height = bbox.height * scale;

      const ctx = canvas.getContext("2d");
      ctx.scale(scale, scale);

      const img = new Image();
      const blob = new Blob([svgStr], {
        type: "image/svg+xml;charset=utf-8"
      });
      const url = URL.createObjectURL(blob);

      img.onload = () => {
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0, 0, bbox.width, bbox.height);
        ctx.drawImage(img, 0, 0);
        URL.revokeObjectURL(url);

        const a = document.createElement("a");
        a.download = name;
        a.href = canvas.toDataURL("image/png");
        a.click();
      };

      img.src = url;
    }
  </script>
</head>

<body>
	<?php if ($isLoginPage): ?>
		<?= $page ?>
	<?php else: ?>
		<div class="drawer lg:drawer-open">
			<input id="app-drawer" type="checkbox" class="drawer-toggle" />
			<div class="drawer-content flex min-h-screen flex-col p-2">
				<?php require_once __DIR__ . "/components/navbar.php"; ?>

				<div class="flex-1 pt-2">
					<main class="rounded-box min-h-full p-4 shadow-sm">
						<?= $page ?>
					</main>
				</div>
			</div>

			<?php require_once __DIR__ . "/components/sidebar.php"; ?>
		</div>
	<?php endif; ?>

	<?php if (!empty($toasts)): ?>
		<div id="toast" class="toast toast-bottom sm:toast-top toast-center">
			<?php foreach ($toasts as $toast):

   	$type = $toast["type"] ?? "info";
   	$message = $toast["message"] ?? "";
   	$alertClass = match ($type) {
   		"success" => "alert-success",
   		"error" => "alert-error",
   		"warning" => "alert-warning",
   		default => "alert-info",
   	};
   	?>
				<div class="alert <?= $alertClass ?>">
					<span><?= h($message) ?></span>
				</div>
			<?php
   endforeach; ?>
		</div>
  <?php else: ?>
    <div id="toast"></div>
	<?php endif; ?>
</body>

</html>
