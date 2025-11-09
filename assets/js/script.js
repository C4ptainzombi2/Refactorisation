// script.js — logique principale du site PHStructures

const apiUrl = "/api/manage_structures.php";
const tableBody = document.querySelector("#structuresTable tbody");
const pasteArea = document.querySelector("#pasteArea");
const pasteFeedback = document.querySelector("#pasteFeedback");
const addButton = document.querySelector("#addButton");

async function fetchStructures() {
  try {
    const res = await fetch(apiUrl);
    const data = await res.json();
    displayStructures(data.structures || []);
  } catch (e) {
    console.error("Erreur chargement structures:", e);
  }
}

function displayStructures(structures) {
  tableBody.innerHTML = "";
  if (!structures.length) {
    tableBody.innerHTML = "<tr><td colspan='6'>Aucune structure trouvée.</td></tr>";
    return;
  }

  structures.forEach(s => {
    const row = document.createElement("tr");

    const system = s["Nom du système"] || "N/A";
    const structure = s["Nom de la structure"] || "N/A";
    const owner = s["Propriétaire"] || "N/A";
    const status = s["État"] || "N/A";
    const reinforce = s["Renforcée"] ? "Oui" : "Non";

    row.innerHTML = `
      <td>${system}</td>
      <td>${structure}</td>
      <td>${owner}</td>
      <td>${status}</td>
      <td class="${s["Renforcée"] ? "reinforced" : "not-reinforced"}">${reinforce}</td>
      <td>
        <button class="dotlan-btn" data-system="${system}">🗺️</button>
        <button class="delete-btn" data-system="${system}" data-name="${structure}">🗑️</button>
      </td>
    `;

    tableBody.appendChild(row);
  });
}

// 🧩 Ajout / Remplacement via zone de texte
addButton.addEventListener("click", async () => {
  const text = pasteArea.value.trim();
  if (!text) {
    pasteFeedback.textContent = "⚠️ Rien à ajouter.";
    return;
  }

  try {
    const obj = JSON.parse(text);

    const res = await fetch(apiUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "replace_or_add", data: obj })
    });

    const result = await res.json();

    if (result.success) {
      pasteFeedback.textContent = result.replaced
        ? "✅ Structure remplacée."
        : "✅ Structure ajoutée.";
      pasteArea.value = "";
      fetchStructures();
    } else {
      pasteFeedback.textContent = "❌ Erreur : " + (result.error || "Inconnue");
    }
  } catch (e) {
    pasteFeedback.textContent = "❌ Format JSON invalide.";
  }
});

// 🗑️ Suppression d’une structure
tableBody.addEventListener("click", async (e) => {
  if (e.target.classList.contains("delete-btn")) {
    const sys = e.target.dataset.system;
    const name = e.target.dataset.name;

    if (!confirm(`Supprimer la structure "${name}" dans ${sys} ?`)) return;

    const res = await fetch(apiUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "delete", system: sys, name: name })
    });

    const result = await res.json();
    if (result.success) {
      fetchStructures();
    } else {
      alert("Erreur : " + result.error);
    }
  }

  // 🗺️ Aperçu Dotlan
  if (e.target.classList.contains("dotlan-btn")) {
    const sys = e.target.dataset.system;
    showDotlanPreview(sys, e.target);
  }
});

// ---------- Aperçu Dotlan ----------
let dotlanPreview = document.getElementById("dotlanPreview");
if (!dotlanPreview) {
  dotlanPreview = document.createElement("div");
  dotlanPreview.id = "dotlanPreview";
  document.body.appendChild(dotlanPreview);
}

function showDotlanPreview(system, button) {
  dotlanPreview.innerHTML = `<img src="https://evemaps.dotlan.net/map/${system.replace(/ /g, "_")}/blank.png" alt="${system}">`;
  const rect = button.getBoundingClientRect();
  dotlanPreview.style.top = rect.top + 40 + "px";
  dotlanPreview.style.left = rect.left + "px";
  dotlanPreview.classList.add("visible");

  setTimeout(() => dotlanPreview.classList.remove("visible"), 4000);
}

fetchStructures();
