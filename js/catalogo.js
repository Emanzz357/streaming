let elencoFilm = [];
const main = document.querySelector("main");

// Legge l'XML del catalogo e costruisce le card
fetch('xml/catalogo.xml')
    .then(res => {
        if (!res.ok) throw new Error("File XML non trovato");
        return res.text();
    })
    .then(xmlText => {
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(xmlText, "text/xml");

        // Mappa ogni nodo <film> in un oggetto JavaScript
        elencoFilm = Array.from(xmlDoc.querySelectorAll("film")).map(film => {
            const obj = { id: film.getAttribute("id") };

            Array.from(film.children).forEach(child => {
                if (child.tagName === "attori") {
                    // Gli attori diventano array di oggetti {nome, ruolo}
                    obj.attori = Array.from(child.querySelectorAll("attore")).map(a => ({
                        nome:  a.textContent,
                        ruolo: a.getAttribute("ruolo")
                    }));
                } else {
                    obj[child.tagName] = child.textContent;
                }
            });

            return obj;
        });

        // Salva in localStorage: film.js lo usa per non dover rileggere l'XML
        localStorage.setItem('elencoFilm', JSON.stringify(elencoFilm));

        // Costruisce una card per ogni film
        elencoFilm.forEach(film => {
            const stelle = generaStelle(parseFloat(film.valutazione));

            main.innerHTML += `
                <a href="film.html?id=${encodeURIComponent(film.id)}" class="element">
                    <div class="film">
                        <img src="${film.poster}" alt="${film.titolo}">
                    </div>
                    <div class="info">
                        <span class="titolo">${film.titolo} (${film.anno})</span>
                        <span class="regista">${film.regista}</span>
                        <span class="genere">${film.genere} · ${film.durata} min</span>
                        <span class="stelle">${stelle}</span>
                        <span class="vis">👁 ${film.visualizzazioni_totali} visualizzazioni</span>
                    </div>
                </a>`;
        });
    })
    .catch(err => console.error("Errore:", err));

// Genera stringa di stelle da un valore 0-5 — es: 4.3 → "★★★★☆ (4.3)"
function generaStelle(val) {
    const intere = Math.round(val);
    return '★'.repeat(intere) + '☆'.repeat(5 - intere) + ` (${val})`;
}

// Mostra/nasconde il pannello di ricerca TMDb
function togglePannelloAggiungi() {
    const pannello = document.getElementById('pannello-aggiungi');
    pannello.style.display = pannello.style.display === 'none' ? 'block' : 'none';
    document.getElementById('risultati-ricerca').innerHTML = '';
    document.getElementById('input-titolo').value = '';
}

// Chiama cerca_film.php con il titolo digitato
function cercaFilm() {
    const titolo = document.getElementById('input-titolo').value.trim();
    if (!titolo) return;

    const div = document.getElementById('risultati-ricerca');
    div.innerHTML = '<p>Ricerca in corso...</p>';

    fetch('php/cerca_film.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    'titolo=' + encodeURIComponent(titolo)
    })
    .then(r => r.json())
    .then(data => {
        if (data.errore) {
            div.innerHTML = `<p class="msg-errore">${data.errore}</p>`;
            return;
        }
        div.innerHTML = data.risultati.map(f => `
            <div class="card-risultato">
                <img src="${f.poster || ''}" alt="${f.titolo}">
                <div class="card-risultato-info">
                    <strong>${f.titolo}</strong> (${f.anno})
                    <button onclick="aggiungiFilm(${f.tmdb_id}, '${f.titolo.replace(/'/g, "\\'")}')">
                        + Aggiungi
                    </button>
                </div>
            </div>
        `).join('');
    })
    .catch(() => div.innerHTML = '<p class="msg-errore">Errore di connessione.</p>');
}

// Chiama aggiungi_film.php con il tmdb_id del film scelto
function aggiungiFilm(tmdb_id, titolo) {
    const div = document.getElementById('risultati-ricerca');
    div.innerHTML = `<p>Aggiunta di "${titolo}" in corso...</p>`;

    fetch('php/aggiungi_film.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    'tmdb_id=' + tmdb_id
    })
    .then(r => r.json())
    .then(data => {
        if (data.errore) {
            div.innerHTML = `<p class="msg-errore">${data.errore}</p>`;
            return;
        }
        div.innerHTML = `<p class="msg-ok">✓ "${data.titolo}" aggiunto con successo!</p>`;
        // Ricarica la pagina dopo 1.2s per mostrare il nuovo film nel catalogo
        setTimeout(() => location.reload(), 1200);
    })
    .catch(() => div.innerHTML = '<p class="msg-errore">Errore di connessione.</p>');
}

// Permette di premere Invio nel campo di ricerca
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('input-titolo');
    if (input) input.addEventListener('keydown', e => { if (e.key === 'Enter') cercaFilm(); });
});
