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
            const filmObj = {
                id: film.getAttribute("id") || ""
            };

            Array.from(film.children).forEach(child => {
                if (child.tagName === "attori") {
                    filmObj.attori = Array.from(child.querySelectorAll("attore")).map(attore => {
                        return {
                            nome: attore.textContent,
                            ruolo: attore.getAttribute("ruolo") || ""
                        };
                    });
                } else {
                    filmObj[child.tagName] = child.textContent;
                }
            });

            return filmObj;
        });

        localStorage.setItem('elencoFilm', JSON.stringify(elencoFilm));

        elencoFilm.forEach(film => {
            const strutturaFilm = `
                <a href="film.html?id=${encodeURIComponent(film.id)}" class="element">
                    <div class="film">
                        <img src="${film.poster}">
                    </div>
                    <div class="info">
                        <span class="titolo">${film.titolo} (${film.anno})</span>
                        <span class="regista">${film.regista}</span>
                    </div>
                </a>`;

            main.innerHTML += strutturaFilm;
        });
    })
    .catch(err => {
        console.error("Si è verificato un errore:", err);
    });