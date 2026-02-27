document.getElementById('generateDataBtn').addEventListener('click', () => {

    const btn = document.getElementById('generateDataBtn');
    btn.disabled = true;
    btn.textContent = "Live en cours...";

    let compteur = 0;

    const interval = setInterval(async () => {

        await fetch('/generate-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'patient=25'
        });

        compteur++;

        console.log("Valeur générée :", compteur);

        if (compteur >= 5) {
            clearInterval(interval);
            btn.disabled = false;
            btn.textContent = "Générer 5 mesures";
        }

    }, 3000); // 3 secondes
});