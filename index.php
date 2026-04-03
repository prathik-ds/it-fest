<?php include 'includes/header.php'; ?>

<section id="hero" style="min-height: 80vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
    <h3 class="orbitron neon-text-purple" style="letter-spacing: 5px; margin-bottom: 20px; font-size: 1.2rem; opacity: 0.8;">The Ultimate College Fusion</h3>
    <h1 class="orbitron neon-text-blue floating" style="font-size: 8rem; line-height: 1; font-weight: 900; margin-bottom: 0px; filter: drop-shadow(0 0 20px var(--neon-blue))">SYNERGY</h1>
    <h2 class="orbitron neon-text-pink" style="letter-spacing: 12px; margin-top: -10px; margin-bottom: 40px; font-size: 1.8rem; font-weight: 300;">IT FEST &times; COMMERCE FEST</h2>
    
    <p style="max-width: 600px; color: #aaa; font-size: 1.1rem; line-height: 1.6; margin-bottom: 50px;">
        Where <span class="neon-text-blue">technology</span> and <span class="neon-text-purple">business</span> converge. Experience a futuristic competition platform for the leaders of tomorrow.
    </p>

    <!-- Countdown Timer -->
    <div id="countdown" class="glass neon-border-blue" style="padding: 20px 40px; display: flex; gap: 40px; margin-bottom: 60px;">
        <div class="time-box">
            <span id="days" class="orbitron neon-text-blue" style="font-size: 2.5rem; display: block; font-weight: 700;">00</span>
            <span style="font-size: 0.7rem; color: #777;">DAYS</span>
        </div>
        <div class="time-box">
            <span id="hours" class="orbitron neon-text-blue" style="font-size: 2.5rem; display: block; font-weight: 700;">00</span>
            <span style="font-size: 0.7rem; color: #777;">HOURS</span>
        </div>
        <div class="time-box">
            <span id="minutes" class="orbitron neon-text-blue" style="font-size: 2.5rem; display: block; font-weight: 700;">00</span>
            <span style="font-size: 0.7rem; color: #777;">MINS</span>
        </div>
        <div class="time-box">
            <span id="seconds" class="orbitron neon-text-blue" style="font-size: 2.5rem; display: block; font-weight: 700;">00</span>
            <span style="font-size: 0.7rem; color: #777;">SECS</span>
        </div>
    </div>

    <div style="display: flex; gap: 20px;">
        <a href="register.php" class="btn-neon" style="padding: 15px 40px; font-size: 1.1rem;">GET STARTED</a>
        <a href="events.php" class="btn-neon" style="padding: 15px 40px; font-size: 1.1rem; border-color: var(--neon-purple); color: var(--neon-purple);">EXPLORE EVENTS</a>
    </div>
</section>

<!-- Highlights Section -->
<section id="highlights" style="margin-top: 150px; text-align: center;">
    <h2 class="orbitron neon-text-blue" style="margin-bottom: 60px;">Fest Highlights</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
        <div class="glass neon-border-blue" style="padding: 40px; text-align: left;">
            <h3 class="orbitron neon-text-purple" style="margin-bottom: 15px;">IT Tracks</h3>
            <p style="color: #888;">Dive into coding, hackathons, and security challenges meant for the brightest minds in tech.</p>
        </div>
        <div class="glass neon-border-blue" style="padding: 40px; text-align: left; border-color: var(--neon-pink)">
            <h3 class="orbitron neon-text-pink" style="margin-bottom: 15px;">Commerce Tracks</h3>
            <p style="color: #888;">Compete in marketing, finance, and business quizzes to prove your corporate expertise.</p>
        </div>
        <div class="glass neon-border-blue" style="padding: 40px; text-align: left; border-color: var(--neon-blue)">
            <h3 class="orbitron neon-text-blue" style="margin-bottom: 15px;">Mega Rewards</h3>
            <p style="color: #888;">Cash prizes, certificates, and recognition that will boost your career profile.</p>
        </div>
    </div>
</section>

<script>
    // Simple Countdown Script
    const targetDate = new Date('April 15, 2026 10:00:00').getTime();

    function updateCountdown() {
        const now = new Date().getTime();
        const diff = targetDate - now;

        if (diff < 0) return;

        const d = Math.floor(diff / (1000 * 60 * 60 * 24));
        const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const s = Math.floor((diff % (1000 * 60)) / 1000);

        document.getElementById('days').innerText = String(d).padStart(2, '0');
        document.getElementById('hours').innerText = String(h).padStart(2, '0');
        document.getElementById('minutes').innerText = String(m).padStart(2, '0');
        document.getElementById('seconds').innerText = String(s).padStart(2, '0');
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();
</script>

<?php include 'includes/footer.php'; ?>
