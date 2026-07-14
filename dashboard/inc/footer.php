        </div><!-- /.dashboard_content -->
    </div><!-- /.dashboard_main -->
</div><!-- /.dashboard_layout -->

<script src="../<?= asset_version('assets/js/dashboard.min.js') ?>"></script>
<?php if (!empty($extra_scripts)): foreach ($extra_scripts as $script): ?>
<script src="<?= htmlspecialchars($script) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
