document.getElementById('generateDataBtn').addEventListener('click', async () => {

    const response = await fetch('/generate-data', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'patient=25'
    });

    const data = await response.json();

    if (data.success) {
        alert('5 valeurs générées');
    } else {
        alert('Erreur');
    }
});