const params = new URLSearchParams(window.location.search);
const filmId = params.get('id');

// Legge sempre l'XML fresco (non il cache) così le visualizzazioni sono aggiornate
fetch('xml/catalogo.xml')
    .then(r => r.text())
    .then(xmlText => {
        const doc  = new DOMParser().parseFromString(xmlText, "text/xml");
        const nodo = doc.querySelector(`film[id="${filmId}"]`);
        if (!nodo) { document.body.innerHTML = '<p style="padding:40px">Film non trovato.</p>'; return; }

        const film = { id: filmId };
        Array.from(nodo.children).forEach(child => {
            if (child.tagName === "attori") {
                film.attori = Array.from(child.querySelectorAll("attore")).map(a => ({
                    // Supporta sia <attore>Nome</attore> che <attore><nome>Nome</nome></attore>
                    nome:  a.querySelector("nome") ? a.querySelector("nome").textContent : a.textContent,
                    ruolo: a.getAttribute("ruolo")
                }));
            } else {
                film[child.tagName] = child.textContent;
            }
        });

        mostraFilm(film);
    })
    .catch(() => document.body.innerHTML = '<p style="padding:40px">Errore nel caricamento.</p>');

function mostraFilm(film) {
    document.title = film.titolo + ' — Streaming 5BI';

    document.getElementById('poster').src = film.poster || '';
    document.getElementById('poster').alt = film.titolo;

    document.getElementById('titolo').textContent = film.titolo + ' (' + film.anno + ')';
    document.getElementById('regista').textContent = 'Regia di ' + film.regista;

    document.getElementById('genere-durata').innerHTML =
        '<b>Genere:</b> ' + film.genere + ' &nbsp;|&nbsp; <b>Durata:</b> ' + film.durata + ' min';

    const val    = parseFloat(film.valutazione);
    const stelle = '★'.repeat(Math.round(val)) + '☆'.repeat(5 - Math.round(val));
    document.getElementById('valutazione').innerHTML =
        '<b>Valutazione:</b> <span class="stelle">' + stelle + '</span> ' + val + '/5';

    document.getElementById('budget-incassi').innerHTML =
        '<b>Budget:</b> ' + formatDollari(film.budget) +
        ' &nbsp;|&nbsp; <b>Incassi:</b> ' + formatDollari(film.incassi);

    document.getElementById('classificazione').innerHTML =
        '<b>Classificazione:</b> <span class="badge-class">' + (film.classificazione || 'N/D') + '</span>';

    if (film.attori && film.attori.length > 0) {
        const lista = film.attori.map(a =>
            `<span class="badge-attore ${a.ruolo}">${a.nome}</span>`
        ).join('');
        document.getElementById('attori').innerHTML = '<b>Cast:</b> ' + lista;
    }

    document.getElementById('trama').innerHTML = '<b>Trama:</b><br>' + film.trama;

    document.getElementById('visualizzazioni').innerHTML =
        '👁 <span id="num-vis">' + (film.visualizzazioni_totali || 0) + '</span> visualizzazioni';

    // Trailer: converte URL watch in embed
    if (film.trailer) {
        const match = film.trailer.match(/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
        if (match) {
            document.getElementById('trailer').src = 'https://www.youtube.com/embed/' + match[1];
            document.getElementById('trailer-container').style.display = 'block';
        }
    }

    // Incrementa visualizzazioni via PHP dopo aver mostrato la pagina
    incrementaVisualizzazioni(film.id);
}

function formatDollari(val) {
    const n = parseInt(val);
    if (!n || n === 0) return 'N/D';
    return '$' + n.toLocaleString('en-US');
}

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
        }
    })
    .catch(() => {});
}
