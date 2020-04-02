<div id="app-navigation">
    <ul>
        <?php foreach ($_['itemsNav'] as $navItem) { ?>
          <li<?php if ($_['active'] == $navItem['id']) { print_unescaped(' class="active"'); } ?>>
            <a data-navigation="<?php p($navItem['id']) ?>" href="<?php p($navItem['url']) ?>">
              <?php p($navItem['name']) ?>
            </a>
          </li>
        <?php } ?>
    </ul>
</div>
