<?php include 'includes/header.php'; ?>

<section id="hero" style="min-height: 80vh; display: flex; flex-direction: column; align-items: flex-start; justify-content: center; padding: 40px 0;">
    <div class="glass" style="padding: 60px; max-width: 800px; border-radius: 24px;">
        <h1 style="font-size: 4.5rem; font-weight: 900; line-height: 1; margin-bottom: 20px; background: linear-gradient(135deg, #fff 0%, #6366f1 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            NexusFest 2026
        </h1>
        <p style="font-size: 1.4rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 40px;">
            Where <span style="color: var(--accent-blue);">intelligence</span> meets <span style="color: var(--accent-purple);">innovation</span>. Join the most prestigious college fest of the year.
        </p>

        <div style="display: flex; gap: 20px;">
            <a href="register.php" class="btn-primary" style="padding: 16px 40px; font-size: 1.1rem;">
                Get Started Now <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
            </a>
            <a href="events.php" class="btn-secondary" style="padding: 16px 40px; font-size: 1.1rem;">
                View All Events
            </a>
        </div>
    </div>
</section>

<section id="highlights" style="padding: 60px 0;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 40px;">
        <div>
            <h2 style="font-size: 2.2rem; font-weight: 800;">Fest Highlights</h2>
            <p style="color: var(--text-secondary); margin-top: 5px;">Experience the best of technology and commerce.</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
        <div class="glass" style="padding: 40px; position: relative; overflow: hidden;">
            <div style="font-size: 2.5rem; color: var(--accent-blue); margin-bottom: 20px;">
                <i class="fas fa-code"></i>
            </div>
            <h3 style="font-size: 1.5rem; margin-bottom: 15px;">IT Tracks</h3>
            <p style="color: var(--text-secondary); line-height: 1.6;">Dive into coding, hackathons, and security challenges meant for the brightest minds in tech.</p>
            <div style="position: absolute; right: -20px; bottom: -20px; font-size: 8rem; opacity: 0.03; color: white;">
                <i class="fas fa-terminal"></i>
            </div>
        </div>

        <div class="glass" style="padding: 40px; position: relative; overflow: hidden;">
            <div style="font-size: 2.5rem; color: var(--accent-purple); margin-bottom: 20px;">
                <i class="fas fa-briefcase"></i>
            </div>
            <h3 style="font-size: 1.5rem; margin-bottom: 15px;">Commerce Tracks</h3>
            <p style="color: var(--text-secondary); line-height: 1.6;">Compete in marketing, finance, and business quizzes to prove your corporate expertise.</p>
            <div style="position: absolute; right: -20px; bottom: -20px; font-size: 8rem; opacity: 0.03; color: white;">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>

        <div class="glass" style="padding: 40px; position: relative; overflow: hidden;">
            <div style="font-size: 2.5rem; color: #10b981; margin-bottom: 20px;">
                <i class="fas fa-trophy"></i>
            </div>
            <h3 style="font-size: 1.5rem; margin-bottom: 15px;">Mega Rewards</h3>
            <p style="color: var(--text-secondary); line-height: 1.6;">Cash prizes, certificates, and recognition that will boost your career profile.</p>
            <div style="position: absolute; right: -20px; bottom: -20px; font-size: 8rem; opacity: 0.03; color: white;">
                <i class="fas fa-medal"></i>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
