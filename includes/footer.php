    <?php 
    $current_page = basename($_SERVER['PHP_SELF']);
    $is_dashboard_page = in_array($current_page, ['dashboard.php', 'coordinator.php', 'admin.php']);
    ?>

    <?php if ($is_dashboard_page): ?>
                </div> <!-- Close content-body -->
                <footer style="margin-top: auto; padding: 30px 40px; text-align: center; border-top: 1px solid var(--border); color: var(--text-dim); font-size: 0.78rem;">
                    <p style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span style="background: var(--grad-primary); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; font-weight: 700;">FUSIONVERSE</span>
                        &copy; 2026 BCA IT Fest — Where Code Meets Innovation
                    </p>
                </footer>
            </main> <!-- Close main-content-dash -->
        </div> <!-- Close app-wrapper -->
    <?php else: ?>
        </div> <!-- Close Container -->
        <footer style="margin-top: 80px; padding: 50px 24px 30px; text-align: center; border-top: 1px solid var(--border); background: rgba(4, 6, 14, 0.95); backdrop-filter: blur(20px); position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 200px; height: 1px; background: linear-gradient(90deg, transparent, var(--accent-1), var(--accent-2), transparent);"></div>
            <p style="font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 1.1rem; margin-bottom: 8px;">
                <span style="background: var(--grad-primary); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">FUSIONVERSE 2026</span>
            </p>
            <p style="font-size: 0.8rem; color: var(--text-muted); letter-spacing: 2px; text-transform: uppercase;">BCA IT Fest — Innovation Through Code</p>
            <p style="font-size: 0.7rem; color: var(--text-dim); margin-top: 16px;">Built with 💜 for BCA students everywhere</p>
        </footer>
    <?php endif; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>

