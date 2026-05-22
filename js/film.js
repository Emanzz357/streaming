const elencoFilm = JSON.parse(localStorage.getItem('elencoFilm')) || [];

const parametriUrl = new URLSearchParams(window.location.search);

const filmId = parametriUrl.get('id');

const filmSelezionato = elencoFilm.find(film => film.id === filmId);

document.title = filmSelezionato.titolo;
document.querySelector("img").src = filmSelezionato.poster;

let infoDiv = document.querySelector("div.info");

infoDiv.querySelector("h1").textContent = filmSelezionato.titolo + " (" + filmSelezionato.anno + ")";

infoDiv.querySelector("h2").textContent = "Regista: " + filmSelezionato.regista;

infoDiv.querySelector("p#genere-durata").innerHTML = "<b>Genere:</b> " + filmSelezionato.genere + " | <b>Durata:</b> " + filmSelezionato.durata + "min";

infoDiv.querySelector("p#trama").innerHTML = "<b>Trama:</b> <br/>" + filmSelezionato.trama;