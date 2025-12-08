const sections = document.querySelectorAll("section");
const navLinks = document.querySelectorAll(".nav-container a");
const indicator = document.querySelector(".nav-indicator");
const navList = document.querySelector(".navigation ul");

let ignoreScrollUntil = 0;

function updateIndicator(activeLink) {
    if(!activeLink) return;
    const linkRect = activeLink.getBoundingClientRect();
    const navRect = navList.getBoundingClientRect();

    const offsetLeft = linkRect.left - navRect.left + linkRect.width / 2 - indicator.offsetWidth / 2;
    indicator.style.left = `${offsetLeft}px`;
}

function getLinkByHash(hash) {
    return Array.from(navLinks).find(l => l.getAttribute("href") === `#${hash}`);
}

navLinks.forEach(link => {
    link.addEventListener("click", (e) => {
        updateIndicator(link);
        const BLOCK_MS = 650;
        ignoreScrollUntil = Date.now() + BLOCK_MS;
    });
});

window.addEventListener("scroll", () => {
    if (Date.now() < ignoreScrollUntil) return;

    let currentSectionId = "";
    sections.forEach(section => {
        const sectionTop = section.getBoundingClientRect().top + window.scrollY;
        if (window.scrollY >= sectionTop - 200) {
            currentSectionId = section.getAttribute("id");
        }
    });

    if (currentSectionId) {
        const activeLink = getLinkByHash(currentSectionId);
        if (activeLink) {
            navLinks.forEach(l => l.classList.toggle('active', l === activeLink));
            updateIndicator(activeLink);
        }
    }
});

window.addEventListener("resize", () => {
    const active = document.querySelector(".nav-container a.active") || navLinks[0];
    updateIndicator(active);
});

window.addEventListener("load", () => {
    const hash = location.hash ? location.hash.slice(1) : null;
    const initialLink = hash ? getLinkByHash(hash) : navLinks[0];
    if (initialLink) {
        setTimeout(() => updateIndicator(initialLink), 50);
    }
});

/* ----------------------------------------------------------------------------*/
document.addEventListener("DOMContentLoaded", () => {
    const track = document.querySelector(".carousel-track");
    const cards = document.querySelectorAll(".deconstructed-card");
    const prevBtn = document.querySelector(".carousel-button.prev");
    const nextBtn = document.querySelector(".carousel-button.next");
    const dotsContainer = document.querySelector(".dots-container");

    cards.forEach((_, index) => {
        const dot = document.createElement("div");
        dot.classList.add("dot");
        if (index === 0) dot.classList.add("active");
        dot.addEventListener("click", () => goToCard(index));
        dotsContainer.appendChild(dot);
    });

    const dots = document.querySelectorAll(".dot");

    const cardWidth = cards[0].offsetWidth;
    const cardMargin = 40;
    const totalCardWidth = cardWidth + cardMargin;

    let currentIndex = 0;

    cards.forEach((card) => {
        card.addEventListener("mousemove", (e) => {
            const rect = card.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width;
            const y = (e.clientY - rect.top) / rect.height;
            const xDeg = (y - 0.5) * 8;
            const yDeg = (x - 0.5) * -8;
            card.style.transform = `perspective(1200px) rotateX(${xDeg}deg) rotateY(${yDeg}deg)`;
            const layers = card.querySelectorAll(".card-layer");
            layers.forEach((layer, index) => {
                const depth = 30 * (index + 1);
                const translateZ = depth;
                const offsetX = (x - 0.5) * 10 * (index + 1);
                const offsetY = (y - 0.5) * 10 * (index + 1);
                layer.style.transform = `translate3d(${offsetX}px, ${offsetY}px, ${translateZ}px)`;
            });
            const waveSvg = card.querySelector(".wave-svg");
            if (waveSvg) {
                const moveX = (x - 0.5) * -20;
                const moveY = (y - 0.5) * -20;
                waveSvg.style.transform = `translate(${moveX}px, ${moveY}px) scale(1.05)`;
                const wavePaths = waveSvg.querySelectorAll("path:not(:first-child)");
                wavePaths.forEach((path, index) => {
                    const factor = 1 + index * 0.5;
                    const waveX = moveX * factor * 0.5;
                    const waveY = moveY * factor * 0.3;
                    path.style.transform = `translate(${waveX}px, ${waveY}px)`;
                });
            }
            const bgObjects = card.querySelectorAll(".bg-object");
            bgObjects.forEach((obj, index) => {
                const factorX = (index + 1) * 10;
                const factorY = (index + 1) * 8;
                const moveX = (x - 0.5) * factorX;
                const moveY = (y - 0.5) * factorY;
                if (obj.classList.contains("square")) {
                    obj.style.transform = `rotate(45deg) translate(${moveX}px, ${moveY}px)`;
                } else if (obj.classList.contains("triangle")) {
                    obj.style.transform = `translate(calc(-50% + ${moveX}px), calc(-50% + ${moveY}px)) scale(1)`;
                } else {
                    obj.style.transform = `translate(${moveX}px, ${moveY}px)`;
                }
            });
        });

        card.addEventListener("mouseleave", () => {
            card.style.transform = "";
            const layers = card.querySelectorAll(".card-layer");
            layers.forEach((layer) => {
                layer.style.transform = "";
            });
            const waveSvg = card.querySelector(".wave-svg");
            if (waveSvg) {
                waveSvg.style.transform = "";
                const wavePaths = waveSvg.querySelectorAll("path:not(:first-child)");
                wavePaths.forEach((path) => {
                    path.style.transform = "";
                });
            }
            const bgObjects = card.querySelectorAll(".bg-object");
            bgObjects.forEach((obj) => {
                if (obj.classList.contains("square")) {
                    obj.style.transform = "rotate(45deg) translateY(-20px)";
                } else if (obj.classList.contains("triangle")) {
                    obj.style.transform = "translate(-50%, -50%) scale(0.5)";
                } else {
                    obj.style.transform = "translateY(20px)";
                }
            });
        });
    });

    function goToCard(index) {
        index = Math.max(0, Math.min(index, cards.length - 1));

        currentIndex = index;
        updateCarousel();
    }

    function updateCarousel() {
        const translateX = -currentIndex * totalCardWidth;

        track.style.transform = `translateX(${translateX}px)`;

        dots.forEach((dot, index) => {
            dot.classList.toggle("active", index === currentIndex);
        });
    }

    prevBtn.addEventListener("click", () => {
        goToCard(currentIndex - 1);
    });

    nextBtn.addEventListener("click", () => {
        goToCard(currentIndex + 1);
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "ArrowLeft") {
            goToCard(currentIndex - 1);
        } else if (e.key === "ArrowRight") {
            goToCard(currentIndex + 1);
        }
    });

    let touchStartX = 0;
    let touchEndX = 0;

    track.addEventListener("touchstart", (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });

    track.addEventListener("touchend", (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });

    function handleSwipe() {
        if (touchStartX - touchEndX > 50) {
            goToCard(currentIndex + 1);
        } else if (touchEndX - touchStartX > 50) {
            goToCard(currentIndex - 1);
        }
    }

    window.addEventListener("resize", () => {
        const newCardWidth = cards[0].offsetWidth;
        const newTotalCardWidth = newCardWidth + cardMargin;

        const translateX = -currentIndex * newTotalCardWidth;
        track.style.transition = "none";
        track.style.transform = `translateX(${translateX}px)`;

        setTimeout(() => {
            track.style.transition = "transform 0.6s cubic-bezier(0.16, 1, 0.3, 1)";
        }, 50);
    });

    updateCarousel();
});
