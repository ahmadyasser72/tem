<?php

ob_start();
require_once __DIR__ . "/routes.php";
$page = ob_get_clean();
$title = htmlentities($title);
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

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const theme = localStorage.getItem("theme");
      if (!!theme)
        document.querySelector(`input[value=${theme}]`).checked = true
    })

    document.addEventListener("input", (e) => {
      if (e.target instanceof HTMLInputElement && e.target.matches(".theme-controller")) {
        localStorage.setItem("theme", e.target.value);
      }
    })
  </script>
</head>

<body>
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
</body>

</html>
