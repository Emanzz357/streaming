const parametriUrl = new URLSearchParams(window.location.search);

const titoloSelezionato = parametriUrl.get('titolo');

console.log("Stai visualizzando il film:", titoloSelezionato);

document.title = titoloSelezionato;