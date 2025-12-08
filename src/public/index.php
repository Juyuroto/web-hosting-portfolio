<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style/style.css">
    <title>Portfolio</title>
</head>
<body>
<header>
    <div class="header-container">
        <button>
            <div class="svg-wrapper-1">
                <div class="svg-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="30" height="30" class="icon-top">
                        <path d="M22,15.04C22,17.23 20.24,19 18.07,19H5.93C3.76,19 2,17.23 2,15.04C2,13.07 3.43,11.44 5.31,11.14C5.28,11 5.27,10.86 5.27,10.71C5.27,9.33 6.38,8.2 7.76,8.2C8.37,8.2 8.94,8.43 9.37,8.8C10.14,7.05 11.13,5.44 13.91,5.44C17.28,5.44 18.87,8.06 18.87,10.83C18.87,10.94 18.87,11.06 18.86,11.17C20.65,11.54 22,13.13 22,15.04Z"></path>
                    </svg>
                </div>
            </div>
            <span><a href="cv.pdf" download="Corazzini Alain CV">My CV</a></span>
        </button>
        <nav class="navigation">
            <ul>
                <li class="nav-container"><a href="#presentation">About Me</a></li>
                <li class="nav-container"><a href="#my-projets">Project</a></li>
                <li class="nav-container"><a href="#my-skills">Skills</a></li>
                <li class="nav-container"><a href="#experience">Experience</a></li>
                <li class="nav-container"><a href="#contact">Contact</a></li>
            </ul>
            <div class="nav-indicator"></div>
        </nav>
    </div>
</header>

<main>

    <!-- Section 1 : About -->
    <section id="presentation" class="section-long">
            <button class="magic-button"><a href="me.php">About Me</a></button>
    </section>

    <!-- Section 2 : Project -->
    <section id="my-projets" class="section-long">
        <div class="carousel">
            <div class="carousel-track">

                <!-- Card 1 -->
                <article class="deconstructed-card">
                    <div class="card-layer card-image">
                        <svg class="wave-svg" viewBox="0 0 300 400" preserveAspectRatio="none">
                            <rect width="100%" height="100%" fill="#000000" />
                            <path d="M0,230 C30,220 60,240 90,230 C120,220 150,240 180,230 C210,220 240,240 270,230 C290,225 295,230 300,225 L300,400 L0,400 Z" fill="#303030" opacity="0.8" />
                            <path d="M0,260 C40,250 80,270 120,260 C160,250 200,270 240,260 C280,250 290,260 300,255 L300,400 L0,400 Z" fill="#494949" opacity="0.9" />
                            <path d="M0,290 C50,280 100,300 150,290 C200,280 250,300 300,290 L300,400 L0,400 Z" fill="#616161" opacity="0.9" />
                        </svg>
                    </div>
                    <div class="card-layer card-frame">
                        <svg viewBox="0 0 300 400" preserveAspectRatio="none">
                            <path class="frame-path" d="M 20,20 H 280 V 380 H 20 Z" />
                        </svg>
                    </div>
                    <div class="card-layer card-content pregnancy-content">
                        <div class="content-fragment fragment-heading">
                            <h2 class="content-text">Python invaders</h2>
                            <h3 class="content-subtext">Game in python with Pygame</h3>
                        </div>
                        <div class="content-fragment fragment-meta">
                            <div class="meta-line"></div>
                            <span class="meta-text">Medium</span>
                        </div>
                        <div class="content-fragment fragment-body">
                            <p class="content-text">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Blanditiis nemo vitae dolores aperiam delectus beatae eligendi sunt voluptates recusandae obcaecati cupiditate saepe rem quam laboriosam asperiores, dignissimos maxime praesentium commodi.</p>
                        </div>
                        <div class="content-fragment fragment-cta">
                            <a href="#" class="cta-link" target="_blank">
                                <div class="cta-box"></div>
                                <span class="cta-text">About</span>
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Card 2 -->
                <article class="deconstructed-card">
                    <div class="card-layer card-image">
                        <svg class="wave-svg" viewBox="0 0 300 400" preserveAspectRatio="none">
                            <rect width="100%" height="100%" fill="#000000" />
                            <path d="M0,230 C30,220 60,240 90,230 C120,220 150,240 180,230 C210,220 240,240 270,230 C290,225 295,230 300,225 L300,400 L0,400 Z" fill="#303030" opacity="0.8" />
                            <path d="M0,260 C40,250 80,270 120,260 C160,250 200,270 240,260 C280,250 290,260 300,255 L300,400 L0,400 Z" fill="#494949" opacity="0.9" />
                            <path d="M0,290 C50,280 100,300 150,290 C200,280 250,300 300,290 L300,400 L0,400 Z" fill="#616161" opacity="0.9" />
                        </svg>
                    </div>
                    <div class="card-layer card-frame">
                        <svg viewBox="0 0 300 400" preserveAspectRatio="none">
                            <path class="frame-path" d="M 20,20 H 280 V 380 H 20 Z" />
                        </svg>
                    </div>
                    <div class="card-layer card-content pregnancy-content">
                        <div class="content-fragment fragment-heading">
                            <h2 class="content-text">Portfolio</h2>
                            <h3 class="content-subtext">My portfolio</h3>
                        </div>
                        <div class="content-fragment fragment-meta">
                            <div class="meta-line"></div>
                            <span class="meta-text">Easy</span>
                        </div>
                        <div class="content-fragment fragment-body">
                            <p class="content-text">Lorem ipsum dolor sit amet consectetur adipisicing elit. Temporibus labore aliquam dolor accusamus tenetur error tempore ullam eligendi, mollitia blanditiis veniam in illo, ea voluptates. Odio rem magnam distinctio ullam.</p>
                        </div>
                        <div class="content-fragment fragment-cta">
                            <a href="#" class="cta-link" target="_blank">
                                <div class="cta-box"></div>
                                <span class="cta-text">About</span>
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Card 3 -->
                <article class="deconstructed-card">
                    <div class="card-layer card-image">
                        <svg class="wave-svg" viewBox="0 0 300 400" preserveAspectRatio="none">
                            <rect width="100%" height="100%" fill="#000000" />
                            <path d="M0,230 C30,220 60,240 90,230 C120,220 150,240 180,230 C210,220 240,240 270,230 C290,225 295,230 300,225 L300,400 L0,400 Z" fill="#303030" opacity="0.8" />
                            <path d="M0,260 C40,250 80,270 120,260 C160,250 200,270 240,260 C280,250 290,260 300,255 L300,400 L0,400 Z" fill="#494949" opacity="0.9" />
                            <path d="M0,290 C50,280 100,300 150,290 C200,280 250,300 300,290 L300,400 L0,400 Z" fill="#616161" opacity="0.9" />
                        </svg>
                    </div>
                    <div class="card-layer card-frame">
                        <svg viewBox="0 0 300 400" preserveAspectRatio="none">
                            <path class="frame-path" d="M 20,20 H 280 V 380 H 20 Z" />
                        </svg>
                    </div>
                    <div class="card-layer card-content pregnancy-content">
                        <div class="content-fragment fragment-heading">
                            <h2 class="content-text">Portfolio in PHP <br> & SQL & Docker</h2>
                            <h3 class="content-subtext">My portfolio Dynamique</h3>
                        </div>
                        <div class="content-fragment fragment-meta">
                            <div class="meta-line"></div>
                            <span class="meta-text">Medium</span>
                        </div>
                        <div class="content-fragment fragment-body">
                            <p class="content-text">Lorem ipsum dolor sit amet consectetur adipisicing elit. Hic eveniet, ut reprehenderit ea, rerum at labore, eos eligendi deserunt culpa vel pariatur dolores unde possimus magni deleniti expedita reiciendis debitis.</p>
                        </div>
                        <div class="content-fragment fragment-cta">
                            <a href="#" class="cta-link" target="_blank">
                                <div class="cta-box"></div>
                                <span class="cta-text">About</span>
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Card 4 -->
                <article class="deconstructed-card">
                    <div class="card-layer card-image">
                        <svg class="wave-svg" viewBox="0 0 300 400" preserveAspectRatio="none">
                            <rect width="100%" height="100%" fill="#000000" />
                            <path d="M0,230 C30,220 60,240 90,230 C120,220 150,240 180,230 C210,220 240,240 270,230 C290,225 295,230 300,225 L300,400 L0,400 Z" fill="#303030" opacity="0.8" />
                            <path d="M0,260 C40,250 80,270 120,260 C160,250 200,270 240,260 C280,250 290,260 300,255 L300,400 L0,400 Z" fill="#494949" opacity="0.9" />
                            <path d="M0,290 C50,280 100,300 150,290 C200,280 250,300 300,290 L300,400 L0,400 Z" fill="#616161" opacity="0.9" />
                        </svg>
                    </div>
                    <div class="card-layer card-frame">
                        <svg viewBox="0 0 300 400" preserveAspectRatio="none">
                            <path class="frame-path" d="M 20,20 H 280 V 380 H 20 Z" />
                        </svg>
                    </div>
                    <div class="card-layer card-content pregnancy-content">
                        <div class="content-fragment fragment-heading">
                            <h2 class="content-text">Personnal Cloud</h2>
                            <h3 class="content-subtext">Open Cloud host in my house</h3>
                        </div>
                        <div class="content-fragment fragment-meta">
                            <div class="meta-line"></div>
                            <span class="meta-text">Very Hard</span>
                        </div>
                        <div class="content-fragment fragment-body">
                            <p class="content-text">Lorem ipsum dolor sit amet consectetur adipisicing elit. Soluta in rerum nobis ab, libero ipsa ipsam consequatur blanditiis odio tempore repudiandae qui eligendi quis quas, sapiente dolor maxime dolores facere.</p>
                        </div>
                        <div class="content-fragment fragment-cta">
                            <a href="#" class="cta-link" target="_blank">
                                <div class="cta-box"></div>
                                <span class="cta-text">About</span>
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Card 5 -->
                <article class="deconstructed-card">
                    <div class="card-layer card-image">
                        <svg class="wave-svg" viewBox="0 0 300 400" preserveAspectRatio="none">
                            <rect width="100%" height="100%" fill="#000000" />
                            <path d="M0,230 C30,220 60,240 90,230 C120,220 150,240 180,230 C210,220 240,240 270,230 C290,225 295,230 300,225 L300,400 L0,400 Z" fill="#303030" opacity="0.8" />
                            <path d="M0,260 C40,250 80,270 120,260 C160,250 200,270 240,260 C280,250 290,260 300,255 L300,400 L0,400 Z" fill="#494949" opacity="0.9" />
                            <path d="M0,290 C50,280 100,300 150,290 C200,280 250,300 300,290 L300,400 L0,400 Z" fill="#616161" opacity="0.9" />
                        </svg>
                    </div>
                    <div class="card-layer card-frame">
                        <svg viewBox="0 0 300 400" preserveAspectRatio="none">
                            <path class="frame-path" d="M 20,20 H 280 V 380 H 20 Z" />
                        </svg>
                    </div>
                    <div class="card-layer card-content pregnancy-content">
                        <div class="content-fragment fragment-heading">
                            <h2 class="content-text">Showcase Website</h2>
                            <h3 class="content-subtext">A website for my father</h3>
                        </div>
                        <div class="content-fragment fragment-meta">
                            <div class="meta-line"></div>
                            <span class="meta-text">Easy</span>
                        </div>
                        <div class="content-fragment fragment-body">
                            <p class="content-text">Lorem ipsum, dolor sit amet consectetur adipisicing elit. Cum et, tenetur quia in soluta laudantium dolore natus libero vel consequuntur recusandae laborum facere similique facilis nostrum fugiat magnam excepturi ut.</p>
                        </div>
                        <div class="content-fragment fragment-cta">
                            <a href="#" class="cta-link" target="_blank">
                                <div class="cta-box"></div>
                                <span class="cta-text">About</span>
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Card 6 -->
            </div>

            <div class="carousel-controls">
                <button class="carousel-button prev">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>
                <button class="carousel-button next">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>

            <div class="dots-container"></div>
        </div>
    </section>

    <!-- Section 3 : Skills -->
    <section id="my-skills" class="section-ultra-long">
        
        <!-- Skills Code -->
        <h1 class="skills-title">Code Skills</h1>
        <div class="skills">
            <div class="skills-row">
                <div class="skills-column">
                    
                    <div class="skills-box">
                        <div class="progress">
                            <h3>HTML<span>25%</span></h3>
                            <div class="bar"><span style="width: 25%;"></span></div>
                        </div>

                        <div class="progress">
                            <h3>CSS<span>30%</span></h3>
                            <div class="bar"><span style="width: 30%;"></span></div>
                        </div>

                        <div class="progress">
                            <h3>JS<span>5%</span></h3>
                            <div class="bar"><span style="width: 5%;"></span></div>
                        </div>

                        <div class="progress">
                            <h3>C<span>2%</span></h3>
                            <div class="bar"><span style="width: 2%;"></span></div>
                        </div>

                        <div class="progress">
                            <h3>PHP<span>10%</span></h3>
                            <div class="bar"><span style="width: 10%;"></span></div>
                        </div>
                    </div>
                </div>
                <div class="skills-column">
                    
                    <div class="skills-box">
                        <div class="progress">
                            <h3>Docker<span>5%</span></h3>
                            <div class="bar"><span style="width: 5%;"></span></div>
                        </div>

                        <div class="progress">
                            <h3>SQL<span>20%</span></h3>
                            <div class="bar"><span style="width: 20%;"></span></div>
                        </div>

                        <div class="progress">
                            <h3>Python<span>30%</span></h3>
                            <div class="bar"><span style="width: 30%;"></span></div>
                        </div>

                        <div class="progress">
                            <h3><span>0%</span></h3>
                            <div class="bar"><span style="width: 0%;"></span></div>
                        </div>

                        <div class="progress">
                            <h3><span>0%</span></h3>
                            <div class="bar"><span style="width: 0%;"></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skills Network -->

        <h1 class="skills-title">Network Skills</h1>
        <div class="skills">
            <div class="skills-row">
                <div class="skills-column">
                    
                    <div class="skills-box">
                        <div class="progress">
                            <h3>Installation & configuration<span>25%</span></h3>
                            <div class="bar"><span style="width: 25%;"></span></div>
                        </div>

                        <div class="progress">
                            <h3>Administration<span>30%</span></h3>
                            <div class="bar"><span style="width: 30%;"></span></div>
                        </div>

                        <div class="progress">
                            <h3>Maintenance<span>20%</span></h3>
                            <div class="bar"><span style="width: 20%;"></span></div>
                        </div>

                        <div class="progress">
                            <h3>Sécurité basique<span>45%</span></h3>
                            <div class="bar"><span style="width: 45%;"></span></div>
                        </div>

                        <div class="progress">
                            <h3>Virtualisation / Cloud<span>10%</span></h3>
                            <div class="bar"><span style="width: 10%;"></span></div>
                        </div>

                    </div>
                    
                </div>

                <div class="skills-column">
                    
                    <div class="skills-box">
                        <div class="progress">
                            <h3><span>0%</span></h3>
                            <div class="bar"><span style="width: 0%;"></span></div>
                        </div>

                        <div class="progress">
                            <h3><span>0%</span></h3>
                            <div class="bar"><span style="width: 0%;"></span></div>
                        </div>

                        <div class="progress">
                            <h3><span>0%</span></h3>
                            <div class="bar"><span style="width: 0%;"></span></div>
                        </div>

                        <div class="progress">
                            <h3><span>0%</span></h3>
                            <div class="bar"><span style="width: 0%;"></span></div>
                        </div>

                        <div class="progress">
                            <h3><span>0%</span></h3>
                            <div class="bar"><span style="width: 0%;"></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skills language -->

        <h1 class="skills-title">Language Skills</h1>
        <div class="skills">
            <div class="skills-row">
                <div class="skills-column">
                    
                    <div class="skills-box">
                        <div class="progress">
                            <h3>French<span>100%</span></h3>
                            <div class="bar"><span style="width: 100%;"></span></div>
                        </div>
                    </div>
                    
                </div>

                <div class="skills-column">
                    
                    <div class="skills-box">
                        <div class="progress">
                            <h3>English<span>20%</span></h3>
                            <div class="bar"><span style="width: 20%;"></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!-- Section 4 : Experience -->

    <section id="experience" class="section-ultra-long">

        <div class="experience-container">
            <div class="row">
                <section class="col">
                    <div class="title">
                        <h2>EDUCATION</h2>
                    </div>

                    <div class="contents">
                        
                        <div class="box">
                            <h4>September 2022 at Juin 2025</h4>
                            <h3>Maryse Bastie High School</h3>
                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
                        </div>

                        <div class="box">
                            <h4>October 2023 at April 2024</h4>
                            <h3>PSE 1</h3>
                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
                        </div>

                        <div class="box">
                            <h4>October 2023 at April 2024</h4>
                            <h3>BNSSA</h3>
                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
                        </div>

                        <div class="box">
                            <h4>September 2025 at Juin 2028</h4>
                            <h3>Cloud Bachelor</h3>
                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
                        </div>

                    </div>
                    </section>

                    <!-- Experience -->

                    <section class="col">
                        
                        <header class="title">
                            <h2>EXPERIENCE</h2>
                        </header>

                        <div class="contents">
                            
                            <div class="box">
                                <h4>29/04 At 24/05 - 2024</h4>
                                <h3>C&C Apple</h3>
                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
                            </div>

                            <div class="box">
                                <h4>30/09 At 18/10 - 2024</h4>
                                <h3>Groupe 3iL</h3>
                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
                            </div>

                            <div class="box">
                                <h4>03/02 At 21/02 - 2025</h4>
                                <h3>Koesio Limoges</h3>
                                <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Velit, recusandae iusto ipsa praesentium iure deleniti, ad minima id molestias perferendis quasi nostrum cum dolor voluptate. Laborum, porro! Distinctio, repellendus dolor?</p>
                            </div>

                            <div class="box">
                                <h4>2019 At 2022</h4>
                                <h3>Restaurant Beausoleil</h3>
                                <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Velit, recusandae iusto ipsa praesentium iure deleniti, ad minima id molestias perferendis quasi nostrum cum dolor voluptate. Laborum, porro! Distinctio, repellendus dolor?</p>
                            </div>

                            <div class="box">
                                <h4>2024 At 2025</h4>
                                <h3>Lifeguard</h3>
                                <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Velit, recusandae iusto ipsa praesentium iure deleniti, ad minima id molestias perferendis quasi nostrum cum dolor voluptate. Laborum, porro! Distinctio, repellendus dolor?</p>
                            </div>

                        </div>
                </section>
            </div>
        </div>
    </section>

    <!-- Section 5 : Contact -->
    <section id="contact" class="contact section-long">
        <div class="container">
            <a href="mailto:alain.corazzini@epitech.eu" class="icon icon-mail">
            <img src="image/icon/mail.svg" alt="Mail" class="icon-img">
            </a>
            <a href="tel:0698329497" class="icon icon-dis">
            <img src="image/icon/phone.svg" alt="Discord" class="icon-img">
            </a>

            <a href="https://github.com/Juyuroto" class="icon icon-github">
            <img src="image/icon/github.svg" alt="GitHub" class="icon-img">
            </a>

            <a href="https://www.linkedin.com/in/alain-corazzini-b81a90359/" class="icon icon-in">
            <img src="image/icon/linkedin.svg" alt="LinkedIn" class="icon-img">
            </a>
        </div>
    </section>

</main>

<footer>
    <p>© 2025 CORAZZINI <a href="/CRUD/index.php">Alain</a>. Tous droits réservés.</p>
</footer>

<script src="script.js"></script>
<script src="serveur.js"></script>
</body>
</html>
