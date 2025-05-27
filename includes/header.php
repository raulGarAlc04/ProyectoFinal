<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= html_escape($title) ?></title>
  <meta name="description" content="<?= html_escape($description) ?>">
  <link rel="stylesheet" type="text/css" href="../css/user_styles.css">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
  <link rel="shortcut icon" type="image/png" href="img/favicon.ico">
</head>

<body>
  <header>
    <div class="container">
      <a class="skip-link" href="#content">Skip to content</a>
      <div class="logo">
        <a href="index.php"><img src="../img/logo.png" alt="Creative Folk"></a>
      </div>
      <nav>
        <button id="toggle-navigation" aria-expanded="false">
          <span class="icon-menu"></span><span class="hidden">Menu</span>
        </button>
        <ul id="menu">
          <li><a href="ver_peliculas.php" <?= ($section == 'peliculas') ? 'class="on" aria-current="page"' : '' ?>>
              Películas
            </a></li>

          <li><a href="ver_series.php" <?= ($section == 'series') ? 'class="on" aria-current="page"' : '' ?>>
              Series
            </a></li>

          <li><a href="ver_plataformas.php" <?= ($section == 'plataformas') ? 'class="on" aria-current="page"' : '' ?>>
              Plataformas
            </a></li>

          <li><a href="../logout.php?user_id=<?php echo $_SESSION['user_id']; ?>">
              Cerrar Sesión
            </a></li>
        </ul>
      </nav>
      <?php
      $foto_perfil = isset($_SESSION['foto_perfil']) && $_SESSION['foto_perfil'] ? $_SESSION['foto_perfil'] : 'blank.png';
      ?>
      <a href="perfil.php" class="profile-btn" title="Ver perfil">
        <img src="../uploads/<?= $foto_perfil ?>" alt="Foto de perfil" class="profile-avatar">
      </a>
    </div>
  </header>