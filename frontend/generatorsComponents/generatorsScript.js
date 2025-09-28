document.addEventListener("DOMContentLoaded", () => {
  fetch("http://localhost:8000/api/generators/list")
    .then((response) => response.json())
    .then((generators) => {
      const list = document.getElementById("generatorsList");
      generators.forEach((generator) => {
        const item = document.createElement("li");
        item.className =
          "list-group-item d-flex justify-content-between align-items-start list-group-item-action";
        item.style.cursor = "pointer";

        item.innerHTML = `
          <div class="ms-2 me-auto">
            <div class="fw-bold">${generator.name}</div>
            Max Output: ${generator.max_output} W
          </div>
          <span class="badge text-bg-primary rounded-pill">
            ${generator.last_load_percentage} %
          </span>
        `;

        item.addEventListener("click", () => {
          // Ulož data do localStorage
          localStorage.setItem("selectedGenerator", JSON.stringify(generator));

          // Přejdi na detail stránku
          window.location.href = "generatorDetail.html";
        });

        list.appendChild(item);
      });
    })
    .catch((error) => {
      console.error("Chyba při načítání generátorů:", error);
    });
});
