<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
include __DIR__ . '/includes/header.php';
?>

<div class="container">
  <header>
    <h1>📡 Gestion des Structures</h1>
    <div id="filters">
      <input type="text" id="searchInput" placeholder="Rechercher une structure...">
      <select id="regionFilter"></select>
      <select id="typeFilter"></select>
      <button id="refreshBtn">🔄 Rafraîchir</button>
    </div>
  </header>

  <section id="pasteAreaContainer">
    <textarea id="pasteArea" placeholder="Collez ici les données copiées du jeu..."></textarea>
    <button id="addButton">Ajouter / Mettre à jour</button>
    <p id="pasteFeedback"></p>
  </section>

  <table id="structuresTable">
    <thead>
      <tr>
        <th>Système</th>
        <th>Structure</th>
        <th>Type</th>
        <th>Alliance / Corp</th>
        <th>Région</th>
        <th>Renforcée</th>
        <th>Date</th>
        <th>Remarques</th>
      </tr>
    </thead>
    <tbody id="tableBody"></tbody>
  </table>
</div>

<?php include __DIR__ . '/includes/modal_dotlan.php'; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
