<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <link rel="stylesheet" href="../../public/assets/css/me.css">
</head>
<body class="container">
<div class="preloader"></div>
    <header>
        <button><a href="index.php">Retour</a></button>
    </header>
    <main id="main-container">

        <!-- Section 1 -->

        <section id="presentation" class="section-long">
            <h1>Alain Corazzini
                <span class="z z-1">Z</span>
                <span class="z z-2">Z</span>
                <span class="z z-3">Z</span>
                <span class="z z-4">Z</span>
            </h1>
            <img class="img-profile" src="" alt="">
        </section>

        <!-- Section 2 -->

        <section class="section-long" id="section2">
            <h1>section 2</h1>
        </section>

        <!-- Section 3 -->

        <section class="section-long" id="section3">
            <div class="form-container">
                <h1>Contact Me</h1>
                <form id="contactForm" action="send_message.php">
                    <label for="name">Name:</label>
                    <input type="text" name="name" id="name" required>

                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>

                    <label for="tel">Phone:</label>
                    <input type="tel" name="tel" id="tel" required>

                    <label for="subject">Subject:</label>
                    <input type="text" name="subject" id="subject" required>

                    <label for="message">Message:</label>
                    <textarea name="message" id="message" cols="30" rows="10" required></textarea>

                    <input id="check-btn" type="submit" value="Send">
                </form>

                <div id="thanksMessage" style="display:none;">
                    <img src="image/goo.png" alt="Merci pour votre message !">
                </div>
            </div>
        </section>

    </main>
    <script src="me.js"></script>
</body>
</html>
