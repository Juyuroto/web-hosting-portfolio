const container = document.getElementById('main-container');
const sections = document.querySelectorAll('.section-long');

let isScrolling = false;

container.addEventListener('wheel', (e) => {
    if (isScrolling) return;

    e.preventDefault();
    isScrolling = true;

    const delta = e.deltaY;
    const currentIndex = Math.round(container.scrollTop / window.innerHeight);

    let targetIndex = currentIndex;
    if (delta > 0) {
        targetIndex = Math.min(sections.length - 1, currentIndex + 1);
    } else {
        targetIndex = Math.max(0, currentIndex - 1);
    }

    sections[targetIndex].scrollIntoView({ behavior: 'smooth' });

    setTimeout(() => {
        isScrolling = false;
    }, 500);
}, { passive: false });

document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);

    fetch('send_message.php', { method: 'POST', body: formData })
        .then(response => response.text())
        .then(data => {
            document.getElementById('contactForm').style.display = 'none';
            document.getElementById('thanksMessage').style.display = 'block';
        });
});
