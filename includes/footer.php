        </div> <!-- Close Container -->
        <footer style="margin-top: 80px; padding: 40px; text-align: center; border-top: 1px solid var(--glass-border);">
            <p style="font-size: 0.9rem; color: var(--text-secondary); letter-spacing: 1px;">© 2026 NexusFest | Event Coordinator Dashboard</p>
        </footer>
    </main>
    <script>
        function createStars() {
            const container = document.getElementById('stars-container');
            if (!container) return;
            
            const starCount = 150;
            for (let i = 0; i < starCount; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                
                const size = Math.random() * 2 + 1;
                const x = Math.random() * 100;
                const y = Math.random() * 100;
                const delay = Math.random() * 5;
                const duration = Math.random() * 3 + 2;
                
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                star.style.left = `${x}%`;
                star.style.top = `${y}%`;
                star.style.setProperty('--duration', `${duration}s`);
                star.style.animationDelay = `${delay}s`;
                
                container.appendChild(star);
            }
        }
        document.addEventListener('DOMContentLoaded', createStars);
    </script>
    <script src="assets/js/script.js"></script>
</body>
</html>
