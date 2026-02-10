<?php include __DIR__ . '/../partials/head.php'; ?>
<body>
<?php include __DIR__ . '/../partials/headerPublic.php'; ?>

<main class="container">
    <h1>Connexion</h1>

    <!-- L'action doit pointer vers la route du routeur, pas vers un fichier php -->
    <form action="login" method="POST">
        <div>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">Se connecter</button>
    </form>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>