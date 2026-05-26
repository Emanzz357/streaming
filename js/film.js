const params        = new URLSearchParams(window.location.search);
const filmId        = params.get('id');

// Prova prima il localStorage (già popolato da catalogo.js)
// Se non c'è (apertura diretta di film.html), rilégge l'XML
const cached = localStorage.getItem('elencoFilm');

if (cached) {
    const elenco = JSON.parse(cached);
    const film   = elenco.find(f => f.id === filmId);
    if (film) {
        mostraFilm(film);
    } else {
        document.body.innerHTML = '<p style="padding:40px">Film non trovato.</p>';
    }
} else {
    // Nessun cache: rilégge l'XML direttamente
    fetch('xml/catalogo.xml')
        .then(r => r.text())
        .then(xmlText => {
            const doc  = new DOMParser().parseFromString(xmlText, "text/xml");
            const nodo = doc.querySelector(`film[id="${filmId}"]`);
            if (!nodo) { document.body.innerHTML = '<p>Film non trovato.</p>'; return; }

            // Costruisce oggetto film dal nodo XML
            const film = { id: filmId };
            Array.from(nodo.children).forEach(child => {
                if (child.tagName === "attori") {
                    film.attori = Array.from(child.querySelectorAll("attore")).map(a => ({
                        nome:  a.textContent,
                        ruolo: a.getAttribute("ruolo")
                    }));
                } else {
                    film[child.tagName] = child.textContent;
                }
            });
            mostraFilm(film);
        })
        .catch(() => document.body.innerHTML = '<p>Errore nel caricamento del film.</p>');
}

// Popola tutti gli elementi HTML con i dati del film
function mostraFilm(film) {
    // Titolo del tab del browser
    document.title = film.titolo + ' — Streaming 5BI';

    // Poster
    document.getElementById('poster').src = film.poster;
    document.getElementById('poster').alt = film.titolo;

    // Titolo e anno
    document.getElementById('titolo').textContent = film.titolo + ' (' + film.anno + ')';

    // Regista
    document.getElementById('regista').textContent = 'Regia di ' + film.regista;

    // Genere e durata
    document.getElementById('genere-durata').innerHTML =
        '<b>Genere:</b> ' + film.genere + ' &nbsp;|&nbsp; <b>Durata:</b> ' + film.durata + ' min';

    // Valutazione con stelle
    const val    = parseFloat(film.valutazione);
    const stelle = '★'.repeat(Math.round(val)) + '☆'.repeat(5 - Math.round(val));
    document.getElementById('valutazione').innerHTML =
        '<b>Valutazione:</b> <span class="stelle">' + stelle + '</span> ' + val + '/5';

    // Budget e incassi formattati in dollari
    document.getElementById('budget-incassi').innerHTML =
        '<b>Budget:</b> ' + formatDollari(film.budget) +
        ' &nbsp;|&nbsp; <b>Incassi:</b> ' + formatDollari(film.incassi);

    // Classificazione età
    document.getElementById('classificazione').innerHTML =
        '<b>Classificazione:</b> <span class="badge-class">' + film.classificazione + '</span>';

    // Lista attori
    if (film.attori && film.attori.length > 0) {
        const lista = film.attori.map(a =>
            `<span class="badge-attore ${a.ruolo}">${a.nome}</span>`
        ).join('');
        document.getElementById('attori').innerHTML = '<b>Cast:</b> ' + lista;
    }

    // Trama
    document.getElementById('trama').innerHTML = '<b>Trama:</b><br>' + film.trama;

    // Visualizzazioni (valore iniziale dall'XML, poi aggiornato dal PHP)
    document.getElementById('visualizzazioni').innerHTML =
        '👁 <span id="num-vis">' + film.visualizzazioni_totali + '</span> visualizzazioni';

    // Trailer: converte URL YouTube watch in URL embed per l'iframe
    // es: youtube.com/watch?v=ABC → youtube.com/embed/ABC
    if (film.trailer) {
        const videoId = estraiVideoId(film.trailer);
        if (videoId) {
            document.getElementById('trailer').src =
                'https://www.youtube.com/embed/' + videoId;
            document.getElementById('trailer-container').style.display = 'block';
        }
    }

    // Incrementa il contatore visualizzazioni via PHP
    // Lo facciamo DOPO aver mostrato la pagina per non bloccare il rendering
    incrementaVisualizzazioni(film.id);
}

// Estrae l'ID video da un URL YouTube
// Funziona con: youtube.com/watch?v=ID e youtu.be/ID
function estraiVideoId(url) {
    const match = url.match(/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    return match ? match[1] : null;
}

// Formatta un numero in stringa dollari — es: 160000000 → "$160,000,000"
function formatDollari(val) {
    const n = parseInt(val);
    if (!n || n === 0) return 'N/D';
    return '$' + n.toLocaleString('en-US');
}

// Chiama incrementa_visualizzazioni.php e aggiorna il numero a schermo
function incrementaVisualizzazioni(filmId) {
    fetch('php/incrementa_visualizzazioni.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    'film_id=' + filmId
    })
    .then(r => r.json())
    .then(data => {
        if (data.successo) {
            const el = document.getElementById('num-vis');
            if (el) el.textContent = data.visualizzazioni;

            // ✅ AGGIUNTA: aggiorna anche il localStorage
            const cached = localStorage.getItem('elencoFilm');
            if (cached) {
                const elenco = JSON.parse(cached);
                const film = elenco.find(f => f.id === filmId);
                if (film) {
                    film.visualizzazioni_totali = String(data.visualizzazioni);
                    localStorage.setItem('elencoFilm', JSON.stringify(elenco));
                }
            }
        }
    })
    .catch(() => {});
}
