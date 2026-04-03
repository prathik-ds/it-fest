// SYNERGY Interactive Scripts

document.addEventListener('DOMContentLoaded', () => {
    console.log("SYNERGY CORE INITIALIZED...");

    // Mouse Glow Effect (Follow pointer)
    const glow = document.createElement('div');
    glow.style.position = 'fixed';
    glow.style.width = '400px';
    glow.style.height = '400px';
    glow.style.borderRadius = '50%';
    glow.style.background = 'radial-gradient(circle, rgba(0, 243, 255, 0.03) 0%, transparent 70%)';
    glow.style.pointerEvents = 'none';
    glow.style.zIndex = '-5';
    glow.style.transform = 'translate(-50%, -50%)';
    glow.style.transition = 'top 0.1s ease-out, left 0.1s ease-out';
    document.body.appendChild(glow);

    document.addEventListener('mousemove', (e) => {
        glow.style.left = e.clientX + 'px';
        glow.style.top = e.clientY + 'px';
    });

    // Form Interactivity: Add neon pulse on focus
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.style.transition = '0.3s';
            input.parentElement.style.transform = 'translateX(5px)';
        });
        input.addEventListener('blur', () => {
            input.parentElement.style.transform = 'translateX(0)';
        });
    });

    // Add subtle reveal animation for glass elements
    const glassElements = document.querySelectorAll('.glass');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                entry.target.style.transition = '0.8s cubic-bezier(0.4, 0, 0.2, 1)';
            }
        });
    }, { threshold: 0.1 });

    glassElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        observer.observe(el);
    });
});
