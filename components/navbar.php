<nav class="navbar bg-base-200 rounded-box w-full shadow-sm">
  <label
    for="app-drawer"
    aria-label="open sidebar"
    class="btn btn-square btn-ghost">
    <iconify-icon icon="line-md:menu-unfold-right" width="none"></iconify-icon>
  </label>

  <div id="navbar-title" class="ps-4"><?= $title ?></div>

  <div class="flex grow justify-end gap-1 px-2">
    <div class="flex items-stretch">
      <?php $username = $_SESSION["user"]["username"] ?? "User"; ?>

      <div class="dropdown dropdown-end">
        <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
          <iconify-icon icon="line-md:account" width="none"></iconify-icon>
        </div>
        <ul
          tabindex="-1"
          class="menu dropdown-content bg-base-200 rounded-box z-1 mt-4.5 w-48 translate-x-4 p-2 shadow-sm">
          <li class="menu-title"><?php echo htmlspecialchars(
          	$username,
          	ENT_QUOTES,
          	"UTF-8",
          ); ?></li>

          <li>
            <details>
              <summary>
                <iconify-icon icon="line-md:light-dark" width="16"></iconify-icon>
                Theme
              </summary>
              <ul>
                <li>
                  <input
                    type="radio"
                    name="theme-dropdown"
                    class="theme-controller btn btn-sm btn-block btn-ghost justify-start"
                    aria-label="Light"
                    value="pastel" />
                </li>
                <li>
                  <input
                    type="radio"
                    name="theme-dropdown"
                    class="theme-controller btn btn-sm btn-block btn-ghost justify-start"
                    aria-label="Dark"
                    value="night" />
                </li>
              </ul>
            </details>
          </li>

          <li>
            <a href="/logout">
              <iconify-icon icon="line-md:logout" width="16"></iconify-icon>
              Logout
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>
