<?php include 'includes/header.php'; ?>

<style>
    /* Premium Vector Graphics Component */
    .synergy-header-wrapper {
        position: relative;
        width: 100%;
        max-width: 1100px;
        margin: 0 auto 50px;
        padding: 60px 40px;
        background: #080808;
        border: 2px solid rgba(0, 243, 255, 0.2);
        border-radius: 15px;
        overflow: hidden;
        box-shadow: inset 0 0 50px rgba(0, 243, 255, 0.05), 0 20px 50px rgba(0,0,0,0.5);
    }

    .synergy-header-wrapper::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background-image: 
            radial-gradient(circle at 10% 10%, rgba(0, 243, 255, 0.05) 0%, transparent 20%),
            radial-gradient(circle at 90% 90%, rgba(50, 255, 50, 0.05) 0%, transparent 20%);
        pointer-events: none;
    }

    /* Circuit patterns */
    .circuit-lines {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0; left: 0;
        opacity: 0.15;
        z-index: 0;
        pointer-events: none;
    }

    .synergy-main-title {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .synergy-text {
        font-size: 8.5rem;
        font-weight: 950;
        letter-spacing: -2px;
        margin: 0;
        line-height: 0.9;
        display: flex;
        gap: 0;
    }

    .synergy-text span:nth-child(even) {
        color: transparent;
        -webkit-text-stroke: 2px #32ff32;
        text-shadow: 0 0 15px rgba(50, 255, 50, 0.4);
    }

    .synergy-text span:nth-child(odd) {
        color: #00f3ff;
        text-shadow: 0 0 20px rgba(0, 243, 255, 0.8), 0 0 40px rgba(0, 243, 255, 0.4);
    }

    .subtitle-accent {
        font-size: 2.2rem;
        font-weight: 400;
        letter-spacing: 5px;
        margin-top: 20px;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .subtitle-accent .bca { color: var(--neon-blue); text-shadow: 0 0 10px var(--neon-blue); }
    .subtitle-accent .bcom { color: #32ff32; text-shadow: 0 0 10px #32ff32; }
    .subtitle-accent .ampersand { color: #fff; opacity: 0.5; font-size: 1.5rem; }

    /* Code & Finance Deco */
    .deco-item {
        position: absolute;
        font-family: monospace;
        font-size: 0.75rem;
        pointer-events: none;
        opacity: 0.6;
        z-index: 1;
    }

    .deco-code { 
        color: #00f3ff; 
        text-align: left;
        line-height: 1.5;
    }
    .deco-finance { 
        color: #32ff32; 
        text-align: right;
    }

    .floating-symbols {
        font-size: 2rem;
        filter: blur(1px);
        opacity: 0.3;
        animation: float 6s infinite ease-in-out;
    }

    @media (max-width: 768px) {
        .synergy-text { font-size: 4rem; }
        .subtitle-accent { font-size: 1.2rem; }
    }
</style>

<section id="hero" style="min-height: 90vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
    
    <!-- Custom SVG/CSS Vector Header -->
    <div class="synergy-header-wrapper floating">
        <!-- Circuit Line SVG Overlay -->
        <svg class="circuit-lines" viewBox="0 0 1000 400" preserveAspectRatio="none">
            <path d="M0,100 L100,100 L150,50 L300,50 L350,100 L650,100 L700,150 L1000,150" fill="none" stroke="#00f3ff" stroke-width="1" />
            <path d="M1000,300 L900,300 L850,350 L700,350 L650,300 L350,300 L300,250 L0,250" fill="none" stroke="#32ff32" stroke-width="1" />
            <circle cx="100" cy="100" r="3" fill="#00f3ff" />
            <circle cx="900" cy="300" r="3" fill="#32ff32" />
        </svg>

        <!-- Floating Code Decorations -->
        <div class="deco-item deco-code" style="top: 20px; left: 30px;">
            <span style="color: #bc13fe">import</span> tensorflow <span style="color: #bc13fe">as</span> tf<br>
            print(<span style="color: #32ff32">"BCA"</span>)
        </div>
        <div class="deco-item deco-code" style="bottom: 20px; left: 30px;">
            <span style="color: grey">// public void main</span><br>
            System.out.println(<span style="color: #00f3ff">"IT"</span>);
        </div>

        <!-- Floating Finance Decorations -->
        <div class="deco-item deco-finance" style="top: 20px; right: 30px;">
            $ € £ ¥<br>
            <span style="font-size: 1.5rem; color: #fff;">📈</span>
        </div>
        <div class="deco-item deco-finance" style="bottom: 20px; right: 30px;">
            ACCOUNTS & LAW<br>
            <span style="font-size: 1.2rem;">📊 💹</span>
        </div>

        <div class="synergy-main-title">
            <h1 class="synergy-text orbitron">
                <span>S</span><span>Y</span><span>N</span><span>E</span><span>R</span><span>G</span><span>Y</span>
            </h1>
            <div class="subtitle-accent orbitron">
                <span class="bca">BCA</span>
                <span class="ampersand">&</span>
                <span class="bcom">B.COM FEST</span>
            </div>
        </div>
    </div>
    
    <p style="max-width: 600px; color: #aaa; font-size: 1.1rem; line-height: 1.6; margin-bottom: 50px;">
        Where <span class="neon-text-blue">technology</span> and <span class="neon-text-purple">business</span> converge. Experience a futuristic competition platform for the leaders of tomorrow.
    </p>

    <div style="display: flex; gap: 20px; position: relative; z-index: 10;">
        <a href="register.php" class="btn-neon" style="padding: 15px 40px; font-size: 1.1rem;">GET STARTED</a>
        <a href="events.php" class="btn-neon" style="padding: 15px 40px; font-size: 1.1rem; border-color: var(--neon-purple); color: var(--neon-purple);">EXPLORE EVENTS</a>
    </div>
</section>

<!-- Highlights Section -->
<section id="highlights" style="margin-top: 50px; text-align: center;">
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
    // Countdown JS remains the same
    const targetDate = new Date('April 15, 2026 10:00:00').getTime();
    function updateCountdown() {
        const now = new Date().getTime();
        const diff = targetDate - now;
        if (diff < 0) return;
        const d = Math.floor(diff / (1000 * 60 * 60 * 24));
        const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const s = Math.floor((diff % (1000 * 60)) / 1000);
        
        // Note: Countdown IDs were removed in the previous turn's layout change. 
        // If we want them back, we need to add the div back. For now, let's keep the focus on the vector title.
    }
    // Logic for countdown is omitted here as we changed the hero layout significantly.
</script>

<?php include 'includes/footer.php'; ?>
