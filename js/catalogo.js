let elencoFilm = [];
let main = document.querySelector("main");

fetch('../xml/catalogo.xml')
    .then(res => {
        if (!res.ok) throw new Error("File XML non trovato");
        return res.text();
    })
    .then(xmlText => {
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(xmlText, "text/xml");

        const filmNodes = xmlDoc.querySelectorAll("film");

        elencoFilm = Array.from(filmNodes).map(film => {
            return {
                titolo: film.querySelector("titolo")?.textContent || "",
                poster: film.querySelector("poster")?.textContent || ""
            };
        });

        elencoFilm.forEach(film => {
            const estructuraFilm = `
                <div class="element">
                    <div class="film">
                        <img src="${film.poster}">
                    </div>
                    <span class="titolo">${film.titolo}</span>
                </div>`;

            main.innerHTML += estructuraFilm;
        });
    })
    .catch(err => {
        console.error("Si è verificato un errore:", err);
    });