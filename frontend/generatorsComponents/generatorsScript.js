fetch("http://localhost:8000/api/generators/list")
  .then((response) => {
    if (!response.ok) {
      throw new Error("Chyba při načítání dat z API");
    }
    return response.json();
  })
  .then((data) => {
    const list = document.getElementById("generatorsList");

    data.forEach((generator) => {
      const item = document.createElement("li");
      item.className =
        "list-group-item d-flex justify-content-between align-items-start";

      item.innerHTML = `
        <div class="ms-2 me-auto">
          <div class="fw-bold">${generator.name}</div>
          Výkon: ${generator.max_output} W<br>
          Zapnutý: <span class="${
            generator.on ? "text-success" : "text-danger"
          }">${generator.on ? "ANO" : "NE"}</span><br>
          Zatížení: ${generator.last_load_percentage} %
        </div>
        <span class="badge text-bg-primary rounded-pill">
          ID: ${generator.id}
        </span>
      `;

      list.appendChild(item);
    });
  })
  .catch((error) => {
    document.body.innerHTML += `<p style="color:red;">${error.message}</p>`;
  });
