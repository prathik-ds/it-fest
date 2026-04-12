    <?php 
    $current_page = basename($_SERVER['PHP_SELF']);
    $is_dashboard_page = in_array($current_page, ['dashboard.php', 'coordinator.php', 'admin.php']);
    ?>

    <?php if ($is_dashboard_page): ?>
                </div> <!-- Close content-body -->
                <footer style="margin-top: auto; padding: 40px; text-align: center; border-top: 1px solid var(--border); color: var(--text-dim); font-size: 0.8rem;">
                    <p>&copy; 2026 NexusFest | Event Management System</p>
                </footer>
            </main> <!-- Close main-content-dash -->
        </div> <!-- Close app-wrapper -->
    <?php else: ?>
        </div> <!-- Close Container -->
        <footer style="margin-top: 100px; padding: 40px; text-align: center; border-top: 1px solid rgba(255, 255, 255, 0.05); background: rgba(5, 5, 5, 0.9);">
            <p class="orbitron neon-text-blue" style="font-size: 0.8rem; letter-spacing: 2px;">© 2026 SYNERGY FEST | Fusion of IT & Commerce</p>
            <p style="font-size: 0.7rem; color: #555; margin-top: 10px;">Designed for the Future. Built with code and commerce.</p>
        </footer>
    <?php endif; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>


