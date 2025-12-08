<?php
session_start();

$mot_de_passe_correct = "alain.87200";

if (isset($_POST['motdepasse'])) {
    if ($_POST['motdepasse'] === $mot_de_passe_correct) {
        $_SESSION['autorise'] = true;
    } else {
        $erreur = "Mot de passe incorrect !";
    }
}

if (!isset($_SESSION['autorise'])) {
    ?>
    <form method="post">
        <h2>Accès restreint</h2>
        <input type="password" name="motdepasse" placeholder="Mot de passe" required>
        <button type="submit">Entrer</button>
        <?php if (isset($erreur)) echo "<p style='color:red;'>$erreur</p>"; ?>
    </form>
    <?php
    exit;
}
?>

<?php
include "config.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Messages reçus</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../../public/assets/css/css/index.css">
</head>
<body>
<nav class="navbar navbar-light justify-content-center fs-3 mb-5">
  <h1>Interface d'administration</h1>
  <button><a href="/index.php">Retour</a></button>
</nav>

<div class="container">
  <?php
  if(isset($_GET["msg"])) {
      $msg = $_GET["msg"];
      echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">'
          . htmlspecialchars($msg) .
          '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
  }
  ?>

  <table class="table table-hover text-center">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Number</th>
        <th>Subject</th>
        <th>Message</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php
    try {
        $sql = "SELECT * FROM contacts ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $shortMsg = strlen($row['message']) > 10 ? substr($row['message'], 0, 10) . '...' : $row['message'];
            $modalId = 'modal' . $row['id'];

            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>" . htmlspecialchars($row['name']) . "</td>
                    <td>" . htmlspecialchars($row['email']) . "</td>
                    <td>" . htmlspecialchars($row['number']) . "</td>
                    <td>" . htmlspecialchars($row['subject']) . "</td>
                    <td>
                        " . htmlspecialchars($shortMsg);

            if (strlen($row['message']) > 10) {
                echo " <button type='button' class='btn btn-sm btn-primary' data-bs-toggle='modal' data-bs-target='#$modalId'>Voir plus</button>";

                echo "
                <div class='modal fade' id='$modalId' tabindex='-1' aria-labelledby='{$modalId}Label' aria-hidden='true'>
                  <div class='modal-dialog modal-dialog-centered modal-lg'>
                    <div class='modal-content'>
                      <div class='modal-header'>
                        <h5 class='modal-title' id='{$modalId}Label'>Message complet</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                      </div>
                      <div class='modal-body' style='max-height: 60vh; overflow-y: auto;'>
                        " . nl2br(htmlspecialchars($row['message'])) . "
                      </div>
                      <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Fermer</button>
                      </div>
                    </div>
                  </div>
                </div>
                ";
            }

            echo "</td>
                  <td>
                    <a href='edit.php?id={$row['id']}' class='link-dark'><i class='fa-solid fa-pen-to-square fs-5 me-3'></i></a>
                    <a href='delete.php?id={$row['id']}' class='link-dark'><i class='fa-solid fa-trash fs-5'></i></a>
                  </td>
                </tr>";
        }

    } catch(PDOException $e) {
        echo "<tr><td colspan='7'>Erreur : " . htmlspecialchars($e->getMessage()) . "</td></tr>";
    }
    ?>
    </tbody>
  </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
